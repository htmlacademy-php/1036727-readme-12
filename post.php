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

$input_fields = 'i.id, i.label, i.name, i.placeholder, i.required';
$sql = "SELECT {$input_fields}, it.name AS type FROM input i
    INNER JOIN input_type it ON it.id = i.type_id
    INNER JOIN form_input fi ON fi.input_id = i.id
    INNER JOIN form f ON f.id = fi.form_id
    WHERE f.name = 'comments'";

$form_inputs = get_mysqli_result($con, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($con, 'comments');

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
        $comment = mysqli_real_escape_string($con, $comment);
        $sql = 'INSERT INTO comment (content, author_id, post_id) VALUES '
             . "('$comment', $user_id, $post_id)";
        get_mysqli_result($con, $sql, false);

        $sql = "SELECT author_id FROM post WHERE id = $post_id";
        $author_id = get_mysqli_result($con, $sql, 'assoc')['author_id'];

        header("Location: /profile.php?id={$author_id}&tab=posts");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_COOKIE['action'])) {
    $sql = "UPDATE post SET show_count = show_count + 1 WHERE id = $post_id";
    get_mysqli_result($con, $sql, false);
} elseif (isset($_COOKIE['action'])) {
    setcookie('action', '', time() - 3600);
}

$post_fields = get_post_fields('p.');

$sql = "SELECT
    COUNT(DISTINCT p2.id) AS repost_count,
    COUNT(DISTINCT c.id) AS comment_count,
    COUNT(DISTINCT pl.id) AS like_count,
    COUNT(DISTINCT pl2.id) AS is_like,
    {$post_fields}, ct.class_name
    FROM post p
    LEFT JOIN user u ON u.id = p.author_id
    LEFT JOIN content_type ct ON ct.id = p.content_type_id
    LEFT JOIN post p2 ON p2.origin_post_id = p.id
    LEFT JOIN comment c ON c.post_id = p.id
    LEFT JOIN post_like pl ON pl.post_id = p.id
    LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
    WHERE p.id = $post_id
    GROUP BY p.id";
$post = get_mysqli_result($con, $sql, 'assoc');
$post['display_mode'] = 'details';

$sql = "SELECT
    COUNT(DISTINCT s.id) AS is_subscription,
    COUNT(DISTINCT s2.id) AS subscriber_count,
    COUNT(DISTINCT p2.id) AS publication_count,
    u.dt_add, u.login, u.avatar_path
    FROM post p
    LEFT JOIN user u ON u.id = p.author_id
    LEFT JOIN post p2 ON p2.author_id = p.author_id
    LEFT JOIN subscription s ON s.user_id = p.author_id AND s.author_id = $user_id
    LEFT JOIN subscription s2 ON s2.user_id = p.author_id
    WHERE p.id = $post_id
    GROUP BY p.id";
$post['author'] = get_mysqli_result($con, $sql, 'assoc');

$post['hashtags'] = get_post_hashtags($con, $post_id);
$post['comments'] = get_post_comments($con, $post_id);

$page_content = include_template('post.php', [
    'post' => $post,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_messages_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: публикация',
    'main_modifier' => 'publication',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
