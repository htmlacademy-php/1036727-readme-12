<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$post_id = intval(filter_input(INPUT_GET, 'id'));
$post_id = validate_post($con, $post_id);

$post_fields = get_post_fields('', 'insert');
$sql = "SELECT $post_fields FROM post WHERE id = $post_id";
$post = get_mysqli_result($con, $sql, 'assoc');
$post['author_id'] = $user_id;
$post['is_repost'] = true;
$post['origin_post_id'] = $post_id;

$sql = "INSERT INTO post ($post_fields) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = db_get_prepare_stmt($con, $sql, $post);

if (mysqli_stmt_execute($stmt)) {
    $post['id'] = mysqli_insert_id($con);

    $sql = "SELECT hashtag_id AS id FROM post_hashtag WHERE post_id = $post_id";
    if ($hashtags = get_mysqli_result($con, $sql)) {

        foreach ($hashtags as $hashtag) {
            $sql = "INSERT INTO post_hashtag (hashtag_id, post_id) VALUES
                ({$hashtag['id']}, {$post['id']})";
            get_mysqli_result($con, $sql, false);
        }
    }

    if ($subscribers = get_subscribers($con)) {

        try {
            $transport = new Swift_SmtpTransport('phpdemo.ru', 25);
            $transport->setUsername('keks@phpdemo.ru');
            $transport->setPassword('htmlacademy');

            $message = new Swift_Message();
            $message->setSubject("Новая публикация от пользователя {$_SESSION['user']['login']}");

            $mailer = new Swift_Mailer($transport);

            foreach ($subscribers as $subscriber) {
                $message->setTo([$subscriber['email'] => $subscriber['login']]);

                $body = "Здравствуйте, {$subscriber['login']}. "
                      . "Пользователь {$_SESSION['user']['login']} только что опубликовал новую запись «{$post['title']}». "
                      . "Посмотрите её на странице пользователя: http://readme.net/profile.php?id={$user_id}";
                $message->setBody($body);
                $message->setFrom('keks@phpdemo.ru', 'Readme');

                $mailer->send($message);
            }

        } catch (Swift_TransportException $ex) {}

    }

    header("Location: /profile.php?id={$user_id}&tab=posts");
    exit;
}

http_response_code(500);
exit;
