<?php

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function get_mysqli_result(mysqli $link, string $sql, string $result_type = 'all') {

    if (!$result = mysqli_query($link, $sql)) {
        update_errors_log($link, $sql, 'mysql_errors.txt');

        http_response_code(500);
        exit;

    } elseif ($result_type === 'all') {
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

    } elseif ($result_type === 'assoc') {
        $result = mysqli_fetch_assoc($result);
    }

    return $result;
}

function update_errors_log(mysqli $link, string $sql, string $filename) : void {
    $date = date('d-m-Y H:i:s');
    $error = mysqli_error($link);
    $data = "$date - $error\n$sql\n\n";

    file_put_contents($filename, $data, FILE_APPEND | LOCK_EX);
}

function get_text_content(string $text, int $post_id, bool $margin = false, int $num_letters = 300) : string {
    $style = $margin ? ' style="margin-top: 0;"' : '';
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
        $result = "<p{$style}>" . $result . '</p>';
        $result .= '<a class="post-text__more-link" href="post.php?id=' . $post_id . '">Читать далее</a>';
    } else {
        $result = "<p{$style}>" . $text . '</p>';
    }

    return $result;
}

function esc(string $str) : string {
    $text = htmlspecialchars($str);

    return $text;
}

function get_post_time(string $date) : string {

    if (!strtotime($date)) {
        return '';
    }

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

    if (!$ts = strtotime($date)) {
        $title = '';
    } else {
        $title = date('d.m.Y H:i', $ts);
    }

    return $title;
}

function get_sorting_link_class(string $field) : string {
    $classname = '';

    if (isset($_GET['sort']) && $_GET['sort'] === $field) {
        $classname = ' sorting__link--active';

        if (isset($_GET['dir']) && $_GET['dir'] === 'asc') {
            $classname .= ' sorting__link--reverse';
        }
    }

    return $classname;
}

function get_sorting_link_url(string $field, array $types) : string {
    if ($filter = filter_input(INPUT_GET, 'filter')) {
        $parameters['filter'] = $filter;
    }

    $parameters['sort'] = $field;
    $parameters['dir'] = $types[$field];

    $scriptname = 'popular.php';
    $query = http_build_query($parameters);
    $url = '/' . $scriptname . '?' . $query;

    return $url;
}

function is_content_type_valid(mysqli $link, string $type) : bool {
    $sql = 'SELECT * FROM content_type';
    $content_types = get_mysqli_result($link, $sql);
    $class_names = array_column($content_types, 'class_name');

    return in_array($type, $class_names) ? true : false;
}

function get_post_input(mysqli $link, string $form) : array {
    $sql = 'SELECT i.* FROM input i '
         . 'INNER JOIN form_input fi ON fi.input_id = i.id '
         . 'INNER JOIN form f ON f.id = fi.form_id '
         . "WHERE f.name = '$form'";
    $form_inputs = get_mysqli_result($link, $sql);
    $input_names = array_column($form_inputs, 'name');

    foreach ($input_names as $name) {
        $input[$name] = filter_input(INPUT_POST, $name);
        $input[$name] = is_null($input[$name]) ? null : trim($input[$name]);
    }

    if ($form === 'adding-post') {
        list($input['text-content'], $input['image-path']) = [null, null];

        if (!is_content_type_valid($link, $input['content-type'])) {
            return false;
        }
    }

    return $input;
}

function get_content_type_id(mysqli $link, string $content_type) : string {

    if (!is_content_type_valid($link, $content_type)) {
        return false;
    }

    $sql = "SELECT * FROM content_type WHERE class_name = '$content_type'";
    $result = get_mysqli_result($link, $sql, 'assoc');

    return $result['id'];
}

function get_required_fields(mysqli $link, string $form, string $tab = '') : array {
    $sql = 'SELECT i.* FROM input i '
         . 'INNER JOIN form_input fi ON fi.input_id = i.id '
         . 'INNER JOIN form f ON f.id = fi.form_id '
         . "WHERE f.name = '$form' AND i.required = 1";
    $sql .= $form == 'adding-post' ? " AND f.modifier = '$tab'" : '';
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

    $stmt_data[] = $_SESSION['user']['id'];
    $stmt_data[] = $content_type_id;

    return $stmt_data;
}

function get_post_value(string $name) : string {
    $value = $_POST[$name] ?? '';

    return $value;
}

function validate_post(mysqli $link, int $post_id) : int {
    $sql = "SELECT id FROM post WHERE id = $post_id";
    $result = get_mysqli_result($link, $sql, false);

    if (!mysqli_num_rows($result)) {
        http_response_code(404);
        exit;
    }

    return $post_id;
}

function validate_profile(mysqli $link, int $profile_id) : int {
    $sql = "SELECT id FROM user WHERE id = $profile_id";
    $result = get_mysqli_result($link, $sql, false);

    if (!mysqli_num_rows($result)) {
        http_response_code(404);
        exit;
    }

    return $profile_id;
}

function get_likes_indicator_class(mysqli $link, int $post_id) : string {
    $user_id = $_SESSION['user']['id'];
    $sql = "SELECT id FROM post_like WHERE post_id = $post_id AND author_id = $user_id";
    $result = get_mysqli_result($link, $sql, false);

    return mysqli_num_rows($result) ? ' post__indicator--likes-active' : '';
}

function get_subscription_status(mysqli $link, int $profile_id) : bool {
    $user_id = $_SESSION['user']['id'];
    $sql = "SELECT id FROM subscription WHERE author_id = $user_id AND user_id = $profile_id";
    $result = get_mysqli_result($link, $sql, false);

    return boolval(mysqli_num_rows($result));
}

function get_likes_count(mysqli $link, int $post_id) : int {
    $sql = "SELECT COUNT(*) FROM post_like WHERE post_id = $post_id";
    $result = get_mysqli_result($link, $sql, 'assoc');

    return $result['COUNT(*)'];
}

function get_comment_count(mysqli $link, int $post_id) : int {
    $sql = "SELECT COUNT(*) FROM comment WHERE post_id = $post_id";
    $result = get_mysqli_result($link, $sql, 'assoc');

    return $result['COUNT(*)'];

}

function get_repost_count(mysqli $link, int $post_id) : int {
    $sql = "SELECT COUNT(*) FROM post WHERE origin_post_id = $post_id";
    $result = get_mysqli_result($link, $sql, 'assoc');

    return $result['COUNT(*)'];
}

function get_show_count(int $show_count) : string {
    $result = "$show_count " . get_noun_plural_form($show_count, 'просмотр', 'просмотра', 'просмотров');

    return $result;
}

function get_subscriber_count(mysqli $link, int $user_id, $numeric_value = false) : string {
    $sql = "SELECT COUNT(*) FROM subscription WHERE user_id = $user_id";
    $result = get_mysqli_result($link, $sql, 'assoc');

    if ($numeric_value) {
        return $result['COUNT(*)'];
    }

    return get_noun_plural_form($result['COUNT(*)'], ' подписчик', ' подписчика', ' подписчиков');
}

function get_publication_count(mysqli $link, int $user_id, $numeric_value = false) : string {
    $sql = "SELECT COUNT(*) FROM post WHERE author_id = $user_id";
    $result = get_mysqli_result($link, $sql, 'assoc');

    if ($numeric_value) {
        return $result['COUNT(*)'];
    }

    return get_noun_plural_form($result['COUNT(*)'], ' публикация', ' публикации', ' публикаций');
}

function get_search_sql(string $value, bool $hashtag_mode = false) : string {

    if ($hashtag_mode === true) {
        $sql = 'SELECT p.*, u.login AS author, u.avatar_path, ct.class_name FROM post p '
             . 'INNER JOIN user u ON u.id = p.author_id '
             . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
             . 'INNER JOIN post_hashtag ph ON ph.post_id = p.id '
             . 'INNER JOIN hashtag h ON h.id = ph.hashtag_id '
             . "WHERE h.name = '$value' "
             . 'ORDER BY p.dt_add DESC';
    } else {
        $sql = 'SELECT p.*, u.login AS author, u.avatar_path, ct.class_name, '
             . "MATCH (p.title, p.text_content) AGAINST ('$value') AS score FROM post p "
             . 'INNER JOIN user u ON u.id = p.author_id '
             . 'INNER JOIN content_type ct ON ct.id = p.content_type_id '
             . "WHERE MATCH (p.title, p.text_content) AGAINST ('$value' IN BOOLEAN MODE) "
             . 'ORDER BY score DESC';
    }

    return $sql;
}

function get_post_hashtags(mysqli $link, int $post_id) : array {
    $sql = 'SELECT * FROM hashtag h '
         . 'INNER JOIN post_hashtag ph ON ph.hashtag_id = h.id '
         . 'INNER JOIN post p ON p.id = ph.post_id '
         . "WHERE p.id = $post_id";
    $hashtags = get_mysqli_result($link, $sql);

    return $hashtags;
}
