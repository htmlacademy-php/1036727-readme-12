<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = Database::getInstance()->validatePost($post_id);

if (!Database::getInstance()->isPostLike([$post_id, $user_id])) {
    Database::getInstance()->insertPostLike([$post_id, $user_id]);
} else {
    Database::getInstance()->deletePostLike([$post_id, $user_id]);
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('action', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
