<?php

require_once('init.php');

$post_id = filter_input(INPUT_GET, 'id');
settype($post_id, 'integer');
validate_post($link, $post_id);

$sql = 'SELECT i.* FROM input i '
     . 'INNER JOIN form_input fi ON fi.input_id = i.id '
     . 'INNER JOIN form f ON f.id = fi.form_id '
     . "WHERE f.name = 'comments'";

$form_inputs = get_mysqli_result($link, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = get_post_input($link, 'comments');

    if (strlen($input['comment']) == 0) {
        $errors['comment'][0] = 'Заполните это поле';
        $errors['comment'][1] = $form_inputs['comment']['label'];
    }

    if (empty($errors)) {
        $comment = mysqli_real_escape_string($link, $input['comment']);
        $sql = 'INSERT INTO comment (content, author_id, post_id) VALUES '
             . "('$comment', 1, $post_id)";
        get_mysqli_result($link, $sql, 'insert');

        header("Location: /post.php?id=$post_id");
        exit;
    }
}

$sql = 'SELECT p.*, u.login AS author, u.avatar_path, ct.class_name FROM post p '
     . 'INNER JOIN user u ON u.id = p.author_id '
     . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
     . "WHERE p.id = $post_id";
$post = get_mysqli_result($link, $sql, 'assoc');
$post['details'] = true;

$sql = 'SELECT * FROM hashtag h '
     . 'INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id '
     . 'INNER JOIN post p ON p.id = ph.post_id '
     . "WHERE p.id = $post_id";
$hashtags = get_mysqli_result($link, $sql);

$sql = 'SELECT c.*, u.login, u.avatar_path FROM comment c '
     . 'INNER JOIN user u ON u.id = c.author_id '
     . "WHERE post_id = $post_id "
     . 'ORDER BY c.dt_add DESC';
$comments = get_mysqli_result($link, $sql);

$is_auth = rand(0, 1);
$user_name = 'Максим'; // укажите здесь ваше имя

$page_content = include_template('post-details.php', [
    'inputs' => $form_inputs,
    'errors' => $errors,
    'link' => $link,
    'post' => $post,
    'hashtags' => $hashtags,
    'comments' => $comments
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'publication',
    'title' => 'readme: публикация',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
