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

function esc(string $str) : string {
    $text = htmlspecialchars($str);

    return $text;
}

function get_post_time(string $date) : string {
    $ts_diff = time() - strtotime($date);

    if ($ts_diff < 3600) {
        $minutes = floor($ts_diff / 60);
        $result = "$minutes " . get_noun_plural_form($minutes, 'минута', 'минуты', 'минут') . ' назад';

    } elseif ($ts_diff >= 3600 && $ts_diff < 86400) {
        $hours = floor($ts_diff / 3600);
        $result = "$hours " . get_noun_plural_form($hours, 'час', 'часа', 'часов') . ' назад';

    } elseif ($ts_diff >= 86400 && $ts_diff < 604800) {
        $days = floor($ts_diff / 86400);
        $result = "$days " . get_noun_plural_form($days, 'день', 'дня', 'дней') . ' назад';

    } elseif ($ts_diff >= 604800 && $ts_diff < 2419200) {
        $weeks = floor($ts_diff / 604800);
        $result = "$weeks " . get_noun_plural_form($weeks, 'неделя', 'недели', 'недель') . ' назад';

    } elseif ($ts_diff >= 2419200) {
        $months = floor($ts_diff / 2419200);
        $result = "$months " . get_noun_plural_form($months, 'месяц', 'месяца', 'месяцев') . ' назад';
    }

    return $result;
}

function get_post_title_attr(string $date) : string {
    $ts = strtotime($date);
    $result = date('d.m.Y H:i', $ts);

    return $result;
}