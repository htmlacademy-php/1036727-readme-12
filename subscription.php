<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$user_id = intval($_SESSION['user']['id']);
$profile_id = intval(filter_input(INPUT_GET, 'id'));
$profile_id = validate_user($con, $profile_id);

if ($profile_id === $user_id) {
    http_response_code(500);
    exit;
}

if (!is_subscription($con, $profile_id)) {
    insert_subscription($con, $profile_id);
    $profile = get_subscription($con, $profile_id);

    try {
        $transport = new Swift_SmtpTransport('phpdemo.ru', 25);
        $transport->setUsername('keks@phpdemo.ru');
        $transport->setPassword('htmlacademy');

        $message = new Swift_Message('У вас новый подписчик');
        $message->setTo([$profile['email'] => $profile['login']]);

        $body = "Здравствуйте, {$profile['login']}. "
              . "На вас подписался новый пользователь {$_SESSION['user']['login']}. "
              . "Вот ссылка на его профиль: http://readme.net/profile.php?id={$profile_id}";
        $message->setBody($body);
        $message->setFrom('keks@phpdemo.ru', 'Readme');

        $mailer = new Swift_Mailer($transport);
        $mailer->send($message);

    } catch (Swift_TransportException $ex) {}

} else {
    delete_subscription($con, $profile_id);
}

$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($ref, PHP_URL_PATH) === '/post.php') {
    setcookie('action', 1, strtotime('+30 days'));
}

header("Location: $ref");
exit;
