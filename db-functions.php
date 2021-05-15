<?php

function get_mysqli_result(mysqli $con, string $sql, array $stmt_data = []): array {
    $stmt = execute_query($con, $sql, $stmt_data);
    if (!$result = mysqli_stmt_get_result($stmt)) {
        http_response_code(500);
        exit;
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function get_mysqli_num_rows(mysqli $con, string $sql, array $stmt_data = []): int {
    $stmt = execute_query($con, $sql, $stmt_data);
    if (!$result = mysqli_stmt_get_result($stmt)) {
        http_response_code(500);
        exit;
    }

    return mysqli_num_rows($result);
}

function execute_query(mysqli $con, string $sql, array $stmt_data = []): mysqli_stmt {
    $stmt = db_get_prepare_stmt($con, $sql, $stmt_data);
    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        exit;
    }

    return $stmt;
}

function get_post_comments(mysqli $con, int $post_id): array {
    $stmt_data = [$post_id];
    $limit = filter_input(INPUT_GET, 'comments');
    $sql = "SELECT
        c.id, c.dt_add, c.content, c.author_id, c.post_id,
        u.login, u.avatar_path
        FROM comment c
        INNER JOIN user u ON u.id = c.author_id
        WHERE post_id = ?
        ORDER BY c.dt_add DESC";
    intval($limit) > 0 && ($stmt_data[] = $limit) && $sql .= ' LIMIT ?';
    $comments = get_mysqli_result($con, $sql, $stmt_data);

    return $comments;
}

function get_content_types(mysqli $con): array {
    $sql = 'SELECT
        id, type_name, class_name, icon_width, icon_height
        FROM content_type';

    return get_mysqli_result($con, $sql);
}

function get_form_inputs(mysqli $con, string $form): array {
    $sql = 'SELECT
        i.id, i.label, i.name, i.placeholder, i.required,
        it.name AS type, f.name AS form
        FROM input i
        INNER JOIN input_type it ON it.id = i.type_id
        INNER JOIN form_input fi ON fi.input_id = i.id
        INNER JOIN form f ON f.id = fi.form_id
        WHERE f.name = ?';
    $form_inputs = get_mysqli_result($con, $sql, [$form]);
    $input_names = array_column($form_inputs, 'name');
    $form_inputs = array_combine($input_names, $form_inputs);

    return $form_inputs;
}

function is_content_type_valid(mysqli $con, string $content_type): bool {
    $sql = 'SELECT class_name FROM content_type';
    $content_types = get_mysqli_result($con, $sql);
    $class_names = array_column($content_types, 'class_name');

    return in_array($content_type, $class_names);
}

function get_required_fields(mysqli $con, string $form, string $tab = ''): array {
    $stmt_data = [$form];
    $sql = 'SELECT i.name
        FROM input i
        INNER JOIN form_input fi ON fi.input_id = i.id
        INNER JOIN form f ON f.id = fi.form_id
        WHERE f.name = ? AND i.required = 1';

    $form === 'adding-post'
        && ($stmt_data[] = $tab) && $sql .= ' AND f.modifier = ?';
    $required_fields = get_mysqli_result($con, $sql, $stmt_data);

    return array_column($required_fields, 'name');
}

function insert_post(mysqli $con, array $stmt_data): int {
    $post_fields = get_post_fields('', 'insert');
    $sql = "INSERT INTO post ($post_fields) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    execute_query($con, $sql, $stmt_data);

    return mysqli_insert_id($con);
}

function get_hashtag_by_name(mysqli $con, string $name): ?array {
    $sql = 'SELECT id FROM hashtag WHERE name = ?';

    return get_mysqli_result($con, $sql, [$name])[0] ?? null;
}

function insert_hashtag(mysqli $con, string $hashtag): int {
    $sql = 'INSERT INTO hashtag SET name = ?';
    execute_query($con, $sql, [$hashtag]);

    return mysqli_insert_id($con);
}

function validate_hashtag(mysqli $con, string $hashtag, int $post_id): void {
    if (!$tag_name = ltrim($hashtag, '#')) {
        return;
    }

    $hashtag = get_hashtag_by_name($con, $tag_name);
    mysqli_query($con, 'START TRANSACTION');

    if (!$hashtag) {
        $hashtag_id = insert_hashtag($con, $tag_name);
    } else {
        $hashtag_id = $hashtag['id'];
    }

    insert_post_hashtag($con, [$hashtag_id, $post_id]);

    if (($result1 ?? true) && $result2) {
        mysqli_query($con, 'COMMIT');
    } else {
        mysqli_query($con, 'ROLLBACK');
    }
}

function get_subscribers(mysqli $con): array {
    $user_id = $_SESSION['user']['id'];
    $sql = 'SELECT u.email, u.login
        FROM user u
        LEFT JOIN subscription s ON s.author_id = u.id
        WHERE s.user_id = ?';
    $subscribers = get_mysqli_result($con, $sql, [$user_id]);

    return $subscribers;
}

function get_message_count(mysqli $con): string {
    $stmt_data = [$_SESSION['user']['id']];
    $sql = 'SELECT COUNT(id) FROM message WHERE recipient_id = ? AND status = 0';

    return get_mysqli_result($con, $sql, $stmt_data)[0]['COUNT(id)'];
}

function get_feed_posts(mysqli $con, string $content_type): array {
    $filter = '';
    $post_fields = get_post_fields('p.');
    $stmt_data = [$_SESSION['user']['id'], $_SESSION['user']['id']];

    if (is_content_type_valid($con, $content_type)) {
        $stmt_data[] = $content_type;
        $filter = ' AND ct.class_name = ?';
    }

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
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = ?
        LEFT JOIN subscription s ON s.user_id = p.author_id
        WHERE s.author_id = ?{$filter}
        GROUP BY p.id
        ORDER BY p.dt_add ASC";
    $posts = get_mysqli_result($con, $sql, $stmt_data);

    for ($i = 0; $i < count($posts); $i++) {
        $hashtags = get_post_hashtags($con, $posts[$i]['id']);
        $posts[$i]['hashtags'] = $hashtags;
    }

    return $posts;
}

function get_user_by_email(mysqli $con, string $email): ?array {
    $user_fields = 'id, dt_add, email, login, password, avatar_path';
    $sql = "SELECT $user_fields FROM user WHERE email = ?";
    $user = get_mysqli_result($con, $sql, [$email])[0] ?? null;

    return $user;
}

function is_post_valid(mysqli $con, int $post_id): bool {
    $sql = 'SELECT id FROM post WHERE id = ?';

    return get_mysqli_num_rows($con, $sql, [$post_id]);
}

function validate_post(mysqli $con, int $post_id): int {
    if (!is_post_valid($con, $post_id)) {
        http_response_code(404);
        exit;
    }

    return $post_id;
}

function is_post_like(mysqli $con, int $post_id): bool {
    $stmt_data = [$post_id, $_SESSION['user']['id']];
    $sql = 'SELECT id FROM post_like WHERE post_id = ? AND author_id = ?';

    return get_mysqli_num_rows($con, $sql, $stmt_data);
}

function insert_post_like(mysqli $con, int $post_id): void {
    $stmt_data = [$_SESSION['user']['id'], $post_id];
    $sql = 'INSERT INTO post_like (author_id, post_id) VALUES (?, ?)';

    execute_query($con, $sql, $stmt_data);
}

function delete_post_like(mysqli $con, int $post_id): void {
    $stmt_data = [$post_id, $_SESSION['user']['id']];
    $sql = 'DELETE FROM post_like WHERE post_id = ? AND author_id = ?';

    execute_query($con, $sql, $stmt_data);
}

function is_user_valid(mysqli $con, int $user_id): bool {
    $sql = 'SELECT id FROM user WHERE id = ?';

    return get_mysqli_num_rows($con, $sql, [$user_id]);
}

function validate_user(mysqli $con, int $user_id): int {
    if (!is_user_valid($con, $user_id)) {
        http_response_code(404);
        exit;
    }

    return $user_id;
}

function is_contact_valid(mysqli $con, int $contact_id): bool {
    $user_id = $_SESSION['user']['id'];
    $stmt_data = [$user_id, $user_id, $contact_id, $contact_id];
    $sql = 'SELECT u.id
        FROM user u
        LEFT JOIN subscription s ON s.user_id = u.id AND s.author_id = ?
        LEFT JOIN message m ON m.recipient_id = ? AND m.sender_id = ?
        WHERE u.id = ?
        GROUP BY u.id
        HAVING COUNT(s.id) > 0 OR COUNT(m.id) > 0';

    return get_mysqli_num_rows($con, $sql, $stmt_data);
}

function insert_message(mysqli $con, array $stmt_data): void {
    $message_fields = 'content, sender_id, recipient_id';
    $sql = "INSERT INTO message ($message_fields) VALUES (?, ?, ?)";

    execute_query($con, $sql, $stmt_data);
}

function update_messages_status(mysqli $con, int $contact_id): void {
    $stmt_data = [$contact_id, $_SESSION['user']['id']];
    $sql = 'UPDATE message SET status = 1
        WHERE sender_id = ? AND recipient_id = ?';

    execute_query($con, $sql, $stmt_data);
}

function get_message_preview(mysqli $con, int $contact_id): array {
    $user_id = $_SESSION['user']['id'];
    $stmt_data = [$user_id, $contact_id, $contact_id, $user_id];
    $sql = 'SELECT
        dt_add, content, sender_id
        FROM message
        WHERE (recipient_id = ? AND sender_id = ?)
        OR (recipient_id = ? AND sender_id = ?)
        ORDER BY dt_add DESC LIMIT 1';
    $message = get_mysqli_result($con, $sql, $stmt_data)[0];
    $preview = mb_substr($message['content'], 0, 30);
    $preview = $message['sender_id'] == $user_id ? "Вы: $preview" : $preview;

    return ['text' => $preview, 'time' => $message['dt_add']];
}

function get_contact_messages(mysqli $con, int $contact_id): array {
    $user_id = $_SESSION['user']['id'];
    $stmt_data = [$user_id, $contact_id, $contact_id, $user_id];
    $sql = 'SELECT
        m.id, m.dt_add, m.content, m.status, m.sender_id, m.recipient_id,
        u.login AS author, u.avatar_path
        FROM message m
        INNER JOIN user u ON u.id = m.sender_id
        WHERE (m.recipient_id = ? AND m.sender_id = ?)
        OR (m.recipient_id = ? AND m.sender_id = ?)
        ORDER BY m.dt_add';
    $messages = get_mysqli_result($con, $sql, $stmt_data);

    return $messages;
}

function get_contacts(mysqli $con): array {
    $user_id = $_SESSION['user']['id'];
    $stmt_data = [$user_id, $user_id, $user_id, $user_id];
    $sql = 'SELECT
        COUNT(DISTINCT m2.id) AS unread_messages_count,
        u.id, u.login, u.avatar_path
        FROM message m
        LEFT JOIN user u ON u.id = m.sender_id OR u.id = m.recipient_id
        LEFT JOIN message m2 ON m2.recipient_id = ? AND m2.sender_id = u.id AND m2.status = 0
        WHERE (m.sender_id = ? OR m.recipient_id = ?) AND u.id != ?
        GROUP BY u.id
        ORDER BY MAX(m.dt_add) DESC';
    $contacts = get_mysqli_result($con, $sql, $stmt_data);

    for ($i = 0; $i < count($contacts); $i++) {
        $contact_id = $contacts[$i]['id'];
        $contacts[$i]['preview'] = get_message_preview($con, $contact_id);
        $contacts[$i]['messages'] = get_contact_messages($con, $contact_id);
    }

    return $contacts;
}

function add_new_contact(mysqli $con, array &$contacts, int $contact_id): bool {
    $stmt_data = [$_SESSION['user']['id'], $contact_id];
    $sql = 'SELECT u.id, u.login, u.avatar_path
        FROM user u
        LEFT JOIN subscription s ON s.user_id = u.id AND s.author_id = ?
        WHERE u.id = ?
        GROUP BY u.id
        HAVING COUNT(s.id) > 0';
    $contact = get_mysqli_result($con, $sql, $stmt_data)[0] ?? null;

    if ($contact) {
        array_unshift($contacts, $contact);
        return setcookie('new_contact', $contact_id);
    }

    return false;
}

function get_items_count(mysqli $con, string $content_type): int {
    $filter = '';
    $stmt_data = [];

    if (is_content_type_valid($con, $content_type)) {
        $stmt_data[] = $content_type;
        $filter = ' WHERE ct.class_name = ?';
    }

    $sql = "SELECT COUNT(p.id)
        FROM post p
        LEFT JOIN content_type ct ON ct.id = p.content_type_id{$filter}";
    $items_count = get_mysqli_result($con, $sql, $stmt_data)[0]['COUNT(p.id)'];

    return $items_count;
}

function get_popular_posts(mysqli $con, array $stmt_data, string $ctype, string $order): array {
    $filter = '';
    $post_fields = get_post_fields('p.');

    if (is_content_type_valid($con, $ctype)) {
        array_splice($stmt_data, 1, 0, $ctype);
        $filter = ' WHERE ct.class_name = ?';
    }

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
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = ?
        $filter
        GROUP BY p.id
        ORDER BY $order LIMIT ? OFFSET ?";
    $posts = get_mysqli_result($con, $sql, $stmt_data);

    return $posts;
}

function insert_comment(mysqli $con, array $stmt_data): void {
    $comment_fields = 'content, author_id, post_id';
    $sql = "INSERT INTO comment ($comment_fields) VALUES (?, ?, ?)";

    execute_query($con, $sql, $stmt_data);
}

function get_post_author_id(mysqli $con, int $post_id): int {
    $sql = 'SELECT author_id FROM post WHERE id = ?';
    $author_id = get_mysqli_result($con, $sql, [$post_id])[0]['author_id'];

    return $author_id;
}

function update_post_show_count(mysqli $con, int $post_id): void {
    $sql = 'UPDATE post SET show_count = show_count + 1 WHERE id = ?';

    execute_query($con, $sql, [$post_id]);
}

function get_post_details(mysqli $con, int $post_id): array {
    $stmt_data = [$_SESSION['user']['id'], $post_id];
    $post_fields = get_post_fields('p.');
    $sql = "SELECT
        COUNT(DISTINCT p2.id) AS repost_count,
        COUNT(DISTINCT c.id) AS comment_count,
        COUNT(DISTINCT pl.id) AS like_count,
        COUNT(DISTINCT pl2.id) AS is_like,
        {$post_fields}, ct.class_name
        FROM post p
        LEFT JOIN user u ON u.id = p.author_id
        LEFT JOIN content_type ct ON ct.id = p.content_type_id
        LEFT JOIN post p2 ON p2.origin_post_id = p.id
        LEFT JOIN comment c ON c.post_id = p.id
        LEFT JOIN post_like pl ON pl.post_id = p.id
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = ?
        WHERE p.id = ?
        GROUP BY p.id";
    $post = get_mysqli_result($con, $sql, $stmt_data)[0];
    $post['display_mode'] = 'details';

    return $post;
}

function get_post_author(mysqli $con, int $post_id): array {
    $stmt_data = [$_SESSION['user']['id'], $post_id];
    $sql = 'SELECT
        COUNT(DISTINCT s.id) AS is_subscription,
        COUNT(DISTINCT s2.id) AS subscriber_count,
        COUNT(DISTINCT p2.id) AS publication_count,
        u.dt_add, u.login, u.avatar_path
        FROM post p
        LEFT JOIN user u ON u.id = p.author_id
        LEFT JOIN post p2 ON p2.author_id = p.author_id
        LEFT JOIN subscription s ON s.user_id = p.author_id AND s.author_id = ?
        LEFT JOIN subscription s2 ON s2.user_id = p.author_id
        WHERE p.id = ?
        GROUP BY p.id';
    $post_author = get_mysqli_result($con, $sql, $stmt_data)[0];

    return $post_author;
}

function get_post_hashtags(mysqli $con, int $post_id): array {
    $sql = 'SELECT h.name
        FROM hashtag h
        INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id
        INNER JOIN post p ON p.id = ph.post_id
        WHERE p.id = ?';
    $hashtags = get_mysqli_result($con, $sql, [$post_id]);

    return $hashtags;
}

function get_user_profile(mysqli $con, int $profile_id): array {
    $stmt_data = [$_SESSION['user']['id'], $profile_id];
    $sql = 'SELECT
        COUNT(DISTINCT s.id) AS is_subscription,
        COUNT(DISTINCT s2.id) AS subscriber_count,
        COUNT(DISTINCT p.id) AS publication_count,
        u.id, u.dt_add, u.login, u.avatar_path
        FROM user u
        LEFT JOIN post p ON p.author_id = u.id
        LEFT JOIN subscription s ON s.user_id = u.id AND s.author_id = ?
        LEFT JOIN subscription s2 ON s2.user_id = u.id
        WHERE u.id = ?
        GROUP BY u.id';
    $user = get_mysqli_result($con, $sql, $stmt_data)[0];

    return $user;
}

function get_repost(mysqli $con, int $post_id): array {
    $sql = 'SELECT
        p.dt_add, p.author_id, u.login AS author, u.avatar_path
        FROM post p
        INNER JOIN user u ON u.id = p.author_id
        WHERE p.id = ?';
    $post = get_mysqli_result($con, $sql, [$post_id])[0];

    return $post;
}

function get_profile_posts(mysqli $con, int $profile_id): array {
    $stmt_data = [$_SESSION['user']['id'], $profile_id];
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
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = ?
        WHERE p.author_id = ?
        GROUP BY p.id
        ORDER BY p.dt_add ASC";
    $posts = get_mysqli_result($con, $sql, $stmt_data);

    for ($i = 0; $i < count($posts); $i++) {
        $post = $posts[$i];
        $posts[$i]['hashtags'] = get_post_hashtags($con, $post['id']);
        $posts[$i]['comments'] = get_post_comments($con, $post['id']);

        $is_repost = $post['is_repost'] && $post_id = $post['origin_post_id'];
        $posts[$i]['origin'] = $is_repost ? get_repost($con, $post_id) : [];
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
        WHERE p.author_id = ?
        GROUP BY p.id, pl.id, u.id
        HAVING COUNT(pl.id) > 0
        ORDER BY pl.dt_add DESC";
    $likes = get_mysqli_result($con, $sql, [$profile_id]);

    return $likes;
}

function get_profile_subscriptions(mysqli $con, int $profile_id): array {
    $stmt_data = [$_SESSION['user']['id'], $profile_id];
    $sql = 'SELECT
        COUNT(DISTINCT s2.id) AS is_subscription,
        COUNT(DISTINCT s3.id) AS subscriber_count,
        COUNT(DISTINCT p.id) AS publication_count,
        u.id, u.dt_add, u.login, u.avatar_path
        FROM subscription s
        LEFT JOIN user u ON u.id = s.user_id
        LEFT JOIN post p ON p.author_id = u.id
        LEFT JOIN subscription s2 ON s2.user_id = u.id AND s2.author_id = ?
        LEFT JOIN subscription s3 ON s3.user_id = u.id
        WHERE s.author_id = ?
        GROUP BY s.id';
    $subscriptions = get_mysqli_result($con, $sql, $stmt_data);

    return $subscriptions;
}

function insert_user(mysqli $con, array $stmt_data): void {
    $user_fields = 'email, login, password, avatar_path';
    $sql = "INSERT INTO user ($user_fields) VALUES (?, ?, ?, ?)";

    execute_query($con, $sql, $stmt_data);
}

function get_post(mysqli $con, int $post_id): array {
    $post_fields = get_post_fields('', 'insert');
    $sql = "SELECT $post_fields FROM post WHERE id = ?";
    $repost = get_mysqli_result($con, $sql, [$post_id])[0];

    return $repost;
}

function get_post_hashtag_ids(mysqli $con, int $post_id): array {
    $sql = 'SELECT hashtag_id AS id FROM post_hashtag WHERE post_id = ?';
    $hashtag_ids = get_mysqli_result($con, $sql, [$post_id]);

    return $hashtag_ids;
}

function insert_post_hashtag(mysqli $con, array $stmt_data): void {
    $sql = 'INSERT INTO post_hashtag (hashtag_id, post_id) VALUES (?, ?)';

    execute_query($con, $sql, $stmt_data);
}

function get_posts_by_hashtag(mysqli $con, string $hashtag): array {
    $stmt_data = [$_SESSION['user']['id'], $hashtag];
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
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = ?
        LEFT JOIN post_hashtag ph ON ph.post_id = p.id
        LEFT JOIN hashtag h ON h.id = ph.hashtag_id
        WHERE h.name = ?
        GROUP BY p.id
        ORDER BY p.dt_add DESC";
    $posts = get_mysqli_result($con, $sql, $stmt_data);

    for ($i = 0; $i < count($posts); $i++) {
        $hashtags = get_post_hashtags($con, $posts[$i]['id']);
        $posts[$i]['hashtags'] = $hashtags;
    }

    return $posts;
}

function get_posts_by_query_string(mysqli $con, string $query): array {
    $stmt_data = [$query, $_SESSION['user']['id'], $query];
    $post_fields = get_post_fields('p.');
    $sql = "SELECT
        COUNT(DISTINCT p2.id) AS repost_count,
        COUNT(DISTINCT c.id) AS comment_count,
        COUNT(DISTINCT pl.id) AS like_count,
        COUNT(DISTINCT pl2.id) AS is_like,
        MATCH (p.title, p.text_content) AGAINST (?) AS score,
        {$post_fields}, u.login AS author, u.avatar_path, ct.class_name
        FROM post p
        LEFT JOIN user u ON u.id = p.author_id
        LEFT JOIN content_type ct ON ct.id = p.content_type_id
        LEFT JOIN post p2 ON p2.origin_post_id = p.id
        LEFT JOIN comment c ON c.post_id = p.id
        LEFT JOIN post_like pl ON pl.post_id = p.id
        LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = ?
        WHERE MATCH (p.title, p.text_content) AGAINST (? IN BOOLEAN MODE)
        GROUP BY p.id
        ORDER BY score DESC";
    $posts = get_mysqli_result($con, $sql, $stmt_data);

    for ($i = 0; $i < count($posts); $i++) {
        $hashtags = get_post_hashtags($con, $posts[$i]['id']);
        $posts[$i]['hashtags'] = $hashtags;
    }

    return $posts;
}

function is_subscription(mysqli $con, int $profile_id): bool {
    $stmt_data = [$_SESSION['user']['id'], $profile_id];
    $sql = 'SELECT id FROM subscription
        WHERE author_id = ? AND user_id = ?';

    return get_mysqli_num_rows($con, $sql, $stmt_data);
}

function insert_subscription(mysqli $con, int $profile_id): void {
    $stmt_data = [$_SESSION['user']['id'], $profile_id];
    $sql = 'INSERT INTO subscription (author_id, user_id) VALUES (?, ?)';

    execute_query($con, $sql, $stmt_data);
}

function delete_subscription(mysqli $con, int $profile_id): void {
    $stmt_data = [$_SESSION['user']['id'], $profile_id];
    $sql = 'DELETE FROM subscription WHERE author_id = ? AND user_id = ?';

    execute_query($con, $sql, $stmt_data);
}

function get_subscription(mysqli $con, int $profile_id): array {
    $sql = 'SELECT email, login FROM user WHERE id = ?';

    return get_mysqli_result($con, $sql, [$profile_id])[0];
}
