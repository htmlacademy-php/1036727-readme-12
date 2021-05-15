<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($con, $post_id);

if (!is_post_like($con, $post_id)) {
    insert_post_like($con, $post_id);
} else {
    delete_post_like($con, $post_id);
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('action', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
