<?php

require_once('init.php');

$sql = 'SELECT * FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$filter = '';

if ($content_type = filter_input(INPUT_GET, 'filter')) {
    $content_type = mysqli_real_escape_string($link, $content_type);

    if (is_content_type_valid($link, $content_type)) {
        $filter = "WHERE c.class_name = '$content_type' ";
    }
}

$sql = 'SELECT p.*, u.login AS author, u.avatar_path AS avatar, c.class_name AS class_name FROM post p '
     . 'INNER JOIN user u ON p.author_id = u.id '
     . 'INNER JOIN content_type c ON p.content_type_id = c.id ' . $filter
     . 'ORDER BY show_count DESC LIMIT 6';
$posts = get_mysqli_result($link, $sql);

$is_auth = rand(0, 1);
$user_name = 'Максим'; // укажите здесь ваше имя

$page_content = include_template('main.php', [
    'content_types' => $content_types,
    'posts' => $posts
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'popular',
    'title' => 'readme: популярное',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
