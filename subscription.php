<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$profile_id = intval(filter_input(INPUT_GET, 'profile_id'));
$profile_id = validate_profile($link, $profile_id);

if ($profile_id === $user_id) {
    http_response_code(500);
    exit;
}

$sql = "SELECT id FROM subscription WHERE author_id = $user_id AND user_id = $profile_id";
$result = get_mysqli_result($link, $sql, false);

if (!mysqli_num_rows($result)) {
    $sql = "INSERT INTO subscription (author_id, user_id) VALUES ($user_id, $profile_id)";
} else {
    $sql = "DELETE FROM subscription WHERE author_id = $user_id AND user_id = $profile_id";
}

get_mysqli_result($link, $sql, false);
$ref = $_SERVER['HTTP_REFERER'] ?? '/';

header("Location: $ref");
exit;
