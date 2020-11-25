<?php

require_once 'init.php';

if (!$link) {
    $error = mysqli_connect_error($link);
    print("Ошибка подключения: $error");
    exit;
}

$mysqli_errors = [];
$content_types = [];
$posts = [];

$sql = 'SELECT * FROM content_type';
$result = mysqli_query($link, $sql);

if ($result) {
    $content_types = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $mysqli_errors[] = mysqli_error($link);
}

$sql = 'SELECT p.*, u.login AS author, u.avatar_path AS avatar, c.class_name AS class_name FROM post p '
     . 'INNER JOIN user u ON p.author_id = u.id '
     . 'INNER JOIN content_type c ON p.content_type_id = c.id '
     . 'ORDER BY show_count DESC';
$result = mysqli_query($link, $sql);

if ($result) {
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $mysqli_errors[] = mysqli_error($link);
}

$is_auth = rand(0, 1);

$user_name = 'Максим'; // укажите здесь ваше имя

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$layout_content = include_template('layout.php', [
    'title' => 'readme: популярное',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
