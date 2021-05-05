<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/messages.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$input_fields = 'i.id, i.label, i.name, i.placeholder, i.required';
$sql = "SELECT {$input_fields}, it.name AS type FROM input i
    INNER JOIN input_type it ON it.id = i.type_id
    INNER JOIN form_input fi ON fi.input_id = i.id
    INNER JOIN form f ON f.id = fi.form_id
    WHERE f.name = 'messages'";

$form_inputs = get_mysqli_result($con, $sql);
$input_names = array_column($form_inputs, 'name');
$form_inputs = array_combine($input_names, $form_inputs);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = get_post_input($con, 'messages');

    if (mb_strlen($input['message']) === 0) {
        $errors['message'][0] = 'Это поле должно быть заполнено';
        $errors['message'][1] = $form_inputs['message']['label'];
    }

    if (empty($errors)) {
        $contact_id = validate_user($con, intval($input['contact-id']));

        if ($contact_id === $user_id
            || !is_contact_valid($con, $contact_id)) {
            http_response_code(500);
            exit;
        }

        $message = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $input['message']);
        $message = preg_replace('/\040\040+/', ' ', $message);

        $message = mysqli_real_escape_string($con, $message);
        $sql = "INSERT INTO message (content, sender_id, recipient_id) VALUES
            ('$message', $user_id, $contact_id)";
        get_mysqli_result($con, $sql, false);
        setcookie('new_contact', '', time() - 3600);

        header("Location: /messages.php?contact={$contact_id}");
        exit;
    }
}

if (isset($_GET['contact'])) {
    $contact_id = intval(filter_input(INPUT_GET, 'contact'));
    update_messages_status($con, $contact_id);
}

$sql = "SELECT
    COUNT(DISTINCT m2.id) AS all_messages_count,
    COUNT(DISTINCT m3.id) AS unread_messages_count,
    u.id, u.login, u.avatar_path
    FROM message m
    LEFT JOIN user u ON u.id = m.sender_id OR u.id = m.recipient_id
    LEFT JOIN message m2 ON m2.recipient_id = $user_id AND m2.sender_id = u.id
    LEFT JOIN message m3 ON m3.recipient_id = $user_id AND m3.sender_id = u.id AND m3.status = 0
    WHERE (m.sender_id = $user_id OR m.recipient_id = $user_id) AND u.id != $user_id
    GROUP BY u.id
    ORDER BY MAX(m.dt_add) DESC";
$contacts = get_mysqli_result($con, $sql);

for ($i = 0; $i < count($contacts); $i++) {
    $contact_id = $contacts[$i]['id'];
    $contacts[$i]['preview'] = get_message_preview($con, $contact_id);
    $contacts[$i]['messages'] = get_contact_messages($con, $contact_id);
}

if (isset($_GET['contact'])) {

    if (!in_array($contact_id, array_column($contacts, 'id'))) {
        if (!add_new_contact($con, $contacts, $contact_id)
            && $contact_id = $_COOKIE['new_contact'] ?? null) {
            add_new_contact($con, $contacts, $contact_id);
        }

    } elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
        add_new_contact($con, $contacts, $contact_id);
    }

} elseif ($contact_id = $_COOKIE['new_contact'] ?? null) {
    add_new_contact($con, $contacts, $contact_id);
}

$page_content = include_template('messages.php', [
    'contacts' => $contacts,
    'errors' => $errors,
    'inputs' => $form_inputs
]);

$messages_count = get_messages_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: личные сообщения',
    'main_modifier' => 'messages',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
