<?php

require_once('init.php');

if (!isset($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}

$sql = 'SELECT * FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$sort_fields = ['popular', 'likes', 'date'];
$sort_types = array_fill_keys($sort_fields, 'desc');
$expires = strtotime('+30 days');
setcookie('sort', 'popular', $expires);
setcookie('dir', 'desc', $expires);

if (isset($_COOKIE['sort']) && isset($_COOKIE['dir'])) {

    if (isset($_GET['sort']) && in_array($_GET['sort'], $sort_fields) && $_COOKIE['sort'] == $_GET['sort']) {
        $sort = $_GET['sort'];

        $value = $_COOKIE['dir'] == 'desc' ? 'asc' : 'desc';
        $sort_types[$sort] = $value;
        setcookie('dir', $value, $expires);

    } elseif (isset($_GET['sort']) && in_array($_GET['sort'], $sort_fields)) {
        $sort = $_GET['sort'];

        $sort_types[$sort] = 'asc';
        setcookie('sort', $sort, $expires);
        setcookie('dir', 'asc', $expires);
    }
}

$content_type_filter = '';
if ($content_type = filter_input(INPUT_GET, 'filter')) {
    $content_type = mysqli_real_escape_string($link, $content_type);

    if (is_content_type_valid($link, $content_type)) {
        $content_type_filter = "WHERE ct.class_name = '$content_type' ";
    }
}

$sort_filter = 'p.show_count DESC';
if (isset($_GET['sort']) && isset($_GET['dir'])) {

    switch ($_GET['sort']) {
        case 'popular':
            $sort_filter = 'p.show_count ';
            break;
        case 'likes':
            $sort_filter = 'like_count ';
            break;
        case 'date':
            $sort_filter = 'p.dt_add ';
            break;
        default:
            $sort_filter = 'p.show_count ';
            break;
    }

    $sort_filter .= $_GET['dir'] == 'asc' ? 'ASC' : 'DESC';
}

$sql = 'SELECT p.*, COUNT(pl.id) AS like_count, u.login AS author, u.avatar_path, ct.class_name FROM post p '
     . 'LEFT JOIN user u ON u.id = p.author_id '
     . 'LEFT JOIN content_type ct ON ct.id = p.content_type_id '
     . 'LEFT JOIN post_like pl ON pl.post_id = p.id '

     . $content_type_filter

     . 'GROUP BY p.id '
     . "ORDER BY $sort_filter LIMIT 6";
$posts = get_mysqli_result($link, $sql);

$page_content = include_template('popular.php', [
    'sort_fields' => $sort_fields,
    'sort_types' => $sort_types,
    'content_types' => $content_types,
    'posts' => $posts,
    'link' => $link
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'popular',
    'title' => 'readme: популярное',
    'page_content' => $page_content
]);

print($layout_content);
