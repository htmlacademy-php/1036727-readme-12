--
-- Добавляет список типов контента для поста
--

INSERT INTO content_type (type_name, class_name, icon_width, icon_height) VALUES
('Фото', 'photo', 22, 18),
('Видео', 'video', 24, 16),
('Текст', 'text', 20, 21),
('Цитата', 'quote', 21, 20),
('Ссылка', 'link', 21, 18);

-- --------------------------------------------------------

--
-- Добавляет список форм
--

INSERT INTO form (name, modifier) VALUES
('adding-post', 'photo'),
('adding-post', 'video'),
('adding-post', 'text'),
('adding-post', 'quote'),
('adding-post', 'link'),
('comments', null),
('registration', null);

-- --------------------------------------------------------

--
-- Добавляет список связей форма - инпут
--

INSERT INTO form_input (form_id, input_id) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 9),

(2, 1),
(2, 4),
(2, 9),

(3, 1),
(3, 5),
(3, 9),

(4, 1),
(4, 6),
(4, 7),
(4, 9),

(5, 1),
(5, 8),
(5, 9),

(6, 10),

(7, 11),
(7, 12),
(7, 13),
(7, 14),
(7, 15);

-- --------------------------------------------------------

--
-- Добавляет список инпутов
--

INSERT INTO input (label, type, name, placeholder, required) VALUES
('Заголовок', 'text', 'heading', 'Введите заголовок', 1),
('Ссылка из интернета', 'text', 'image-url', 'Введите ссылку', 0),
(null, 'file', 'file-photo', null, null),
('Ссылка youtube', 'text', 'video-url', 'Введите ссылку', 1),
('Текст поста', null, 'post-text', 'Введите текст публикации', 1),
('Текст цитаты', null, 'cite-text', 'Текст цитаты', 1),
('Автор', 'text', 'quote-author', 'Автор цитаты', 1),
('Ссылка', 'text', 'post-link', 'Введите ссылку', 1),
('Теги', 'text', 'tags', 'Введите теги', 0),
('Ваш комментарий', null, 'comment', 'Ваш комментарий', 1),
('Электронная почта', 'email', 'email', 'Укажите эл.почту', 1),
('Логин', 'text', 'login', 'Укажите логин', 1),
('Пароль', 'password', 'password', 'Придумайте пароль', 1),
('Повтор пароля', 'password', 'password-repeat', 'Повторите пароль', 1),
(null, 'file', 'avatar', null, null);

-- --------------------------------------------------------

--
-- Добавляет пользователей
--

INSERT INTO user (email, login, password, avatar_path) VALUES
('example1@gmail.com', 'Лариса Роговая', 'password1', 'userpic-larisa.jpg'),
('example2@gmail.com', 'Пётр Дёмин', 'password2', 'userpic-petro.jpg'),
('example3@gmail.com', 'Марк Смолов', 'password3', 'userpic-mark.jpg'),
('example4@gmail.com', 'Таня Фирсова', 'password4', 'userpic-tanya.jpg');

-- --------------------------------------------------------

--
-- Добавляет существующий список постов
--

INSERT INTO post (title, text_content, quote_author, image_path, link, author_id, content_type_id) VALUES
('Цитата', 'Мы в жизни любим только раз, а после ищем лишь похожих', 'Сергей Есенин', null, null, 1, 4),
('Игра престолов', 'Не могу дождаться начала финального сезона своего любимого сериала!', null, null, null, 2, 3),
('Наконец, обработал фотки!', null, null, 'rock-default.jpg', null, 3, 1),
('Моя мечта', null, null, 'rock-default.jpg', null, 1, 1),
('Лучшие курсы', null, null, null, 'www.htmlacademy.ru', 2, 5);

-- --------------------------------------------------------

--
-- Добавляет комментарии
--

INSERT INTO comment (text_content, author_id, post_id) VALUES
('Красота!!!1!', 1, 3),
('Красота!!!1!', 1, 4);

-- --------------------------------------------------------

--
-- Получает список постов с сортировкой по популярности и вместе с именами авторов и типом контента
--

SELECT p.*, u.login AS author, c.type_name AS content_type
FROM post p
INNER JOIN user u ON p.author_id = u.id
INNER JOIN content_type c ON p.content_type_id = c.id
ORDER BY show_count DESC;

-- --------------------------------------------------------

--
-- Получает список постов для конкретного пользователя
--

SELECT * FROM post WHERE author_id = 1;

-- --------------------------------------------------------

--
-- Получает список комментариев для одного поста, в комментариях должен быть логин пользователя
--

SELECT c.id, content, u.login AS author
FROM comment c
INNER JOIN user u ON c.author_id = u.id
WHERE post_id = 3;

-- --------------------------------------------------------

--
-- Добавляет лайк к посту
--

INSERT INTO post_like (author_id, post_id) VALUES (1, 1);

-- --------------------------------------------------------

--
-- Подписка на пользователя
--

INSERT INTO subscription (author_id, user_id) VALUES (1, 2);

-- --------------------------------------------------------
