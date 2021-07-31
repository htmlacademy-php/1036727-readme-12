<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/messages.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$db = Database::getInstance();

$user_id = $_SESSION['user']['id'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getPostInput('messages');
    $errors = validateForm('messages', $input);

    if (!is_null($errors) && empty($errors)) {
        $contact_id = $input['contact-id'];
        $message = cutOutExtraSpaces($input['message']);
        $stmt_data = [$message, $user_id, $contact_id];
        $db->insertMessage($stmt_data);

        if (($_COOKIE['new_contact'] ?? null) == $contact_id) {
            setcookie('new_contact', '', time() - 3600);
        }

        header("Location: /messages.php?contact={$contact_id}");
        exit;
    }
}

setcookie('search_ref', '', time() - 3600);

if (isset($_GET['contact'])) {
    $contact_id = intval(filter_input(INPUT_GET, 'contact'));
    $db->updateMessagesStatus($contact_id);
}

$contacts = $db->getContacts();

if (isset($_GET['contact'])) {

    if (!in_array($contact_id, array_column($contacts, 'id'))) {
        if (!$db->addNewContact($contacts, $contact_id)
            && $contact_id = $_COOKIE['new_contact'] ?? null) {
            $db->addNewContact($contacts, $contact_id);
        }

    } elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
        $db->addNewContact($contacts, $contact_id);
    }

} elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
    $db->addNewContact($contacts, $contact_id);
}

$message_count = $db->getUnreadMessageCount();
$form_inputs = $db->getFormInputs('messages');

$page_content = includeTemplate('messages.php', [
    'contacts' => $contacts,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: личные сообщения',
    'main_modifier' => 'messages',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
