<?php

require_once('init.php');

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
