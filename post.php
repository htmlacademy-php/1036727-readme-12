<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/post.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$db = Anatolev\Database::getInstance();

$user_id = $_SESSION['user']['id'];
$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = $db->validatePost($post_id);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('comments');
    $errors = validateForm('comments', $input);

    if (!is_null($errors) && empty($errors)) {
        $comment = cutOutExtraSpaces($input['comment']);
        $stmt_data = [$comment, $user_id, $input['post-id']];
        $db->insertComment($stmt_data);
        $author_id = $db->getPostAuthorId($input['post-id']);

        header("Location: /profile.php?id={$author_id}&tab=posts");
        exit;
    }
}

setcookie('search_ref', '', time() - 3600);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_COOKIE['action'])) {
    $db->updatePostShowCount($post_id);
} elseif (isset($_COOKIE['action'])) {
    setcookie('action', '', time() - 3600);
}

$limit = intval(filter_input(INPUT_GET, 'comments'));

$post = $db->getPostDetails($post_id);
$post['author'] = $db->getPostAuthor($post_id);
$post['hashtags'] = $db->getPostHashtags($post_id);
$post['comments'] = $db->getPostComments($post_id, $limit);

$message_count = $db->getUnreadMessageCount();
$form_inputs = $db->getFormInputs('comments');

$page_content = includeTemplate('post.php', [
    'post' => $post,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: публикация',
    'main_modifier' => 'publication',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
