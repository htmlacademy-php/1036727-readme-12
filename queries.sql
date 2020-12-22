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

INSERT INTO form (name) VALUES
('adding-post__photo'),
('adding-post__video'),
('adding-post__text'),
('adding-post__quote'),
('adding-post__link'),
('comments__form');

-- --------------------------------------------------------

--
-- Добавляет список связей форма - инпут
--

INSERT INTO form_input (form_id, input_id) VALUES
(1, 1),
(1, 2),
(1, 8),

(2, 1),
(2, 3),
(2, 8),

(3, 1),
(3, 4),
(3, 8),

(4, 1),
(4, 5),
(4, 6),
(4, 8),

(5, 1),
(5, 7),
(5, 8);

-- --------------------------------------------------------

--
-- Добавляет список инпутов
--

INSERT INTO input (label, name, placeholder, required) VALUES
('Заголовок', 'heading', 'Введите заголовок', 1),
('Ссылка из интернета', 'image-url', 'Введите ссылку', 0),
('Ссылка youtube', 'video-url', 'Введите ссылку', 1),
('Текст поста', 'post-text', 'Введите текст публикации', 1),
('Текст цитаты', 'cite-text', 'Текст цитаты', 1),
('Автор', 'quote-author', 'Автор цитаты', 1),
('Ссылка', 'post-link', 'Введите ссылку', 1),
('Теги', 'tags', 'Введите теги', 0);

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
