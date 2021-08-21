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

$db = Database::getInstance();

$user_id = $_SESSION['user']['id'];

$ctypes = $db->getContentTypes();
$class_names = array_column($ctypes, 'class_name');
$ctypes = array_combine($class_names, $ctypes);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('adding-post');

    if ($db->isContentTypeExist($input['content-type'] ?? '')) {
        $form_name = "adding-post-{$input['content-type']}";
        $errors = validateForm($form_name, $input);

        if (!is_null($errors) && empty($errors)) {

            if ($input['content-type'] === 'photo') {
                $input['image-path'] = uploadImageFile($input, $errors);
            } elseif ($input['content-type'] === 'text') {
                $input['text-content'] = cutOutExtraSpaces($input['post-text']);
            } elseif ($input['content-type'] === 'quote') {
                $input['text-content'] = cutOutExtraSpaces($input['cite-text']);
            } elseif ($input['content-type'] === 'link') {
                processInputPostLink($input);
            }

            $ctype_id = $ctypes[$input['content-type']]['id'];
            $stmt_data = getStmtData($input, 'adding-post');
            $stmt_data += [$user_id, 0, null, $ctype_id];
            $post_id = $db->insertPost($stmt_data);

            if ($hashtags = explode(' ', $input['tags'])) {
                processPostHashtags($hashtags, $post_id);
            }

            if ($subscribers = $db->getSubscribers()) {
                sendPostNotifications($subscribers, $input['heading']);
            }

            header("Location: /post.php?id={$post_id}");
            exit;
        }
    }
}

setcookie('search_ref', '', time() - 3600);

$url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
if (parse_url($url, PHP_URL_PATH) !== '/add.php') {
    setcookie('add_ref', $url, strtotime('+30 days'));
}

$message_count = $db->getUnreadMessageCount();
$tabs_content = $db->getTabsContentData();
$form_inputs = $db->getFormInputs('adding-post');

$page_content = includeTemplate('add.php', [
    'content_types' => $ctypes,
    'tabs_content' => $tabs_content,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: добавление публикации',
    'main_modifier' => 'adding-post',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
