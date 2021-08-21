<?php

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function includeTemplate($name, array $data = [])
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     getNounPluralForm(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function getNounPluralForm(int $number, string $one, string $two, string $many): string
{
    $number = (int)$number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Извлекает из ссылки на youtube видео его уникальный ID
 * @param string $youtube_url Ссылка на youtube видео
 * @return array
 */
function extractYoutubeId($youtube_url)
{
    $id = false;

    $parts = parse_url($youtube_url);

    if ($parts) {
        if ($parts['path'] === '/watch') {
            parse_str($parts['query'], $vars);
            $id = $vars['v'] ?? null;
        } else {
            if ($parts['host'] === 'youtu.be') {
                $id = substr($parts['path'], 1);
            }
        }
    }

    return $id;
}

/**
 * Возвращает код iframe для вставки youtube видео на страницу
 * @param string $youtube_url Ссылка на youtube видео
 * @return string
 */
function embedYoutubeVideo($youtube_url)
{
    $res = "";
    $id = extractYoutubeId($youtube_url);

    if ($id) {
        $src = "https://www.youtube.com/embed/" . $id;
        $res = '<iframe src="' . $src . '" width="760" height="400" frameborder="0"></iframe>';
    }

    return $res;
}

/**
 * Возвращает img-тег с обложкой видео для вставки на страницу
 * @param string $youtube_url Ссылка на youtube видео
 * @return string
 */
function embedYoutubeCover($youtube_url, string $style = '')
{
    $res = "";
    $id = extractYoutubeId($youtube_url);

    if ($id) {
        $src = sprintf("https://img.youtube.com/vi/%s/mqdefault.jpg", $id);
        $res = '<img style="' . $style . '" src="' . $src . '" width="320" height="120" alt="youtube cover"/>';
    }

    return $res;
}
