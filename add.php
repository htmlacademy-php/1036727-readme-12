<?php

require_once('vendor/autoload.php');
require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/add.php?tab=text';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$content_types = get_content_types($con);
$class_names = array_column($content_types, 'class_name');
$content_types = array_combine($class_names, $content_types);

$tab = filter_input(INPUT_GET, 'tab') ?? 'photo';
$tab = in_array($tab, $class_names) ? $tab : 'photo';

$form_inputs = get_form_inputs($con, 'adding-post');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input('adding-post');

    if ($input && is_content_type_valid($con, $input['content-type'])) {

        $required_fields = get_required_fields($con, 'adding-post', $tab);
        foreach ($required_fields as $field) {
            if (mb_strlen($input[$field]) === 0) {
                $errors[$field][0] = 'Это поле должно быть заполнено';
                $errors[$field][1] = $form_inputs[$field]['label'];
            }
        }

        if ($input['content-type'] === 'photo') {
            if (!empty($_FILES['file-photo']['name'])) {
                validate_input_file_photo($errors, $input);
            } elseif (filter_var($input['image-url'], FILTER_VALIDATE_URL)) {
                validate_input_image_url($errors, $input);
            } else {
                $errors['file-photo'][0] = 'Вы не загрузили файл';
                $errors['file-photo'][1] = 'Изображение';
            }

        } elseif ($input['content-type'] === 'video') {
            if (filter_var($input['video-url'], FILTER_VALIDATE_URL)) {
                validate_input_video_url($errors, $input);
            } else {
                $errors['video-url'][0] = 'Некорректный url-адрес';
                $errors['video-url'][1] = 'Ссылка youtube';
            }

        } elseif ($input['content-type'] === 'text') {
            $content = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $input['post-text']);
            $input['text-content'] = preg_replace('/\040\040+/', ' ', $content);

        } elseif ($input['content-type'] === 'quote') {
            $content = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $input['cite-text']);
            $input['text-content'] = preg_replace('/\040\040+/', ' ', $content);

        } elseif ($input['content-type'] === 'link') {
            if (filter_var($input['post-link'], FILTER_VALIDATE_URL)) {
                validate_input_post_link($input);
            } else {
                $errors['post-link'][0] = 'Некорректный url-адрес';
                $errors['post-link'][1] = 'Ссылка';
            }
        }

        if (empty($errors)) {
            $content_type = $content_types[$input['content-type']];
            $stmt_data = get_stmt_data($input, 'adding-post');
            $stmt_data += [$user_id, 0, null, $content_type['id']];
            $post_id = insert_post($con, $stmt_data);

            if ($tags = array_filter(explode(' ', $input['tags']))) {
                foreach ($tags as $tag_name) {
                    validate_hashtag($con, $tag_name, $post_id);
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
                              . "Пользователь {$_SESSION['user']['login']} только что опубликовал новую запись «{$input['heading']}». "
                              . "Посмотрите её на странице пользователя: http://readme.net/profile.php?id={$_SESSION['user']['id']}";
                        $message->setBody($body);
                        $message->setFrom('keks@phpdemo.ru', 'Readme');

                        $mailer->send($message);
                    }

                } catch (Swift_TransportException $ex) {}

            }

            header("Location: /post.php?id={$post_id}");
            exit;

        } elseif (isset($input['image-path'])) {
            delete_file($input['image-path']);
        }
    }
}

$url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($url, PHP_URL_PATH) !== '/add.php') {
    setcookie('add_ref', $url, strtotime('+30 days'));
}

$page_content = include_template('add.php', [
    'content_types' => $content_types,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_message_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: добавление публикации',
    'main_modifier' => 'adding-post',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
