--
-- Добавляет список типов контента для поста
--

INSERT INTO content_type (type_name, class_name) VALUES ('Текст', 'text');
INSERT INTO content_type (type_name, class_name) VALUES ('Цитата', 'quote');
INSERT INTO content_type (type_name, class_name) VALUES ('Фото', 'photo');
INSERT INTO content_type (type_name, class_name) VALUES ('Видео', 'video');
INSERT INTO content_type (type_name, class_name) VALUES ('Ссылка', 'link');

-- --------------------------------------------------------

--
-- Добавляет пользователей
--

INSERT INTO user (email, login, password, avatar_path) VALUES ('example1@gmail.com', 'Лариса Роговая', 'password1', 'userpic-larisa.jpg');
INSERT INTO user (email, login, password, avatar_path) VALUES ('example2@gmail.com', 'Пётр Дёмин', 'password2', 'userpic-petro.jpg');
INSERT INTO user (email, login, password, avatar_path) VALUES ('example3@gmail.com', 'Марк Смолов', 'password3', 'userpic-mark.jpg');
INSERT INTO user (email, login, password, avatar_path) VALUES ('example4@gmail.com', 'Таня Фирсова', 'password4', 'userpic-tanya.jpg');

-- --------------------------------------------------------

--
-- Добавляет существующий список постов
--

INSERT INTO post (title, content, quote_author, author_id, content_type_id) VALUES ('Цитата', 'Мы в жизни любим только раз, а после ищем лишь похожих', 'quote_author', 1, 2);
INSERT INTO post (title, content, author_id, content_type_id) VALUES ('Игра престолов', 'Не могу дождаться начала финального сезона своего любимого сериала!', 2, 1);
INSERT INTO post (title, content, image_path, author_id, content_type_id) VALUES ('Наконец, обработал фотки!', 'example', 'rock-medium.jpg', 3, 3);
INSERT INTO post (title, content, image_path, author_id, content_type_id) VALUES ('Моя мечта', 'example', 'coast-medium.jpg', 1, 3);
INSERT INTO post (title, content, link, author_id, content_type_id) VALUES ('Лучшие курсы', 'example', 'www.htmlacademy.ru', 2, 5);

-- --------------------------------------------------------

--
-- Добавляет комментарии
--

INSERT INTO comment (content, author_id, post_id) VALUES ('Красота!!!1!', 1, 3);
INSERT INTO comment (content, author_id, post_id) VALUES ('Красота!!!1!', 1, 4);

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
