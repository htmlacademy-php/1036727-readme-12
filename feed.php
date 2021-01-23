<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$sql = 'SELECT * FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$posts = [];

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts,
    'link' => $link
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'feed',
    'title' => 'readme: моя лента',
    'page_content' => $page_content
]);

print($layout_content);
