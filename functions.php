<?php

/**
 * Callback для встроенной функции set_error_handler
 *
 * @param int $severity Уровень ошибки
 * @param string $message Сообщение об ошибке
 * @param string $filename Имя файла, в котором произошла ошибка
 * @param int $lineno Номер строки, на которой произошла ошибка
 */
function exceptionsErrorHandler($severity, $message, $filename, $lineno)
{
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

/**
 * Обрезает текст до указанной длины.
 * Добавляет в конце знак троеточия и ссылку на публикацию
 * (если длина текста > указанной длины).
 * Возвращает итоговый HTML
 * <p style="...">текст</p> (<a href="...">Читать далее</a>)
 *
 * @param string $text Строка для обрезания
 * @param int $post_id Идентификатор публикации
 * @param string $style Стили для элемента p
 * @param int $length Длина строки
 *
 * @return string
 */
function cropTextContent(string $text, int $post_id, string $style = '', int $length = 300): string
{
    $text_length = mb_strlen($text);

    if ($text_length > $length) {
        $words = explode(' ', $text);
        $result_words_length = 0;
        $result_words = [];

        foreach ($words as $word) {
            $result_words_length += mb_strlen($word);

            if ($result_words_length > $length) {
                break;
            }

            $result_words_length += 1;
            $result_words[] = $word;
        }

        $result = implode(' ', $result_words);

        $result .= '...';
        $result = '<p style"' . $style . '">' . $result . '</p>';
        $result .= '<a class="post-text__more-link" href="post.php?id=' . $post_id . '">Читать далее</a>';
    } else {
        $result = '<p style"' . $style . '">' . $text . '</p>';
    }

    return $result;
}

/**
 * Если указанная форма существует - формирует массив из данных,
 * которые были отправлены методом POST для выбранной html-формы
 *
 * ['input_name' => trim('input_value') | null]
 *
 * Возвращает данные или пустой массив
 * (если указанная форма не существует).
 *
 * @param string $form
 * @return array
 */
function getPostInput(string $form): array
{
    $input_names = [
        'adding-post' => [
            'heading',
            'image-url',
            'video-url',
            'post-text',
            'cite-text',
            'text-content',
            'quote-author',
            'post-link',
            'tags',
            'file-photo',
            'image-path',
            'content-type'
        ],
        'registration' => [
            'email',
            'login',
            'passwd',
            'passwd-repeat',
            'avatar-path'
        ],
        'login' => ['email', 'passwd'],
        'comments' => ['comment', 'post-id'],
        'messages' => ['message', 'contact-id']
    ];

    if (!isset($input_names[$form])) {
        return [];
    }

    foreach ($input_names[$form] as $name) {
        $input[$name] = filter_input(INPUT_POST, $name);
        $input[$name] = is_null($input[$name]) ? null : trim($input[$name]);
    }

    return $input;
}

/**
 * Возвращает поля таблицы post в двух вариантах:
 *
 * 1. Для выборки (select)
 * 2. Для вставки (insert)
 *
 * @param string $mode
 * @return array
 */
function getPostFields(string $mode = 'select'): array
{
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

    return $post_fields[$mode] ?? [];
}

/**
 * Если указанная форма существует - формирует массив из данных,
 * которые были переданы через аргумент $input
 *
 * ['input_key' => $input['input_key']]
 *
 * Возвращает данные или пустой массив
 * (если указанная форма / инпут не существует).
 *
 * @param array $input
 * @param string $form
 *
 * @return array
 */
function getStmtData(array $input, string $form): array
{
    $input_keys = [
        'adding-post' => [
            'heading',
            'text-content',
            'quote-author',
            'image-path',
            'video-url',
            'post-link'
        ],
        'registration' => [
            'email',
            'login',
            'passwd',
            'avatar-path'
        ]
    ];

    if (!isset($input_keys[$form])) {
        return [];
    }

    foreach ($input_keys[$form] as $key) {
        if (!array_key_exists($key, $input)) {
            return [];
        }
        $stmt_data[$key] = $input[$key];
    }

    return $stmt_data;
}

/**
 * Возвращает дату в относительном формате
 *
 * @param string $date Строка даты / времени
 * @return string
 */
function getRelativeTime(string $date): string
{

    if (!strtotime($date)) {
        return '';
    }

    $array = [
        [SECONDS_PER_MINUTE, 1, 'секунда', 'секунды', 'секунд'],
        [SECONDS_PER_HOUR, SECONDS_PER_MINUTE, 'минута', 'минуты', 'минут'],
        [SECONDS_PER_DAY, SECONDS_PER_HOUR, 'час', 'часа', 'часов'],
        [SECONDS_PER_WEEK, SECONDS_PER_DAY, 'день', 'дня', 'дней'],
        [SECONDS_PER_MONTH, SECONDS_PER_WEEK, 'неделя', 'недели', 'недель'],
        [SECONDS_PER_YEAR, SECONDS_PER_DAY * 30, 'месяц', 'месяца', 'месяцев'],
        [PHP_INT_MAX, SECONDS_PER_YEAR, 'год', 'года', 'лет']
    ];

    $ts_diff = time() - strtotime($date);

    $i = 0;
    do {
        $time = floor($ts_diff / $array[$i][1]);
        $relative_time = "$time " . getNounPluralForm($time, $array[$i][2], $array[$i][3], $array[$i][4]);
        $i++;

        if ($ts_diff < $array[$i - 1][0]) {
            break;
        }

    } while ($i < count($array));

    return $relative_time;
}

/**
 * Возвращает значение для атрибута datetime
 *
 * @param string $date Строка даты / времени
 * @return string
 */
function getDatetimeValue(string $date): string
{
    if ($ts = strtotime($date)) {
        $datetime = date('Y-m-d H:i', $ts);
        $datetime = str_replace(' ', 'T', $datetime);
    }

    return $datetime ?? '';
}

/**
 * Возвращает значение для атрибута title
 *
 * @param string $date Строка даты / времени
 * @return string
 */
function getTimeTitle(string $date): string
{
    if ($ts = strtotime($date)) {
        $title = date('d.m.Y H:i', $ts);
    }

    return $title ?? '';
}

/**
 * Возвращает модификатор для текстового содержимого
 * (.adding-post__tab-content h2)
 *
 * @param string $ctype
 * @return string
 */
function getTabContentModifier(string $ctype): string
{
    $content_types = [
        'photo' => 'фото',
        'video' => 'видео',
        'text' => 'текста',
        'quote' => 'цитаты',
        'link' => 'ссылки'
    ];

    return $content_types[$ctype] ?? '';
}

/**
 * Возвращает значение для атрибута class
 * (a.sorting__link)
 *
 * @param string $field Поле для сортировки
 * @return string
 */
function getSortingLinkClass(string $field): string
{
    if (isset($_GET['sort']) && $_GET['sort'] === $field) {
        $classname = ' sorting__link--active';
        if (isset($_GET['dir']) && $_GET['dir'] === 'asc') {
            $classname .= ' sorting__link--reverse';
        }
    }

    return $classname ?? '';
}

/**
 * Возвращает значение для атрибута href
 * (a.sorting__link)
 *
 * @param string $field Поле для сортировки
 * @param array $types Направления сортировки
 *
 * @return string
 */
function getSortingLinkUrl(string $field, array $types): string
{
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

/**
 * Возвращает значение для атрибута href
 * (a.popular__page-link)
 *
 * @param int $current_page Текущая страница
 * @param bool $next (href для следующей страницы?)
 *
 * @return string
 */
function getPageLinkUrl(int $current_page, bool $next): string
{
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

/**
 * Возвращает значение для атрибута href
 * (a.adding-post__close)
 *
 * @return string
 */
function getAddingPostCloseUrl(): string
{
    $url = $_SERVER['HTTP_REFERER'] ?? '/feed.php';
    if (parse_url($url, PHP_URL_PATH) === '/add.php') {
        $url = $_COOKIE['add_ref'] ?? $url;
    }

    return $url;
}

/**
 * Обрабатывает веб-страницу на предмет наличия h1 или title
 * Возвращает текстовое содержимое элемента h1 или title
 *
 * @param string $url URL веб-страницы
 * @return string
 */
function getLinkInfo(string $url)
{
    set_error_handler('exceptionsErrorHandler');

    try {
        $html = file_get_contents($url);
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

    return $h1 ?? $title;
}

/**
 * Callback для встроенной функции usort
 * Сортирует элементы многомерного массива по ключу 'sizes'
 *
 * @param array $a
 * @param array $b
 *
 * @return int
 */

function cmp(array $a, array $b): int
{
    $a = isset($a['sizes']) ? explode('x', $a['sizes'])[0] : '0';
    $b = isset($b['sizes']) ? explode('x', $b['sizes'])[0] : '0';

    if ($a === $b) {
        return 0;
    }
    return ($a > $b) ? -1 : 1;
}

/**
 * Возвращает URL фавиконки максимального качества
 *
 * @param string $url URL веб-страницы
 * @return string
 */
function getFaviconUrl(string $url): string
{
    $url = parse_url($url, PHP_URL_HOST);
    $url = "https://favicongrabber.com/api/grab/{$url}?pretty=true";

    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Readme');

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

/**
 * Загружает фавиконку и возвращает её адрес
 *
 * @param string $url URL веб-страницы
 * @return string
 */
function uploadFavicon(string $url): string
{
    $icon_url = getFaviconUrl($url);

    $file_name = uniqid();
    $file_path = "uploads/{$file_name}.jpeg";

    set_error_handler('exceptionsErrorHandler');
    try {
        $content = file_get_contents($icon_url);
        file_put_contents($file_path, $content);
        $file_type = mime_content_type($file_path);

        $file_extension = explode('/', $file_type);
        $file_name .= ".{$file_extension[1]}";
        rename($file_path, 'uploads/' . $file_name);

    } catch (ErrorException $ex) {}
    restore_error_handler();

    return $file_name ?? '';
}

/**
 * Обрабатывает публикацию типа "Ссылка"
 *
 * @param array &$input Данные из массива $_POST (по ссылке)
 */
function processInputPostLink(array &$input)
{
    $input['text-content'] = getLinkInfo($input['post-link']);
    $input['image-path'] = uploadFavicon($input['post-link']);
}

/**
 * Загружает локальный файл и возвращает его адрес
 *
 * @param string $file_key Ключ файла ($_FILES)
 * @return string|null
 */
function uploadLocalFile(string $file_key)
{
    if (!empty($_FILES[$file_key]['tmp_name'])) {
        $file_path = $_FILES[$file_key]['tmp_name'];
        $file_type = mime_content_type($file_path);
        $file_extension = explode('/', $file_type);
        $file_name = uniqid() . ".{$file_extension[1]}";
        move_uploaded_file($file_path, "uploads/$file_name");
    }

    return $file_name ?? null;
}

/**
 * Загружает удалённый файл и возвращает его адрес
 *
 * @param string $file_url URL удалённого файла
 * @param array &$errors Ошибки валидации (по ссылке)
 *
 * @return string|null
 */
function uploadRemoteFile(string $file_url, array &$errors)
{
    if (filter_var($file_url, FILTER_VALIDATE_URL)) {
        set_error_handler('exceptionsErrorHandler');
        try {
            $temp_file = tmpfile();
            $content = file_get_contents($file_url);
            fwrite($temp_file, $content);
            $file_path = stream_get_meta_data($temp_file)['uri'];
            $file_type = mime_content_type($file_path);
            fclose($temp_file);

            $file_extension = explode('/', $file_type);
            $file_name = uniqid() . ".{$file_extension[1]}";
            file_put_contents('uploads/' . $file_name, $content);

        } catch (ErrorException $ex) {
            $errors['file-photo'][0] = 'Вы не загрузили файл';
            $errors['file-photo'][1] = 'Изображение';
        }
        restore_error_handler();
    }

    return $file_name ?? null;
}

/**
 * Загружает файл для публикации типа "Картинка"
 *
 * @param array $input Данные из массива POST
 * @param array &$errors Ошибки валидации (по ссылке)
 *
 * @return string|null
 */
function uploadImageFile(array $input, array &$errors)
{
    if (!empty($_FILES['file-photo']['name'])) {
        $file_name = uploadLocalFile('file-photo');

    } elseif ($url = $input['image-url'] ?? '') {
        $file_name = uploadRemoteFile($url, $errors);
    }

    return $file_name ?? null;
}

/**
 * Обрабатывает хэштеги следующим образом:
 *
 * 1. Добавляет хэштеги, которые отсутствуют в БД (readme.hashtag)
 * 2. Добавляет связи с указанной публикацией в БД (readme.post_hashtag)
 *
 * @param array $hashtags Хэштеги
 * @param int $post_id Идентификатор публикации
 */
function processPostHashtags(array $hashtags, int $post_id)
{
    array_walk($hashtags, function (&$val, $key) {
        $val = ltrim($val, '#');
    });

    if ($hashtags = array_filter($hashtags)) {
        $exist_hashtags = Database::getInstance()->getExistHashtags($hashtags);
        $exist_hashtag_ids = array_column($exist_hashtags, 'id');
        $exist_hashtag_names = array_column($exist_hashtags, 'name');
        $not_exist_hashtags = array_diff($hashtags, $exist_hashtag_names);

        foreach ($not_exist_hashtags as $hashtag) {
            $hashtag_ids[] = Database::getInstance()->insertHashtag($hashtag);
        }

        $hashtag_ids = array_merge($exist_hashtag_ids, $hashtag_ids ?? []);

        foreach ($hashtag_ids as $hashtag_id) {
            Database::getInstance()->insertPostHashtag([$hashtag_id, $post_id]);
        }
    }
}

/**
 * Отправляет email-уведомления о новой публикации
 * подписчикам аутентифицированного пользователя
 *
 * @param array $recipients Подписчики
 * @param string $post_title Заголовок публикации
 */
function sendPostNotifications(array $recipients, string $post_title)
{
    try {
        $smtp_config = require('config/smtp.php');
        $transport = new Swift_SmtpTransport($smtp_config['host'], $smtp_config['port']);
        $transport->setUsername($smtp_config['username']);
        $transport->setPassword($smtp_config['password']);

        $message = new Swift_Message();
        $message->setSubject("Новая публикация от пользователя {$_SESSION['user']['login']}");

        $mailer = new Swift_Mailer($transport);

        foreach ($recipients as $recipient) {
            $message->setTo([$recipient['email'] => $recipient['login']]);
            $body = includeTemplate('notifications/post.php', [
                'recipient' => $recipient,
                'post_title' => $post_title
            ]);
            $message->setBody($body);
            $message->setFrom('keks@phpdemo.ru', 'Readme');

            $mailer->send($message);
        }

    } catch (Swift_TransportException $ex) {}
}

/**
 * Аутентифицирует пользователя
 * Записывает в сессию ассоциативный массив с данными пользователя
 * (если передано корректное сочетание email / пароль)
 *
 * $_SESSION['user'] = ['key' => 'value']
 *
 * После успешного входа переадресует пользователя на feed.php
 * или возвращает массив с ошибками валидации формы
 *
 * @return array
 */
function authenticate(): array
{
    $input = getPostInput('login');
    $errors = validateForm('login', $input);

    if (!is_null($errors) && empty($errors)) {
        $db = Database::getInstance();
        $_SESSION['user'] = $db->getUserByEmail($input['email']);
        $url = $_COOKIE['login_ref'] ?? '/feed.php';
        setcookie('login_ref', '', time() - 3600);

        header("Location: $url");
        exit;
    }

    return $errors;
}

/**
 * Возвращает значение из массива POST
 * по указанному ключу или пустую строку
 *
 * @param string $name Ключ инпута ($_POST)
 * @return string
 */
function getPostValue(string $name): string
{
    return filter_input(INPUT_POST, $name) ?? '';
}

/**
 * Преобразует специальные символы в HTML-сущности
 * https://php.net/manual/ru/function.htmlspecialchars.php
 *
 * @param string $str Конвертируемая строка
 * @return string Преобразованная строка
 */
function esc(string $str): string
{
    return htmlspecialchars($str);
}

/**
 * Callback для встроенной функции array_walk
 * Добавляет префикс для каждого элемента массива
 *
 * @param &$item Значение элемента массива (по ссылке)
 * @param $key Ключ элемента массива
 * @param string $prefix Префикс
 */
function addPrefix(&$item, $key, string $prefix)
{
    $item = $prefix . $item;
}

/**
 * Вырезает множественные пробелы и переносы строк:
 *
 * 1. 2 и более пробела (превращает в 1)
 * 2. 3 и более переноса строки (превращает в 2)
 *
 * @param string $text Конвертируемая строка
 * @return string Преобразованная строка
 */
function cutOutExtraSpaces(string $text): string
{
    $text = preg_replace('/(\r\n){3,}|(\n){3,}/', "\n\n", $text);
    return preg_replace('/\040\040+/', ' ', $text);
}

/**
 * Создаёт хеш пароля
 * https://php.net/manual/ru/function.password-hash
 *
 * @param string Пользовательский пароль
 * @return string Хешированный пароль
 */
function getPasswordHash(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}
