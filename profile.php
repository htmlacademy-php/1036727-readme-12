<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = validate_user($link, $profile_id);

$sql = 'SELECT i.* FROM input i '
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name = 'comments'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($link, 'comments');

    if (mb_strlen($input['comment']) === 0) {
        $errors['comment'][0] = 'Это поле должно быть заполнено';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    } elseif (mb_strlen($input['comment']) < 4) {
        $errors['comment'][0] = 'Длина комментария не должна быть меньше четырёх символов';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    }

    if (empty($errors)) {
        $post_id = validate_post($link, intval($input['post-id']));
        $comment = mysqli_real_escape_string($link, $input['comment']);
        $sql = 'INSERT INTO comment (content, author_id, post_id) VALUES '
             . "('$comment', {$_SESSION['user']['id']}, $post_id)";
        get_mysqli_result($link, $sql, false);

        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    }
}

$sql = "SELECT * FROM user WHERE id = $profile_id";
$user = get_mysqli_result($link, $sql, 'assoc');

$posts = [];
$likes = [];
$subscriptions = [];

switch (filter_input(INPUT_GET, 'tab')) {
    case 'posts':
        $sql = 'SELECT p.*, COUNT(c.id), ct.class_name FROM post p '
             . 'LEFT JOIN content_type ct ON ct.id = p.content_type_id '
             . 'LEFT JOIN comment c ON c.post_id = p.id '
             . "WHERE p.author_id = $profile_id "
             . 'GROUP BY p.id '
             . 'ORDER BY p.dt_add DESC';
        $posts = get_mysqli_result($link, $sql);
        break;
    case 'likes':
        break;
    case 'subscriptions':
        break;
}


$page_content = include_template('profile.php', [
    'subscriptions' => $subscriptions,
    'posts' => $posts,
    'likes' => $likes,
    'link' => $link,
    'user' => $user,
    'inputs' => $form_inputs,
    'errors' => $errors
]);

$layout_content = include_template('layout.php', [
    'link' => $link,
    'title' => 'readme: профиль',
    'page_main_class' => 'profile',
    'page_content' => $page_content
]);

print($layout_content);
