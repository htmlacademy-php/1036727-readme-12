<?php

function get_mysqli_result(mysqli $con, string $sql, string $result_type = 'all') {
    if (!$result = mysqli_query($con, $sql)) {
        update_errors_log($con, $sql, 'mysql_errors.txt');

        http_response_code(500);
        exit;

    } elseif ($result_type === 'all') {
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } elseif ($result_type === 'assoc') {
        $result = mysqli_fetch_assoc($result);
    }

    return $result;
}

function update_errors_log(mysqli $con, string $sql, string $filename): void {
    $date = date('d-m-Y H:i:s');
    $error = mysqli_error($con);
    $data = "$date - $error\n$sql\n\n";

    file_put_contents($filename, $data, FILE_APPEND | LOCK_EX);
}

function is_user_valid(mysqli $con, int $user_id): bool {
    $sql = "SELECT id FROM user WHERE id = $user_id";
    $result = get_mysqli_result($con, $sql, false);

    return boolval(mysqli_num_rows($result));
}

function validate_user(mysqli $con, int $user_id): int {
    if (!is_user_valid($con, $user_id)) {
        http_response_code(404);
        exit;
    }

    return $user_id;
}

function get_content_types(mysqli $con): array {
    $sql = 'SELECT
        id, type_name, class_name, icon_width, icon_height
        FROM content_type';
    $content_types = get_mysqli_result($con, $sql);

    return $content_types;
}

function is_content_type_valid(mysqli $con, string $type): bool {
    $sql = 'SELECT class_name FROM content_type';
    $content_types = get_mysqli_result($con, $sql);
    $class_names = array_column($content_types, 'class_name');

    return in_array($type, $class_names);
}

function get_post(mysqli $con, int $post_id): array {
    $sql = "SELECT
        p.dt_add, p.author_id, u.login AS author, u.avatar_path
        FROM post p
        INNER JOIN user u ON u.id = p.author_id
        WHERE p.id = $post_id";
    $post = get_mysqli_result($con, $sql, 'assoc');

    return $post;
}

function is_post_valid(mysqli $con, int $post_id): int {
    $sql = "SELECT id FROM post WHERE id = $post_id";
    $result = get_mysqli_result($con, $sql, false);

    return boolval(mysqli_num_rows($result));
}

function validate_post(mysqli $con, int $post_id): int {
    if (!is_post_valid($con, $post_id)) {
        http_response_code(404);
        exit;
    }

    return $post_id;
}

function get_post_comments(mysqli $con, int $post_id): array {
    $comments = filter_input(INPUT_GET, 'comments');
    $limit = !$comments || $comments !== 'all' ? ' LIMIT 2' : '';

    $sql = "SELECT
        c.id, c.dt_add, c.content, c.author_id, c.post_id,
        u.login, u.avatar_path
        FROM comment c
        INNER JOIN user u ON u.id = c.author_id
        WHERE post_id = $post_id
        ORDER BY c.dt_add DESC{$limit}";
    $comments = get_mysqli_result($con, $sql);

    return $comments;
}

function get_subscribers(mysqli $con): array {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT u.email, u.login
        FROM user u
        LEFT JOIN subscription s ON s.author_id = u.id
        WHERE s.user_id = $user_id";
    $subscribers = get_mysqli_result($con, $sql);

    return $subscribers;
}

function get_messages_count(mysqli $con, int $contact_id = null): string {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT COUNT(id) FROM message WHERE recipient_id = $user_id";
    $sql .= $contact_id ? " AND sender_id = $contact_id" : ' AND status = 0';
    $messages_count = get_mysqli_result($con, $sql, 'assoc')['COUNT(id)'];

    return $messages_count;
}

function get_message_preview(mysqli $con, int $contact_id): array {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT
        dt_add, content, sender_id
        FROM message
        WHERE (recipient_id = $user_id AND sender_id = $contact_id)
        OR (recipient_id = $contact_id AND sender_id = $user_id)
        ORDER BY dt_add DESC LIMIT 1";
    $message = get_mysqli_result($con, $sql, 'assoc');
    $preview = mb_substr($message['content'], 0, 30);
    $preview = $message['sender_id'] == $user_id ? "Вы: $preview" : $preview;

    return ['text' => $preview, 'time' => $message['dt_add']];
}

function get_contact_messages(mysqli $con, int $contact_id): array {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT
        m.id, m.dt_add, m.content, m.status, m.sender_id, m.recipient_id,
        u.login AS author, u.avatar_path
        FROM message m
        INNER JOIN user u ON u.id = m.sender_id
        WHERE (m.recipient_id = $user_id AND m.sender_id = $contact_id)
        OR (m.recipient_id = $contact_id AND m.sender_id = $user_id)
        ORDER BY m.dt_add";
    $messages = get_mysqli_result($con, $sql);

    return $messages;
}

function update_messages_status(mysqli $con, int $contact_id): void {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "UPDATE message SET status = 1
        WHERE sender_id = $contact_id AND recipient_id = $user_id";
    get_mysqli_result($con, $sql, false);
}

function is_contact_valid(mysqli $con, int $contact_id): bool {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT u.id
        FROM user u
        LEFT JOIN subscription s ON s.user_id = u.id AND s.author_id = $user_id
        LEFT JOIN message m ON m.recipient_id = $user_id AND m.sender_id = $contact_id
        WHERE u.id = $contact_id
        HAVING COUNT(s.id) > 0 OR COUNT(m.id) > 0";
    $contact = get_mysqli_result($con, $sql, false);

    return boolval(mysqli_num_rows($contact));
}

function add_new_contact(mysqli $con, array &$contacts, int $contact_id): bool {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT u.id, u.login, u.avatar_path
        FROM user u
        LEFT JOIN subscription s ON s.user_id = u.id AND s.author_id = $user_id
        WHERE u.id = $contact_id
        HAVING COUNT(s.id) > 0";
    $contact = get_mysqli_result($con, $sql, 'assoc');

    if ($contact) {
        array_unshift($contacts, $contact);
        return setcookie('new_contact', $contact_id);
    }

    return false;
}

function get_post_hashtags(mysqli $con, int $post_id): array {
    $sql = "SELECT h.name
        FROM hashtag h
        INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id
        INNER JOIN post p ON p.id = ph.post_id
        WHERE p.id = $post_id";
    $hashtags = get_mysqli_result($con, $sql);

    return $hashtags;
}

function get_form_inputs(mysqli $con, string $form): array {
    $sql = "SELECT
        i.id, i.label, i.name, i.placeholder, i.required,
        it.name AS type, f.name AS form
        FROM input i
        INNER JOIN input_type it ON it.id = i.type_id
        INNER JOIN form_input fi ON fi.input_id = i.id
        INNER JOIN form f ON f.id = fi.form_id
        WHERE f.name = '$form'";
    $form_inputs = get_mysqli_result($con, $sql);
    $input_names = array_column($form_inputs, 'name');
    $form_inputs = array_combine($input_names, $form_inputs);

    return $form_inputs;
}

function get_required_fields(mysqli $con, string $form, string $tab = ''): array {
    $sql = "SELECT i.name
        FROM input i
        INNER JOIN form_input fi ON fi.input_id = i.id
        INNER JOIN form f ON f.id = fi.form_id
        WHERE f.name = '$form' AND i.required = 1";
    $sql .= $form === 'adding-post' ? " AND f.modifier = '$tab'" : '';
    $required_fields = get_mysqli_result($con, $sql);

    return array_column($required_fields, 'name');
}

function validate_hashtag(mysqli $con, string $hashtag, int $post_id): void {
    if (!$tag_name = ltrim($hashtag, '#')) {
        return;
    }

    $tag_name = mysqli_real_escape_string($con, $tag_name);
    $sql = "SELECT COUNT(*), id FROM hashtag WHERE name = '$tag_name'";
    $hashtag = get_mysqli_result($con, $sql, 'assoc');
    mysqli_query($con, 'START TRANSACTION');

    if ($hashtag['COUNT(*)'] === '0') {
        $sql = "INSERT INTO hashtag SET name = '$tag_name'";
        $result1 = get_mysqli_result($con, $sql, false);
        $hashtag_id = mysqli_insert_id($con);
    } else {
        $hashtag_id = $hashtag['id'];
    }

    $sql = "INSERT INTO post_hashtag (hashtag_id, post_id) VALUES ($hashtag_id, $post_id)";
    $result2 = get_mysqli_result($con, $sql, false);

    if (($result1 ?? true) && $result2) {
        mysqli_query($con, 'COMMIT');
    } else {
        mysqli_query($con, 'ROLLBACK');
    }
}

function get_feed_posts(mysqli $con, string $filter): array {
    $user_id = intval($_SESSION['user']['id']);
    $post_fields = get_post_fields('p.');
    $sql = "SELECT
        COUNT(DISTINCT p2.id) AS repost_count,
        COUNT(DISTINCT c.id) AS comment_count,
        COUNT(DISTINCT pl.id) AS like_count,
        COUNT(DISTINCT pl2.id) AS is_like,
        {$post_fields}, u.login AS author, u.avatar_path, ct.class_name
        FROM post p
        LEFT JOIN user u ON u.id = p.author_id
        LEFT JOIN content_type ct ON ct.id = p.content_type_id
        LEFT JOIN post p2 ON p2.origin_post_id = p.id
        LEFT JOIN comment c ON c.post_id = p.id
        LEFT JOIN post_like pl ON pl.post_id = p.id
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
        LEFT JOIN subscription s ON s.user_id = p.author_id
        WHERE s.author_id = {$user_id}{$filter}
        GROUP BY p.id
        ORDER BY p.dt_add ASC";
    $posts = get_mysqli_result($con, $sql);

    for ($i = 0; $i < count($posts); $i++) {
        $hashtags = get_post_hashtags($con, $posts[$i]['id']);
        $posts[$i]['hashtags'] = $hashtags;
    }

    return $posts;
}

function get_user_by_email(mysqli $con, string $email): array {
    $user_fields = 'id, dt_add, email, login, password, avatar_path';
    $sql = "SELECT $user_fields FROM user WHERE email = '$email';";
    $user = get_mysqli_result($con, $sql, 'assoc');

    return $user;
}

function get_contacts(mysqli $con): array {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT
        COUNT(DISTINCT m2.id) AS unread_messages_count,
        u.id, u.login, u.avatar_path
        FROM message m
        LEFT JOIN user u ON u.id = m.sender_id OR u.id = m.recipient_id
        LEFT JOIN message m2 ON m2.recipient_id = $user_id AND m2.sender_id = u.id AND m2.status = 0
        WHERE (m.sender_id = $user_id OR m.recipient_id = $user_id) AND u.id != $user_id
        GROUP BY u.id
        ORDER BY MAX(m.dt_add) DESC";
    $contacts = get_mysqli_result($con, $sql);

    for ($i = 0; $i < count($contacts); $i++) {
        $contact_id = $contacts[$i]['id'];
        $contacts[$i]['preview'] = get_message_preview($con, $contact_id);
        $contacts[$i]['messages'] = get_contact_messages($con, $contact_id);
    }

    return $contacts;
}

function get_items_count(mysqli $con, string $filter): string {
    $sql = "SELECT COUNT(p.id) FROM post p
        LEFT JOIN content_type ct ON ct.id = p.content_type_id
        $content_type_filter";
    $items_count = get_mysqli_result($con, $sql, 'assoc')['COUNT(p.id)'];

    return $items_count;
}

function get_popular_posts(mysqli $con, string $filter1, string $filter2, int $offset): array {
    $user_id = intval($_SESSION['user']['id']);
    $post_fields = get_post_fields('p.');
    $sql = "SELECT
        COUNT(DISTINCT c.id) AS comment_count,
        COUNT(DISTINCT pl.id) AS like_count,
        COUNT(DISTINCT pl2.id) AS is_like,
        {$post_fields}, u.login AS author, u.avatar_path, ct.class_name
        FROM post p
        LEFT JOIN user u ON u.id = p.author_id
        LEFT JOIN content_type ct ON ct.id = p.content_type_id
        LEFT JOIN comment c ON c.post_id = p.id
        LEFT JOIN post_like pl ON pl.post_id = p.id
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
        $filter1
        GROUP BY p.id
        ORDER BY $filter2 LIMIT 6 OFFSET $offset";
    $posts = get_mysqli_result($con, $sql);

    return $posts;
}

function get_user_profile(mysqli $con, int $profile_id): array {
    $user_id = intval($_SESSION['user']['id']);
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

    return $user;
}

function get_profile_posts(mysqli $con, int $profile_id): array {
    $user_id = intval($_SESSION['user']['id']);
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

    return $posts;
}

function get_profile_likes(mysqli $con, int $profile_id): array {
    $post_fields = get_post_fields('p.');
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

    return $likes;
}

function get_profile_subscriptions(mysqli $con, int $profile_id): array {
    $user_id = intval($_SESSION['user']['id']);
    $sql = "SELECT
        COUNT(DISTINCT s2.id) AS is_subscription,
        COUNT(DISTINCT s3.id) AS subscriber_count,
        COUNT(DISTINCT p.id) AS publication_count,
        u.id, u.dt_add, u.login, u.avatar_path
        FROM subscription s
        LEFT JOIN user u ON u.id = s.user_id
        LEFT JOIN post p ON p.author_id = u.id
        LEFT JOIN subscription s2 ON s2.user_id = u.id AND s2.author_id = $user_id
        LEFT JOIN subscription s3 ON s3.user_id = u.id
        WHERE s.author_id = $profile_id
        GROUP BY s.id";
    $subscriptions = get_mysqli_result($con, $sql);

    return $subscriptions;
}
