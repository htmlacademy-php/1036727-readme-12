<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$db = anatolev\Database::getInstance();

$user_id = $_SESSION['user']['id'];
$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = $db->validatePost($post_id);

if (!$db->isPostLike([$post_id, $user_id])) {
    $db->insertPostLike([$post_id, $user_id]);
} else {
    $db->deletePostLike([$post_id, $user_id]);
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('action', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
