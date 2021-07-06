<?php

const FORM_TYPES = [
    'adding-post__photo' => [
        [
            'heading',
            'Заголовок',
            ['validate_filled']
        ],
        [
            'file-photo',
            'Изображение',
            ['validate_image_file']
        ]
    ],
    'adding-post__video' => [
        [
            'heading',
            'Заголовок',
            ['validate_filled']
        ],
        [
            'video-url',
            'Ссылка youtube',
            ['validate_youtube_url', 'validate_url', 'validate_filled']
        ]
    ],
    'adding-post__text' => [
        [
            'heading',
            'Заголовок',
            ['validate_filled']
        ],
        [
            'post-text',
            'Текст поста',
            ['validate_filled']
        ]
    ],
    'adding-post__quote' => [
        [
            'heading',
            'Заголовок',
            ['validate_filled']
        ],
        [
            'cite-text',
            'Текст цитаты',
            ['validate_filled']
        ],
        [
            'quote-author',
            'Автор',
            ['validate_filled']
        ]
    ],
    'adding-post__link' => [
        [
            'heading',
            'Заголовок',
            ['validate_filled']
        ],
        [
            'post-link',
            'Ссылка',
            ['validate_url', 'validate_filled']
        ]
    ],
    'registration' => [
        [
            'email',
            'Электронная почта',
            ['is_email_exist', 'validate_email', 'validate_filled']
        ],
        [
            'login',
            'Логин',
            ['validate_filled']
        ],
        [
            'passwd',
            'Пароль',
            ['validate_filled']
        ],
        [
            'passwd-repeat',
            'Повтор пароля',
            ['compare_password', 'validate_filled']
        ],
        [
            'avatar',
            'Изображение',
            ['validate_avatar_file']
        ]
    ],
    'login' => [
        [
            'email',
            'Электронная почта',
            ['validate_email', 'validate_filled']
        ],
        [
            'passwd',
            'Пароль',
            ['validate_filled']
        ]
    ],
    'comments' => [
        [
            'comment',
            'Ваш комментарий',
            ['validate_comment_length', 'validate_filled']
        ],
        [
            'post-id',
            null,
            ['is_post_exist']
        ]
    ],
    'messages' => [
        [
            'message',
            'Ваше сообщение',
            ['validate_filled']
        ],
        [
            'contact-id',
            null,
            ['validate_contact', 'is_user_exist']
        ]
    ]
];

function validate_form(string $form_type, array $post_data): array {
    if (!$form_fields = FORM_TYPES[$form_type] ?? []) {
        return [true];
    }

    $errors = [];

    foreach ($form_fields as $form_field) {
        list($field_name, $field_label, $validation_rules) = $form_field;
        $value = $post_data[$field_name] ?? '';

        foreach ($validation_rules as $validation_rule) {
            if ($error = $validation_rule($value, $post_data)) {
                $errors[$field_name][0] = $error;
                $errors[$field_name][1] = $field_label;
            }
        }
    }

    return $errors;
}

function validate_filled(string $value, array $post_data) {
    if (mb_strlen($value) === 0) {
        return 'Это поле должно быть заполнено';
    }
}

function validate_email(string $value, array $post_data) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'E-mail введён некорректно';
    }
}

function validate_url(string $value, array $post_data) {
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return 'Некорректный url-адрес';
    }
}

function validate_youtube_url(string $value, array $post_data) {
    if (strpos($value, 'youtube.com/watch?v=') === false) {
        return 'Некорректный url-адрес';
    }
}

function validate_comment_length(string $value, array $post_data) {
    if (mb_strlen($value) < 4) {
        return 'Длина комментария не должна быть меньше четырёх символов';
    }
}

function is_post_exist(string $value, array $post_data) {
    if (!Database::getInstance()->isPostValid($value)) {
        return true;
    }
}

function is_user_exist(string $value, array $post_data) {
    if (!Database::getInstance()->isUserValid($value)) {
        return true;
    }
}

function validate_contact(string $value, array $post_data) {
    if (!Database::getInstance()->isContactValid($value)) {
        return true;
    }
}

function is_email_exist(string $value, array $post_data) {
    if (Database::getInstance()->isEmailExist($value)) {
        return 'Пользователь с этим email уже зарегистрирован';
    }
}

function compare_password(string $value, array $post_data) {
    if ($value !== ($post_data['passwd'] ?? '')) {
        return 'Пароли не совпадают';
    }
}

function validate_avatar_file(string $value, array $post_data, int $max_size = 1) {
    if (!empty($_FILES['avatar']['name'])) {
        $file_path = $_FILES['avatar']['tmp_name'];
        $file_size = $_FILES['avatar']['size'];
        $file_type = mime_content_type($file_path);

        if (!in_array($file_type, ACCEPT_MIME_TYPES)) {
            return 'Неверный MIME-тип файла';
        } elseif ($file_size > (BYTES_PER_MEGABYTE * $max_size)) {
            return "Максимальный размер файла: {$max_size}Мб";
        }
    }
}

function validate_image_file(string $value, array $post_data, int $max_size = 1) {
    if (!empty($_FILES['file-photo']['name'])) {
        $file_path = $_FILES['file-photo']['tmp_name'];
        $file_size = $_FILES['file-photo']['size'];
        $file_type = mime_content_type($file_path);

    } elseif (isset($post_data['image-url'])) {
        set_error_handler('exceptions_error_handler');
        try {
            $temp_file = tmpfile();
            $content = file_get_contents($post_data['image-url']);
            fwrite($temp_file, $content);
            $file_path = stream_get_meta_data($temp_file)['uri'];
            $file_size = fstat($temp_file)['size'];
            $file_type = mime_content_type($file_path);
            fclose($temp_file);

        } catch (ErrorException $ex) {
            return 'Вы не загрузили файл';
        }
        restore_error_handler();

    } else {
        return 'Вы не загрузили файл';
    }

    if (!in_array($file_type, ACCEPT_MIME_TYPES)) {
        return 'Неверный MIME-тип файла';
    } elseif ($file_size > (BYTES_PER_MEGABYTE * $max_size)) {
        return "Максимальный размер файла: {$max_size}Мб";
    }
}
