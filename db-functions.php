<?php

function get_post_hashtags(mysqli $link, int $post_id) : array {
    $sql = "SELECT
        h.id, h.name
        FROM hashtag h
        INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id
        INNER JOIN post p ON p.id = ph.post_id
        WHERE p.id = $post_id";
    $hashtags = get_mysqli_result($link, $sql);

    return $hashtags;
}

function get_post_comments(mysqli $link, int $post_id) : array {
    $counter = filter_input(INPUT_GET, 'comments');
    $limit = !$counter || $counter !== 'all' ? ' LIMIT 2' : '';

    $sql = "SELECT
        c.id, c.dt_add, c.content, c.author_id, c.post_id,
        u.login, u.avatar_path
        FROM comment c
        INNER JOIN user u ON u.id = c.author_id
        WHERE post_id = $post_id
        ORDER BY c.dt_add DESC{$limit}";
    $comments = get_mysqli_result($link, $sql);

    return $comments;
}

function get_post(mysqli $link, int $post_id) : array {
    $sql = "SELECT
        p.dt_add, p.author_id, u.login AS author, u.avatar_path
        FROM post p
        INNER JOIN user u ON u.id = p.author_id
        WHERE p.id = $post_id";
    $post = get_mysqli_result($link, $sql, 'assoc');

    return $post;
}

function get_messages_count(mysqli $link) : string {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT COUNT(id)
        FROM message
        WHERE recipient_id = $user_id AND status = 0";
    $messages_count = get_mysqli_result($link, $sql, 'assoc')['COUNT(id)'];

    return $messages_count;
}

function get_message_preview(mysqli $link, int $contact_id) : array {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT dt_add, content, sender_id FROM message
        WHERE (recipient_id = $user_id AND sender_id = $contact_id)
        OR (recipient_id = $contact_id AND sender_id = $user_id)
        ORDER BY dt_add DESC LIMIT 1";
    $message = get_mysqli_result($link, $sql, 'assoc');
    $preview = mb_substr($message['content'], 0, 30);
    $preview = $message['sender_id'] == $user_id ? "Вы: $preview" : $preview;

    return ['text' => $preview, 'time' => $message['dt_add']];
}

function get_contact_messages(mysqli $link, int $contact_id) : array {
    $user_id = intval($_SESSION['user']['id']);
    $message_fields = 'm.id, m.dt_add, m.content, m.status, m.sender_id, m.recipient_id';
    $sql = "SELECT {$message_fields}, u.login AS author, u.avatar_path FROM message m
        INNER JOIN user u ON u.id = m.sender_id
        WHERE (m.recipient_id = $user_id AND m.sender_id = $contact_id)
        OR (m.recipient_id = $contact_id AND m.sender_id = $user_id)
        ORDER BY m.dt_add";
    $messages = get_mysqli_result($link, $sql);

    return $messages;
}
