<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/search.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

if (!$search = trim(filter_input(INPUT_GET, 'q'))) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

    header("Location: $ref");
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$posts = [];

if (substr($search, 0, 1) === '#') {
    if ($hashtag = substr($search, 1)) {
        $hashtag = mysqli_real_escape_string($con, $hashtag);
        $post_fields = get_post_fields('p.');
        $user_fields = 'u.login AS author, u.avatar_path';
        $sql = "SELECT
            COUNT(DISTINCT p2.id) AS repost_count,
            COUNT(DISTINCT c.id) AS comment_count,
            COUNT(DISTINCT pl.id) AS like_count,
            COUNT(DISTINCT pl2.id) AS is_like,
            {$post_fields}, {$user_fields}, ct.class_name
            FROM post p
            LEFT JOIN user u ON u.id = p.author_id
            LEFT JOIN content_type ct ON ct.id = p.content_type_id
            LEFT JOIN post p2 ON p2.origin_post_id = p.id
            LEFT JOIN comment c ON c.post_id = p.id
            LEFT JOIN post_like pl ON pl.post_id = p.id
            LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
            LEFT JOIN post_hashtag ph ON ph.post_id = p.id
            LEFT JOIN hashtag h ON h.id = ph.hashtag_id
            WHERE h.name = '$hashtag'
            GROUP BY p.id
            ORDER BY p.dt_add DESC";
        $posts = get_mysqli_result($con, $sql);
    }

} else {
    $search_words = [];

    foreach (explode(' ', $search) as $search_word) {
        $search_word = ltrim($search_word, '+-<>');
        $search_word = rtrim($search_word, '+-<>*');
        if (mb_strlen($search_word) >= 3) {
            $search_words[] = "{$search_word}*";
        }
    }

    if ($request = implode(' ', $search_words)) {
        $request = mysqli_real_escape_string($con, $request);
        $post_fields = get_post_fields('p.');
        $user_fields = 'u.login AS author, u.avatar_path';
        $sql = "SELECT
            COUNT(DISTINCT p2.id) AS repost_count,
            COUNT(DISTINCT c.id) AS comment_count,
            COUNT(DISTINCT pl.id) AS like_count,
            COUNT(DISTINCT pl2.id) AS is_like,
            MATCH (p.title, p.text_content) AGAINST ('$request') AS score,
            {$post_fields}, {$user_fields}, ct.class_name
            FROM post p
            LEFT JOIN user u ON u.id = p.author_id
            LEFT JOIN content_type ct ON ct.id = p.content_type_id
            LEFT JOIN post p2 ON p2.origin_post_id = p.id
            LEFT JOIN comment c ON c.post_id = p.id
            LEFT JOIN post_like pl ON pl.post_id = p.id
            LEFT JOIN post_like pl2 ON pl2.post_id = p.id AND pl2.author_id = $user_id
            WHERE MATCH (p.title, p.text_content) AGAINST ('$request' IN BOOLEAN MODE)
            GROUP BY p.id
            ORDER BY score DESC";
        $posts = get_mysqli_result($con, $sql);
    }
}

for ($i = 0; $i < count($posts); $i++) {
    $hashtags = get_post_hashtags($con, $posts[$i]['id']);
    $posts[$i]['hashtags'] = $hashtags;
}

$search_ref = $_COOKIE['search_ref'] ?? null;
$ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php';

if (empty($posts) && is_null($search_ref)) {
    setcookie('search_ref', $ref, strtotime('+30 days'));
} elseif (!empty($posts) && !is_null($search_ref)) {
    setcookie('search_ref', $search_ref, time() - 3600);
}

$page_content = include_template('search.php', [
    'posts' => $posts
]);

$messages_count = get_messages_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: страница результатов поиска',
    'main_modifier' => 'search-results',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
