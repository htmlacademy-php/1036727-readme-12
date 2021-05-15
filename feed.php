<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$content_type = filter_input(INPUT_GET, 'filter') ?? '';

$content_types = get_content_types($con);
$posts = get_feed_posts($con, $content_type);

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$messages_count = get_message_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: моя лента',
    'main_modifier' => 'feed',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
