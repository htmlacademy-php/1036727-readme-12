<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/profile.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = validate_user($con, $profile_id);

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
        $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
        $ref = preg_replace('%&comments=all%', '', $ref);

        header("Location: $ref");
        exit;
    }
}

$user = get_user_profile($con, $profile_id);
$posts = get_profile_posts($con, $profile_id);
$likes = get_profile_likes($con, $profile_id);
$subscriptions = get_profile_subscriptions($con, $profile_id);

$page_content = include_template('profile.php', [
    'user' => $user,
    'posts' => $posts,
    'likes' => $likes,
    'subscriptions' => $subscriptions,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_message_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: профиль',
    'main_modifier' => 'profile',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
