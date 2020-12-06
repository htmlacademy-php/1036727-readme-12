<?php

require_once('init.php');

$post_id = filter_input(INPUT_GET, 'id');
settype($post_id, 'integer');

if (!is_post_valid($link, $post_id)) {
    http_response_code(404);
    exit;
}

$sql = 'SELECT p.*, u.login AS author, u.avatar_path, ct.class_name FROM post p '
     . 'INNER JOIN user u ON p.author_id = u.id '
     . 'INNER JOIN content_type ct ON p.content_type_id = ct.id '
     . "WHERE p.id = $post_id";
$post = get_mysqli_result($link, $sql, 'assoc');

$is_auth = rand(0, 1);
$user_name = 'Максим'; // укажите здесь ваше имя

$page_content = include_template('post-details.php', [
    'link' => $link,
    'post' => $post
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'publication',
    'title' => 'readme: публикация',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
