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

$stmt_data = get_post($con, $post_id);
$stmt_data['author_id'] = $user_id;
$stmt_data['is_repost'] = true;
$stmt_data['origin_post_id'] = $post_id;

$post_id2 = insert_post($con, $stmt_data);

if ($hashtags = get_post_hashtag_ids($con, $post_id)) {
    foreach ($hashtags as $hashtag) {
        $stmt_data = [$hashtag['id'], $post_id2];
        insert_post_hashtag($con, $stmt_data);
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
