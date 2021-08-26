<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/profile.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$db = Anatolev\Database::getInstance();

$user_id = $_SESSION['user']['id'];
$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = $db->validateUser($profile_id);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('comments');
    $errors = validateForm('comments', $input);

    if (!is_null($errors) && empty($errors)) {
        $comment = cutOutExtraSpaces($input['comment']);
        $stmt_data = [$comment, $user_id, $input['post-id']];
        $db->insertComment($stmt_data);
        $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

        header("Location: $ref");
        exit;
    }
}

setcookie('search_ref', '', time() - 3600);

$limit = intval(filter_input(INPUT_GET, 'comments'));

$user = $db->getUserProfile($profile_id);
$posts = $db->getProfilePosts($profile_id, $limit);
$likes = $db->getProfileLikes($profile_id);
$subscriptions = $db->getProfileSubscriptions($profile_id);

$message_count = $db->getUnreadMessageCount();
$form_inputs = $db->getFormInputs('comments');

$page_content = includeTemplate('profile.php', [
    'user' => $user,
    'posts' => $posts,
    'likes' => $likes,
    'subscriptions' => $subscriptions,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: профиль',
    'main_modifier' => 'profile',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
