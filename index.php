<?php

function get_text_content(string $text, int $num_letters = 300) : string {
    $text_length = mb_strlen($text);

    if ($text_length > $num_letters) {
        $words = explode(' ', $text);
        $result_words_length = 0;
        $result_words = [];

        foreach ($words as $word) {
            $result_words_length += mb_strlen($word);

            if ($result_words_length > $num_letters) {
                break;
            }

            $result_words_length += 1; // +1 пробельный символ
            $result_words[] = $word;
        }

        $result = implode(' ', $result_words);

        $result .= '...';
        $result = '<p>' . $result . '</p>';
        $result .= '<a class="post-text__more-link" href="#">Читать далее</a>';
    } else {
        $result = '<p>' . $text . '</p>';
    }

    return $result;
}

function esc(string $str, bool $remove_tags = false) : string {
    if ($remove_tags) {
        $text = strip_tags($str);
    } else {
        $text = htmlspecialchars($str);
    }

    return $text;
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
