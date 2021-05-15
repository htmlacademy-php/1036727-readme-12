<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/post.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($con, $post_id);

$form_inputs = get_form_inputs($con, 'comments');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('comments');

    if (mb_strlen($input['comment']) === 0) {
        $errors['comment'][0] = 'Это поле должно быть заполнено';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    } elseif (mb_strlen($input['comment']) < 4) {
        $errors['comment'][0] = 'Длина комментария не должна быть меньше четырёх символов';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    }

    if (empty($errors)) {
        $post_id = validate_post($con, intval($input['post-id']));
        $comment = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $input['comment']);
        $comment = preg_replace('/\040\040+/', ' ', $comment);
        $stmt_data = [$comment, $_SESSION['user']['id'], $post_id];
        insert_comment($con, $stmt_data);
        $author_id = get_post_author_id($con, $post_id);

        header("Location: /profile.php?id={$author_id}&tab=posts");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_COOKIE['action'])) {
    update_post_show_count($con, $post_id);
} elseif (isset($_COOKIE['action'])) {
    setcookie('action', '', time() - 3600);
}

$post = get_post_details($con, $post_id);
$post['author'] = get_post_author($con, $post_id);
$post['hashtags'] = get_post_hashtags($con, $post_id);
$post['comments'] = get_post_comments($con, $post_id);

$page_content = include_template('post.php', [
    'post' => $post,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_message_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: публикация',
    'main_modifier' => 'publication',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
