<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$content_type_filter = '';
$content_type = filter_input(INPUT_GET, 'filter');
$content_type = mysqli_real_escape_string($con, $content_type);

if (is_content_type_valid($con, $content_type)) {
    $content_type_filter = " AND ct.class_name = '$content_type' ";
}

$content_types = get_content_types($con);
$posts = get_feed_posts($con, $content_type_filter);

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$messages_count = get_messages_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: моя лента',
    'main_modifier' => 'feed',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
