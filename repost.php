<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$db = Database::getInstance();

$user_id = $_SESSION['user']['id'];
$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = $db->validatePost($post_id);

$stmt_data = $db->getPost($post_id);
$stmt_data['author_id'] = $user_id;
$stmt_data['is_repost'] = true;
$stmt_data['origin_post_id'] = $post_id;

$post_id2 = $db->insertPost($stmt_data);

if ($hashtags = $db->getPostHashtagIds($post_id)) {
    foreach ($hashtags as $hashtag) {
        $stmt_data = [$hashtag['id'], $post_id2];
        $db->insertPostHashtag($stmt_data);
    }
}

if ($subscribers = $db->getSubscribers()) {
    sendPostNotifications($subscribers, $stmt_data['title']);
}

header("Location: /profile.php?id={$user_id}&tab=posts");
exit;
