<?php

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function crop_text_content(string $text, int $post_id, string $style = '', int $num_letters = 300) : string {
    $style = $style ? " style=\"{$style}\"" : '';
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

            $result_words_length += 1;
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

function get_post_input(string $form) : array {
    $input_names = [
        'adding-post' => [
            'heading',
            'image-url',
            'video-url',
            'post-text',
            'cite-text',
            'quote-author',
            'post-link',
            'tags',
            'file-photo',
            'content-type'
        ],
        'registration' => [
            'email',
            'login',
            'password',
            'password-repeat',
            'avatar'
        ],
        'login' => ['email', 'password'],
        'comments' => ['comment', 'post-id'],
        'messages' => ['message', 'contact-id']
    ];

    if (!isset($input_names[$form])) {
        http_response_code(500);
        exit;
    }

    foreach ($input_names[$form] as $name) {
        $input[$name] = filter_input(INPUT_POST, $name);
        $input[$name] = is_null($input[$name]) ? null : trim($input[$name]);
    }

    return $input;
}

function get_post_value(string $name) : string {
    $value = filter_input(INPUT_POST, $name) ?? '';

    return $value;
}

function esc(string $str) : string {
    $text = htmlspecialchars($str);

    return $text;
}

function add_prefix(&$item, $key, $prefix) {
    $item = $prefix . $item;
}

function get_post_fields(string $prefix, string $mode = 'select') : string {
    $post_fields = [
        'select' => [
            'id',
            'dt_add',
            'title',
            'text_content',
            'quote_author',
            'image_path',
            'video_path',
            'link',
            'show_count',
            'author_id',
            'is_repost',
            'origin_post_id',
            'content_type_id'
        ],
        'insert' => [
            'title',
            'text_content',
            'quote_author',
            'image_path',
            'video_path',
            'link',
            'author_id',
            'is_repost',
            'origin_post_id',
            'content_type_id'
        ]
    ];
    array_walk($post_fields[$mode], 'add_prefix', $prefix);

    return implode(', ', $post_fields[$mode]);
}

function get_stmt_data(array $input, string $form) : array {
    $input_keys = [
        'adding-post' => [
            'heading',
            'text-content',
            'quote-author',
            'image-path',
            'video-url',
            'post-link'
        ]
    ];

    if (!isset($input_keys[$form])) {
        http_response_code(500);
        exit;
    }

    foreach ($input_keys[$form] as $key) {
        $stmt_data[$key] = $input[$key];
    }

    return $stmt_data;
}

function get_relative_time(string $date) : string {

    if (!strtotime($date)) {
        return '';
    }

    $ts_diff = time() - strtotime($date);

    if ($ts_diff < 60) {
        $relative_time = "$ts_diff " . get_noun_plural_form($ts_diff, 'секунда', 'секунды', 'секунд');

    } elseif ($ts_diff < 3600) {
        $minutes = floor($ts_diff / 60);
        $relative_time = "$minutes " . get_noun_plural_form($minutes, 'минута', 'минуты', 'минут');

    } elseif ($ts_diff < 86400) {
        $hours = floor($ts_diff / 3600);
        $relative_time = "$hours " . get_noun_plural_form($hours, 'час', 'часа', 'часов');

    } elseif ($ts_diff < 604800) {
        $days = floor($ts_diff / 86400);
        $relative_time = "$days " . get_noun_plural_form($days, 'день', 'дня', 'дней');

    } elseif ($ts_diff < 3024000) {
        $weeks = floor($ts_diff / 604800);
        $relative_time = "$weeks " . get_noun_plural_form($weeks, 'неделя', 'недели', 'недель');

    } elseif ($ts_diff >= 3024000) {
        $dt_diff = date_diff(date_create($date), date_create('now'));
        $months = date_interval_format($dt_diff, '%m');
        $relative_time = "$months " . get_noun_plural_form($months, 'месяц', 'месяца', 'месяцев');
    }

    return $relative_time;
}

function get_datetime_value(string $date) : string {
    if ($ts = strtotime($date)) {
        $datetime = date('Y-m-d H:i', $ts);
        $datetime = str_replace(' ', 'T', $datetime);
    }

    return $datetime ?? '';
}

function get_time_title(string $date) : string {
    if ($ts = strtotime($date)) {
        $title = date('d.m.Y H:i', $ts);
    }

    return $title ?? '';
}

function get_sorting_link_class(string $field) : string {
    if (isset($_GET['sort']) && $_GET['sort'] === $field) {
        $classname = ' sorting__link--active';
        if (isset($_GET['dir']) && $_GET['dir'] === 'asc') {
            $classname .= ' sorting__link--reverse';
        }
    }

    return $classname ?? '';
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

function get_page_link_url(int $current_page, bool $next) : string {
    $parameters['filter'] = filter_input(INPUT_GET, 'filter');
    $parameters['sort'] = filter_input(INPUT_GET, 'sort');
    $parameters['dir'] = filter_input(INPUT_GET, 'dir');

    $parameters = array_filter($parameters);

    $parameters['page'] = $current_page;
    $next ? $parameters['page']++ : $parameters['page']--;

    $scriptname = 'popular.php';
    $query = http_build_query($parameters);
    $url = '/' . $scriptname . '?' . $query;

    return $url;
}

function get_adding_post_close_url() : string {
    $url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
    if (parse_url($url, PHP_URL_PATH) === '/add.php') {
        $url = $_COOKIE['add_ref'] ?? $url;
    }

    return $url;
}

function validate_input_file_photo(array &$errors, array &$input) : void {
    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_path = $_FILES['file-photo']['tmp_name'];
    $file_size = $_FILES['file-photo']['size'];
    $file_type = finfo_file($finfo, $file_path);

    if (!in_array($file_type, $mime_types)) {
        $errors['file-photo'][0] = 'Неверный MIME-тип файла';
        $errors['file-photo'][1] = 'Изображение';
    } elseif ($file_size > 1000000) {
        $errors['file-photo'][0] = 'Максимальный размер файла: 1Мб';
        $errors['file-photo'][1] = 'Изображение';
    } else {
        $file_name = uniqid();
        $file_extension = explode('/', $file_type);
        $file_name .= ".{$file_extension[1]}";
        move_uploaded_file($file_path, 'uploads/' . $file_name);
        $input['image-path'] = $file_name;
    }
}

function validate_input_image_url(array &$errors, array &$input) : void {
    $mime_types = ['image/jpeg', 'image/png', 'image/gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = uniqid();
    $file_path = "uploads/{$file_name}.jpeg";

    set_error_handler('exceptions_error_handler');
    try {
        $content = file_get_contents($input['image-url']);
        file_put_contents($file_path, $content);
        $file_type = finfo_file($finfo, $file_path);

        if (!in_array($file_type, $mime_types)) {
            unlink($file_path);
            $errors['file-photo'][0] = 'Неверный MIME-тип файла';
            $errors['file-photo'][1] = 'Изображение';
        } elseif (filesize($file_path) > 1000000) {
            unlink($file_path);
            $errors['file-photo'][0] = 'Максимальный размер файла: 1Мб';
            $errors['file-photo'][1] = 'Изображение';
        } else {
            $file_extension = explode('/', $file_type);
            $file_name .= ".{$file_extension[1]}";
            rename($file_path, 'uploads/' . $file_name);
            $input['image-path'] = $file_name;
        }

    } catch (ErrorException $ex) {
        $errors['file-photo'][0] = 'Вы не загрузили файл';
        $errors['file-photo'][1] = 'Изображение';
    }
    restore_error_handler();
}

function validate_input_video_url(array &$errors, array &$input) : void {
    if (strpos($input['video-url'], 'youtube.com/watch?v=') === false) {
        $errors['video-url'][0] = 'Некорректный url-адрес';
        $errors['video-url'][1] = 'Ссылка youtube';
    }
}

function validate_link_info(array &$input) : void {
    set_error_handler('exceptions_error_handler');

    try {
        $html = file_get_contents($input['post-link']);
    } catch (ErrorException $ex) {
        return;
    }

    restore_error_handler();
    $doc = new DOMDocument();

    libxml_use_internal_errors(true);
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8');
    $doc->loadHTML($html);
    libxml_clear_errors();

    $h1_elements = $doc->getElementsByTagName('h1');

    if ($h1_elements->count() === 1) {
        $h1_childs = $h1_elements->item(0)->childNodes;

        if ($h1_childs->count() === 1
            && $h1_childs->item(0)->nodeName === '#text') {
            $h1 = trim($h1_elements->item(0)->nodeValue);
        }
    }

    $title = $doc->getElementsByTagName('title')->item(0);
    $title = !is_null($title) ? trim($title->nodeValue) : '';

    $input['text-content'] = $h1 ?? $title;
}

function cmp($a, $b) {
    $a = isset($a['sizes']) ? explode('x', $a['sizes'])[0] : '0';
    $b = isset($b['sizes']) ? explode('x', $b['sizes'])[0] : '0';

    if ($a == $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

function get_icon_url(string $url) : string {
    $url = parse_url($url, PHP_URL_HOST);
    $url = "https://favicongrabber.com/api/grab/{$url}?pretty=true";

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');

    do {
        $response = curl_exec($curl_handle);
        $http_code = curl_getinfo($curl_handle)['http_code'];
        $http_error = $http_code >= 400 && $http_code !== 502;

        if (!$response || $http_error) {
            return '';
        }

    } while ($http_code === 502);

    curl_close($curl_handle);
    $response = json_decode($response, true)['icons'];
    usort($response, 'cmp');

    return $response[0]['src'] ?? '';
}

function validate_icon_file(array &$input) : void {
    $icon_url = get_icon_url($input['post-link']);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_name = uniqid();
    $file_path = "uploads/{$file_name}.jpeg";

    set_error_handler('exceptions_error_handler');
    try {
        $content = file_get_contents($icon_url);
        file_put_contents($file_path, $content);
        $file_type = finfo_file($finfo, $file_path);

        $file_extension = explode('/', $file_type);
        $file_name .= ".{$file_extension[1]}";
        rename($file_path, 'uploads/' . $file_name);
        $input['image-path'] = $file_name;

    } catch (ErrorException $ex) {}
    restore_error_handler();
}

function validate_input_post_link(array &$input) : void {
    validate_link_info($input);
    validate_icon_file($input);
}

function delete_file(string $filename) : void {
    if (file_exists($filename)) {
        unlink($filename);
    }
}
