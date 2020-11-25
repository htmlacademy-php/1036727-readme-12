<?php

require_once 'init.php';

if (!$link) {
    $error = mysqli_connect_error($link);
    print("Ошибка подключения: $error");
    exit;
}

$mysqli_errors = [];
$content_types = [];

$sql = 'SELECT * FROM content_type';
$result = mysqli_query($link, $sql);

if ($result) {
    $content_types = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $mysqli_errors[] = mysqli_error($link);
}

$is_auth = rand(0, 1);

$user_name = 'Максим'; // укажите здесь ваше имя

$posts = [
    [
    'heading' => 'Цитата',
    'type' => 'post-quote',
    'content' => 'Мы в жизни любим только раз, а после ищем лишь похожих',
    'username' => 'Лариса',
    'avatar' => 'userpic-larisa-small.jpg'
    ],
    [
    'heading' => 'Игра престолов',
    'type' => 'post-text',
    'content' => 'Не могу дождаться начала финального сезона своего любимого сериала!',
    'username' => 'Владик',
    'avatar' => 'userpic.jpg'
    ],
    [
    'heading' => 'Наконец, обработал фотки!',
    'type' => 'post-photo',
    'content' => 'rock-medium.jpg',
    'username' => 'Виктор',
    'avatar' => 'userpic-mark.jpg'
    ],
    [
    'heading' => 'Моя мечта',
    'type' => 'post-photo',
    'content' => 'coast-medium.jpg',
    'username' => 'Лариса',
    'avatar' => 'userpic-larisa-small.jpg'
    ],
    [
    'heading' => 'Лучшие курсы',
    'type' => 'post-link',
    'content' => 'www.htmlacademy.ru',
    'username' => 'Владик',
    'avatar' => 'userpic.jpg'
    ]
];

$page_content = include_template('main.php', [
    'posts' => $posts
]);

$layout_content = include_template('layout.php', [
    'title' => 'readme: популярное',
    'page_content' => $page_content,
    'is_auth' => $is_auth,
    'username' => $user_name
]);

print($layout_content);
