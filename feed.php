<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /');
    exit;
}

$db = anatolev\Database::getInstance();

$content_type = filter_input(INPUT_GET, 'filter') ?? '';

$content_types = $db->getContentTypes();
$posts = $db->getFeedPosts($content_type);
$message_count = $db->getUnreadMessageCount();

setcookie('search_ref', '', time() - 3600);

$page_content = includeTemplate('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: моя лента',
    'main_modifier' => 'feed',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
