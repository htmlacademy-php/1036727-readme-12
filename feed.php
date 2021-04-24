<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$sql = 'SELECT id, type_name, class_name, icon_width, icon_height FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$content_type_filter = '';
if ($content_type = filter_input(INPUT_GET, 'filter')) {
    $content_type = mysqli_real_escape_string($link, $content_type);

    if (is_content_type_valid($link, $content_type)) {
        $content_type_filter = " AND ct.class_name = '$content_type' ";
    }
}

$post_fields = get_post_fields('p.');
$user_fields = 'u.login AS author, u.avatar_path';

$sql = "SELECT
    COUNT(DISTINCT p2.id) AS repost_count,
    COUNT(DISTINCT c.id) AS comment_count,
    COUNT(DISTINCT pl.id) AS like_count,
    COUNT(DISTINCT pl2.id) AS is_like,
    {$post_fields}, {$user_fields}, ct.class_name
    FROM post p
    LEFT JOIN user u ON u.id = p.author_id
    LEFT JOIN content_type ct ON ct.id = p.content_type_id
    LEFT JOIN post p2 ON p2.origin_post_id = p.id
    LEFT JOIN comment c ON c.post_id = p.id
    LEFT JOIN post_like pl ON pl.post_id = p.id
    LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
    LEFT JOIN subscription s ON s.user_id = p.author_id
    WHERE s.author_id = {$user_id}{$content_type_filter}
    GROUP BY p.id
    ORDER BY p.dt_add ASC";
$posts = get_mysqli_result($link, $sql);

for ($i = 0; $i < count($posts); $i++) {
    $hashtags = get_post_hashtags($link, $posts[$i]['id']);
    $posts[$i]['hashtags'] = $hashtags;
}

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$messages_count = get_messages_count($link);
$layout_content = include_template('layout.php', [
    'title' => 'readme: моя лента',
    'main_modifier' => 'feed',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
