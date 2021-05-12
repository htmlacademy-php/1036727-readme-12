<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/profile.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

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
        $comment = mysqli_real_escape_string($con, $comment);
        $sql = 'INSERT INTO comment (content, author_id, post_id) VALUES '
             . "('$comment', {$_SESSION['user']['id']}, $post_id)";
        get_mysqli_result($con, $sql, false);
        $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
        $ref = preg_replace('%&comments=all%', '', $ref);

        header("Location: $ref");
        exit;
    }
}

$sql = "SELECT
    COUNT(DISTINCT s.id) AS is_subscription,
    COUNT(DISTINCT s2.id) AS subscriber_count,
    COUNT(DISTINCT p.id) AS publication_count,
    u.id, u.dt_add, u.login, u.avatar_path
    FROM user u
    LEFT JOIN post p ON p.author_id = u.id
    LEFT JOIN subscription s ON s.user_id = u.id AND s.author_id = $user_id
    LEFT JOIN subscription s2 ON s2.user_id = u.id
    WHERE u.id = $profile_id
    GROUP BY u.id";
$user = get_mysqli_result($con, $sql, 'assoc');

$post_fields = get_post_fields('p.');

$sql = "SELECT
    COUNT(DISTINCT p2.id) AS repost_count,
    COUNT(DISTINCT c.id) AS comment_count,
    COUNT(DISTINCT pl.id) AS like_count,
    COUNT(DISTINCT pl2.id) AS is_like,
    {$post_fields}, ct.class_name
    FROM post p
    LEFT JOIN content_type ct ON ct.id = p.content_type_id
    LEFT JOIN post p2 ON p2.origin_post_id = p.id
    LEFT JOIN comment c ON c.post_id = p.id
    LEFT JOIN post_like pl ON pl.post_id = p.id
    LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
    WHERE p.author_id = $profile_id
    GROUP BY p.id
    ORDER BY p.dt_add ASC";
$posts = get_mysqli_result($con, $sql);

for ($i = 0; $i < count($posts); $i++) {
    $post = $posts[$i];
    $posts[$i]['hashtags'] = get_post_hashtags($con, $post['id']);
    $posts[$i]['comments'] = get_post_comments($con, $post['id']);

    $is_repost = $post['is_repost'] && $post_id = $post['origin_post_id'];
    $posts[$i]['origin'] = $is_repost ? get_post($con, $post_id) : [];
}

$user_fields = 'u.id AS user_id, u.login AS author, u.avatar_path';
$sql = "SELECT {$post_fields}, {$user_fields},
    ct.type_name, ct.class_name, pl.dt_add
    FROM post p
    LEFT JOIN content_type ct ON ct.id = p.content_type_id
    LEFT JOIN post_like pl ON pl.post_id = p.id
    LEFT JOIN user u ON u.id = pl.author_id
    WHERE p.author_id = $profile_id
    GROUP BY p.id, pl.id, u.id
    HAVING COUNT(pl.id) > 0
    ORDER BY pl.dt_add DESC";
$likes = get_mysqli_result($con, $sql);

$user_fields = 'u.id, u.dt_add, u.email, u.login, u.password, u.avatar_path';
$sql = "SELECT
    COUNT(DISTINCT s2.id) AS is_subscription,
    COUNT(DISTINCT s3.id) AS subscriber_count,
    COUNT(DISTINCT p.id) AS publication_count,
    {$user_fields}
    FROM subscription s
    LEFT JOIN user u ON u.id = s.user_id
    LEFT JOIN post p ON p.author_id = u.id
    LEFT JOIN subscription s2 ON s2.user_id = u.id AND s2.author_id = $user_id
    LEFT JOIN subscription s3 ON s3.user_id = u.id
    WHERE s.author_id = $profile_id
    GROUP BY s.id";
$subscriptions = get_mysqli_result($con, $sql);

$page_content = include_template('profile.php', [
    'user' => $user,
    'posts' => $posts,
    'likes' => $likes,
    'subscriptions' => $subscriptions,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_messages_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: профиль',
    'main_modifier' => 'profile',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
