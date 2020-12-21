<?php

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function get_mysqli_result(mysqli $link, string $sql, int $type = FETCH_ALL) : array {
    $result = mysqli_query($link, $sql);
    $mysqli_result = [];

    if (!$result && ini_get('display_errors')) {
        $error = mysqli_error($link);
        print("Ошибка MySQL: $error");

    } elseif (!$result && $type === 0) {
        http_response_code(500);
        exit;

    } elseif ($result && $type === 1) {
        $mysqli_result = mysqli_fetch_all($result, MYSQLI_ASSOC);

    } elseif ($result && $type === 2) {
        $mysqli_result = mysqli_fetch_assoc($result);
    }

    return $mysqli_result;
}

function modification_query(mysqli $link, string $sql) : bool {

    if (!mysqli_query($link, $sql)) {
        http_response_code(500);
        exit;
    }

    return true;
}

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
    $time_title = date('d.m.Y H:i', $ts);

    return $time_title;
}

function get_sorting_link_class(string $field) : string {
    $result = '';

    if (isset($_GET['sort']) && $_GET['sort'] == $field) {
        $result = ' sorting__link--active';

        if (isset($_GET['dir']) && $_GET['dir'] == 'asc') {
            $result .= ' sorting__link--reverse';
        }
    }

    return $result;
}

function get_sorting_link_url(string $field, array $types) : string {
    if ($filter = filter_input(INPUT_GET, 'filter')) {
        $parameters['filter'] = $filter;
    }

    $parameters['sort'] = $field;
    $parameters['dir'] = $types[$field];

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

function get_post_input(mysqli $link, string $form) : array {
    $sql = 'SELECT i.* FROM input i '
         . 'INNER JOIN form_input fi ON fi.input_id = i.id '
         . 'INNER JOIN form f ON f.id = fi.form_id '
         . "WHERE f.name LIKE '%{$form}%'";
    $form_inputs = get_mysqli_result($link, $sql);
    $input_names = array_column($form_inputs, 'name');

    if ($form == 'adding-post') {
        list($input['text-content'], $input['image-path']) = null;
    }

    foreach ($input_names as $name) {
        $input[$name] = filter_input(INPUT_POST, $name);
        $input[$name] = is_null($input[$name]) ? null : trim($input[$name]);
    }

    return $input;
}

function get_content_type(mysqli $link) : string {
    $content_type = filter_input(INPUT_POST, 'content-type') ?? 'photo';

    if (is_content_type_valid($link, $content_type)) {
        return $content_type;
    }

    return false;
}

function get_content_type_id(mysqli $link, string $type) : int {

    if (is_content_type_valid($link, $type)) {
        $sql = "SELECT * FROM content_type WHERE class_name = '$type'";
        $result = get_mysqli_result($link, $sql, FETCH_ASSOC);

        return $result['id'];
    }

    return false;
}

function get_required_fields(mysqli $link, string $tab) : array {
    $sql = 'SELECT i.* FROM input i '
         . 'INNER JOIN form_input fi ON fi.input_id = i.id '
         . 'INNER JOIN form f ON f.id = fi.form_id '
         . "WHERE f.name = 'adding-post__{$tab}' AND i.required = 1";
    $required_fields = get_mysqli_result($link, $sql);

    return array_column($required_fields, 'name');
}

function get_stmt_data(array $input, int $content_type_id) : array {
    $values = ['heading', 'text-content', 'quote-author', 'image-path', 'video-url', 'post-link'];
    foreach ($values as $value) {
        if (array_key_exists($value, $input)) {
            $stmt_data[] = $input[$value];
        } else {
            return false;
        }
    }

    $stmt_data[] = $content_type_id;

    return $stmt_data;
}

function get_post_value(string $name) : string {
    $value = $_POST[$name] ?? '';

    return $value;
}

function post_validate(mysqli $link, int $post) : void {
    $sql = "SELECT COUNT(*) FROM post WHERE id = $post";
    $result = get_mysqli_result($link, $sql, FETCH_ASSOC);

    if ($result['COUNT(*)'] == 0) {
        http_response_code(404);
        exit;
    }
}

function get_likes_count(mysqli $link, int $post) : int {

}

function get_comment_count(mysqli $link, int $post) : int {

}

function get_repost_count(mysqli $link, int $post) : int {

}

function get_subscriber_count(mysqli $link, int $user, $numeric_value = false) : string {
    $sql = "SELECT COUNT(*) FROM subscription WHERE user_id = $user";
    $result = get_mysqli_result($link, $sql, FETCH_ASSOC);

    if ($numeric_value) {
        return $result['COUNT(*)'];
    }

    return get_noun_plural_form($result['COUNT(*)'], ' подписчик', ' подписчика', ' подписчиков');
}

function get_publication_count(mysqli $link, int $user, $numeric_value = false) : string {
    $sql = "SELECT COUNT(*) FROM post WHERE author_id = $user";
    $result = get_mysqli_result($link, $sql, FETCH_ASSOC);

    if ($numeric_value) {
        return $result['COUNT(*)'];
    }

    return get_noun_plural_form($result['COUNT(*)'], ' публикация', ' публикации', ' публикаций');
}
