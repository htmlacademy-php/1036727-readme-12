<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($link, $post_id);

$sql = "SELECT COUNT(*) FROM post_like WHERE post_id = $post_id AND author_id = $user_id";
$result = get_mysqli_result($link, $sql, 'assoc');

if ($result['COUNT(*)'] == 0) {
    $sql = "INSERT INTO post_like (author_id, post_id) VALUES ($user_id, $post_id)";
} else {
    $sql = "DELETE FROM post_like WHERE post_id = $post_id AND author_id = $user_id";
}

get_mysqli_result($link, $sql, false);
$ref = $_SERVER['HTTP_REFERER'] ?? '/';

if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('like', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
