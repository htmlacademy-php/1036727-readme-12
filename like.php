<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($link, $post_id);

$sql = "SELECT id FROM post_like WHERE post_id = $post_id AND author_id = $user_id";
$result = get_mysqli_result($link, $sql, false);

if (!mysqli_num_rows($result)) {
    $sql = "INSERT INTO post_like (author_id, post_id) VALUES ($user_id, $post_id)";
} else {
    $sql = "DELETE FROM post_like WHERE post_id = $post_id AND author_id = $user_id";
}

get_mysqli_result($link, $sql, false);
$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('action', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
