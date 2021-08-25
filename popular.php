<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    $url = $_SERVER['REQUEST_URI'] ?? '/popular.php';
    $expires = strtotime('+30 days');
    setcookie('login_ref', $url, $expires);

    header('Location: /');
    exit;
}

$db = anatolev\Database::getInstance();

$user_id = $_SESSION['user']['id'];

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

$order = 'p.show_count DESC';
if (isset($_GET['sort'], $_GET['dir'])) {
    switch ($_GET['sort']) {
        case 'likes':
            $order = 'COUNT(pl.id) ';
            break;
        case 'date':
            $order = 'p.dt_add ';
            break;
        default:
            $order = 'p.show_count ';
            break;
    }

    $order .= $_GET['dir'] === 'asc' ? 'ASC' : 'DESC';
}

$page_items = 6;

$ctype = filter_input(INPUT_GET, 'filter') ?? '';
$current_page = intval(filter_input(INPUT_GET, 'page') ?? 1);
$items_count = $db->getItemsCount($ctype);
$pages_count = ceil($items_count / $page_items) ?: 1;

if ($current_page <= 0) {
    $current_page = 1;
} elseif ($current_page > $pages_count) {
    $current_page = $pages_count;
}

$offset = ($current_page - 1) * $page_items;

$stmt_data = [$user_id, $ctype, $page_items, $offset];
$posts = $db->getPopularPosts($stmt_data, $order);

$content_types = $db->getContentTypes();
$message_count = $db->getUnreadMessageCount();

setcookie('search_ref', '', time() - 3600);

$page_content = includeTemplate('popular.php', [
    'sort_fields' => $sort_fields,
    'sort_types' => $sort_types,
    'content_types' => $content_types,
    'posts' => $posts,
    'current_page' => $current_page,
    'pages_count' => $pages_count
]);

$layout_content = includeTemplate('layouts/base.php', [
    'title' => 'readme: популярное',
    'main_modifier' => 'popular',
    'page_content' => $page_content,
    'message_count' => $message_count
]);

print($layout_content);
