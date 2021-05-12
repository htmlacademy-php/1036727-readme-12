<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/popular.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$user_id = intval($_SESSION['user']['id']);

$content_types = get_content_types($con);

$sort_fields = ['popular', 'likes', 'date'];
$sort_types = array_fill_keys($sort_fields, 'desc');

$expires = strtotime('+30 days');
$sort = isset($_GET['sort'], $_COOKIE['sort']) ? $_COOKIE['sort'] : 'popular';
$value = isset($_GET['dir'], $_COOKIE['dir']) ? $_COOKIE['dir'] : 'desc';

setcookie('sort', $sort, $expires);
setcookie('dir', $value, $expires);
$request_uri = preg_replace('%&page=[0-9]+%', '', $_SERVER['REQUEST_URI']);
setcookie('request_uri', $request_uri, $expires);

if (isset($_COOKIE['sort']) && isset($_COOKIE['dir']) && $_COOKIE['request_uri'] !== $request_uri) {

    if (isset($_GET['sort']) && in_array($_GET['sort'], $sort_fields) && $_COOKIE['sort'] === $_GET['sort']) {
        $sort = $_GET['sort'];

        $value = $_COOKIE['dir'] === 'desc' ? 'asc' : 'desc';
        $sort_types[$sort] = $value;
        setcookie('dir', $value, $expires);

    } elseif (isset($_GET['sort']) && in_array($_GET['sort'], $sort_fields)) {
        list($sort, $value) = [$_GET['sort'], 'asc'];

        setcookie('sort', $sort, $expires);
        setcookie('dir', 'asc', $expires);
    }
}

$sort_types[$sort] = $value;

$content_type_filter = '';
$content_type = filter_input(INPUT_GET, 'filter')
$content_type = mysqli_real_escape_string($con, $content_type);

if (is_content_type_valid($con, $content_type)) {
    $content_type_filter = "WHERE ct.class_name = '$content_type' ";
}

$sort_filter = 'p.show_count DESC';
if (isset($_GET['sort']) && isset($_GET['dir'])) {

    switch ($_GET['sort']) {
        case 'popular':
            $sort_filter = 'p.show_count ';
            break;
        case 'likes':
            $sort_filter = 'COUNT(pl.id) ';
            break;
        case 'date':
            $sort_filter = 'p.dt_add ';
            break;
        default:
            $sort_filter = 'p.show_count ';
            break;
    }

    $sort_filter .= $_GET['dir'] === 'asc' ? 'ASC' : 'DESC';
}

$current_page = intval(filter_input(INPUT_GET, 'page') ?? 1);
$page_items = 6;

$sql = "SELECT COUNT(p.id) FROM post p
    LEFT JOIN content_type ct ON ct.id = p.content_type_id
    $content_type_filter";
$items_count = get_mysqli_result($con, $sql, 'assoc')['COUNT(p.id)'];
$pages_count = ceil($items_count / $page_items) ?: 1;

if ($current_page <= 0) {
    $current_page = 1;
} elseif ($current_page > $pages_count) {
    $current_page = $pages_count;
}

$offset = ($current_page - 1) * $page_items;

$data = [$content_type_filter, $sort_filter, $offset];
$posts = get_popular_posts($con, ...$data);

$page_content = include_template('popular.php', [
    'sort_fields' => $sort_fields,
    'sort_types' => $sort_types,
    'content_types' => $content_types,
    'posts' => $posts,
    'current_page' => $current_page,
    'pages_count' => $pages_count
]);

$messages_count = get_messages_count($con);
$layout_content = include_template('layout.php', [
    'title' => 'readme: популярное',
    'main_modifier' => 'popular',
    'page_content' => $page_content,
    'messages_count' => $messages_count
]);

print($layout_content);
