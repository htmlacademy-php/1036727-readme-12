--
-- Добавляет пользователей
--

INSERT INTO user (email, login, password, avatar_path) VALUES
('example1@gmail.com', 'Лариса Роговая', '$2y$10$SiiKYqvftZ.t9MWw4uVDle7jOQqkyn.T8eWse9DYlQuMR/nAwDm82', '../img/default/userpic-larisa.jpg'),
('example2@gmail.com', 'Пётр Дёмин', '$2y$10$SiiKYqvftZ.t9MWw4uVDle7jOQqkyn.T8eWse9DYlQuMR/nAwDm82', '../img/default/userpic-petro.jpg'),
('example3@gmail.com', 'Марк Смолов', '$2y$10$SiiKYqvftZ.t9MWw4uVDle7jOQqkyn.T8eWse9DYlQuMR/nAwDm82', '../img/default/userpic-mark.jpg'),
('example4@gmail.com', 'Таня Фирсова', '$2y$10$SiiKYqvftZ.t9MWw4uVDle7jOQqkyn.T8eWse9DYlQuMR/nAwDm82', '../img/default/userpic-tanya.jpg'),
('admin@gmail.com', 'admin', '$2y$10$SiiKYqvftZ.t9MWw4uVDle7jOQqkyn.T8eWse9DYlQuMR/nAwDm82', null);

-- --------------------------------------------------------

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
-- Добавляет существующий список постов
--

INSERT INTO post (title, text_content, quote_author, image_path, link, author_id, content_type_id) VALUES
('Цитата', 'Мы в жизни любим только раз, а после ищем лишь похожих', 'Сергей Есенин', null, null, 1, 4),
('Игра престолов', 'Не могу дождаться начала финального сезона своего любимого сериала!', null, null, null, 2, 3),
('Наконец, обработал фотки!', null, null, '../img/default/rock-default.jpg', null, 3, 1),
('Моя мечта', null, null, '../img/default/rock-default.jpg', null, 1, 1),
('Лучшие курсы', 'Интерактивные онлайн-курсы HTML Academy', null, '../img/default/htmlacademy.svg', 'https://htmlacademy.ru', 2, 5);

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
('messages', null),
('registration', null),
('login', null);

-- --------------------------------------------------------

--
-- Добавляет список типов инпутов
--

INSERT INTO input_type (name) VALUES
('text'),
('password'),
('textarea'),
('file'),
('hidden');

-- --------------------------------------------------------

--
-- Добавляет список инпутов
--

INSERT INTO input (label, name, placeholder, required, type_id) VALUES
('Электронная почта', 'email', 'Укажите эл.почту', 1, 1),
('Логин', 'login', 'Укажите логин', 1, 1),
('Пароль', 'password', 'Придумайте пароль', 1, 2),
('Повтор пароля', 'password-repeat', 'Повторите пароль', 1, 2),
(null, 'avatar', null, null, 4),
('Пароль', 'password', 'Введите пароль', 1, 2),
('Заголовок', 'heading', 'Введите заголовок', 1, 1),
('Ссылка из интернета', 'image-url', 'Введите ссылку', 0, 1),
(null, 'file-photo', null, null, 4),
(null, 'content-type', null, null, 5),
('Ссылка youtube', 'video-url', 'Введите ссылку', 1, 1),
('Текст поста', 'post-text', 'Введите текст публикации', 1, 3),
('Текст цитаты', 'cite-text', 'Текст цитаты', 1, 3),
('Автор', 'quote-author', 'Автор цитаты', 1, 1),
('Ссылка', 'post-link', 'Введите ссылку', 1, 1),
('Теги', 'tags', 'Введите теги', 0, 1),
('Ваш комментарий', 'comment', 'Ваш комментарий', 1, 3),
(null, 'post-id', null, null, 5),
('Ваше сообщение', 'message', 'Ваше сообщение', 1, 3),
(null, 'contact-id', null, null, 5);

-- --------------------------------------------------------

--
-- Добавляет список связей форма - инпут
--

INSERT INTO form_input (form_id, input_id) VALUES
(1, 7),
(1, 8),
(1, 16),
(1, 10),
(1, 9),

(2, 7),
(2, 11),
(2, 16),
(2, 10),

(3, 7),
(3, 12),
(3, 16),
(3, 10),

(4, 7),
(4, 13),
(4, 14),
(4, 16),
(4, 10),

(5, 7),
(5, 15),
(5, 16),
(5, 10),

(6, 17),
(6, 18),

(7, 19),
(7, 20),

(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 5),

(9, 1),
(9, 6);

-- --------------------------------------------------------
