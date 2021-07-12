<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = Database::getInstance()->validatePost($post_id);

$stmt_data = Database::getInstance()->getPost($post_id);
$stmt_data['author_id'] = $user_id;
$stmt_data['is_repost'] = true;
$stmt_data['origin_post_id'] = $post_id;

$post_id2 = Database::getInstance()->insertPost($stmt_data);

if ($hashtags = Database::getInstance()->getPostHashtagIds($post_id)) {
    foreach ($hashtags as $hashtag) {
        $stmt_data = [$hashtag['id'], $post_id2];
        Database::getInstance()->insertPostHashtag($stmt_data);
    }
}

if ($subscribers = Database::getInstance()->getSubscribers()) {
    sendPostNotifications($subscribers, $post['title']);
}

header("Location: /profile.php?id={$user_id}&tab=posts");
exit;
