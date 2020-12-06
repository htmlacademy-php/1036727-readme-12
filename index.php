<?php

require_once('init.php');

$sql = 'SELECT * FROM content_type';
$content_types = get_mysqli_result($link, $sql);

$sort_types = ['popular', 'likes', 'date'];
$sort_dir = array_fill_keys($sort_types, 'desc');

setcookie('sort', 'popular');
setcookie('dir', 'desc');

if (isset($_COOKIE['sort']) && isset($_COOKIE['dir'])) {

    if (isset($_GET['sort']) && in_array($_GET['sort'], $sort_types) && $_COOKIE['sort'] == $_GET['sort']) {
        $sort = $_GET['sort'];

        $value = $_COOKIE['dir'] == 'desc' ? 'asc' : 'desc';
        $sort_dir[$sort] = $value;
        setcookie('dir', $value);

    } elseif (isset($_GET['sort']) && in_array($_GET['sort'], $sort_types)) {
        $sort = $_GET['sort'];

        $sort_dir[$sort] = 'asc';
        setcookie('sort', $sort);
        setcookie('dir', 'asc');
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
     . 'LEFT JOIN user u ON p.author_id = u.id '
     . 'LEFT JOIN content_type ct ON p.content_type_id = ct.id '
     . 'LEFT JOIN post_like pl ON p.id = pl.post_id '

     . $content_type_filter

     . 'GROUP BY p.id '
     . "ORDER BY $sort_filter LIMIT 6";
$posts = get_mysqli_result($link, $sql);

$is_auth = rand(0, 1);
$user_name = 'Максим'; // укажите здесь ваше имя

$page_content = include_template('main.php', [
    'dir' => $sort_dir,
    'content_types' => $content_types,
    'posts' => $posts
]);

$layout_content = include_template('layout.php', [
    'page_main_class' => 'popular',
    'title' => 'readme: популярное',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
