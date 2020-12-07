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

    if ($ts_diff < 60) {
        $relative_time = "$ts_diff " . get_noun_plural_form($ts_diff, 'секунда', 'секунды', 'секунд') . ' назад';

    } elseif ($ts_diff < 3600) {
        $minutes = floor($ts_diff / 60);
        $relative_time = "$minutes " . get_noun_plural_form($minutes, 'минута', 'минуты', 'минут') . ' назад';

    } elseif ($ts_diff < 86400) {
        $hours = floor($ts_diff / 3600);
        $relative_time = "$hours " . get_noun_plural_form($hours, 'час', 'часа', 'часов') . ' назад';

    } elseif ($ts_diff < 604800) {
        $days = floor($ts_diff / 86400);
        $relative_time = "$days " . get_noun_plural_form($days, 'день', 'дня', 'дней') . ' назад';

    } elseif ($ts_diff < 3024000) {
        $weeks = floor($ts_diff / 604800);
        $relative_time = "$weeks " . get_noun_plural_form($weeks, 'неделя', 'недели', 'недель') . ' назад';

    } elseif ($ts_diff >= 3024000) {
        $dt_diff = date_diff(date_create($date), date_create('now'));
        $months = date_interval_format($dt_diff, '%m');
        $relative_time = "$months " . get_noun_plural_form($months, 'месяц', 'месяца', 'месяцев') . ' назад';
    }

    return $relative_time;
}

function get_time_title(string $date) : string {
    $ts = strtotime($date);
    $result = date('d.m.Y H:i', $ts);

    return $result;
}

function get_mysqli_result(mysqli $link, string $sql, string $type = 'all') : array {
    $result = mysqli_query($link, $sql);
    $mysqli_result = [];

    if (!$result && ini_get('display_errors')) {
        $error = mysqli_error($link);
        print("Ошибка MySQL: $error");

    } elseif ($result && $type == 'all') {
        $mysqli_result = mysqli_fetch_all($result, MYSQLI_ASSOC);

    } elseif ($result && $type == 'assoc') {
        $mysqli_result = mysqli_fetch_assoc($result);
    }

    return $mysqli_result;
}

function get_sorting_link_class(string $sort_type) : string {
    $result = '';

    if (isset($_GET['sort']) && $_GET['sort'] == $sort_type) {
        $result = ' sorting__link--active';

        if (isset($_GET['dir']) && $_GET['dir'] == 'asc') {
            $result .= ' sorting__link--reverse';
        }
    }

    return $result;
}

function get_sorting_link_url(string $sort_type, string $sort_dir) : string {
    if ($filter = filter_input(INPUT_GET, 'filter')) {
        $parameters['filter'] = $filter;
    }

    $parameters['sort'] = $sort_type;
    $parameters['dir'] = $sort_dir;

    $scriptname = 'index.php';
    $query = http_build_query($parameters);
    $url = '/' . $scriptname . '?' . $query;

    return $url;
}

function is_content_type_valid(mysqli $link, string $type) : bool {
    $sql = 'SELECT * FROM content_type';
    $content_types = get_mysqli_result($link, $sql);
    $class_names = array_column($content_types, 'class_name');

    if (in_array($type, $class_names)) {
        return true;
    }

    return false;
}

function is_post_valid(mysqli $link, int $post) : bool {
    if ($post > 0) {
        $sql = "SELECT COUNT(*) FROM post WHERE id = $post";
        $result = get_mysqli_result($link, $sql, 'assoc');

        return $result['COUNT(*)'] == 1;
    }

    return false;
}

function get_likes_count(mysqli $link, int $post) : int {

}

function get_comment_count(mysqli $link, int $post) : int {

}

function get_repost_count(mysqli $link, int $post) : int {

}

function get_subscriber_count(mysqli $link, int $user, $numeric_value = false) : string {
    $sql = "SELECT COUNT(*) FROM subscription WHERE user_id = $user";
    $result = get_mysqli_result($link, $sql, 'assoc');

    if ($numeric_value) {
        return $result['COUNT(*)'];
    }

    return get_noun_plural_form($result['COUNT(*)'], ' подписчик', ' подписчика', ' подписчиков');
}

function get_publication_count(mysqli $link, int $user, $numeric_value = false) : string {
    $sql = "SELECT COUNT(*) FROM post WHERE author_id = $user";
    $result = get_mysqli_result($link, $sql, 'assoc');

    if ($numeric_value) {
        return $result['COUNT(*)'];
    }

    return get_noun_plural_form($result['COUNT(*)'], ' публикация', ' публикации', ' публикаций');
}
