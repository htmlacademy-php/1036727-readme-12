<?php

namespace anatolev;

class Database
{
    private $mysqli;
    private static $db;

    /**
     * Возвращает ээкземпляр класса Database
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$db === null) {
            self::$db = new Database();
        }

        return self::$db;
    }

    private function __construct()
    {
        $db_config = require_once('config/db.php');
        $db_config = array_values($db_config);
        $this->mysqli = new \mysqli(...$db_config);

        if (!$this->mysqli) {
            http_response_code(500);
            exit;
        }

        $this->mysqli->set_charset('utf8');
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    /**
     * Выполняет sql-запрос и возвращает массив содержащий ассоциативные массивы
     * с данными результирующей таблицы
     *
     * @param string $sql SQL-запрос с плейсхолдерами вместо значений
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     *
     * @return array
     */
    public function select(string $sql, array $stmt_data = []): array
    {
        $stmt = $this->executeQuery($sql, $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Выполняет sql-запрос и возвращает ассоциативный массив значений,
     * соответствующий результирующей выборке, где каждый ключ в массиве
     * соответствует имени одного из столбцов выборки или null,
     * если других строк не существует
     *
     * @param string $sql SQL-запрос с плейсхолдерами вместо значений
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     *
     * @return array
     */
    public function selectOne(string $sql, array $stmt_data = [])
    {
        $stmt = $this->executeQuery($sql, $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->fetch_assoc();
    }

    /**
     * Выполняет sql-запрос и возвращает число рядов в результирующей выборке
     *
     * Замечание:
     * Если число рядов больше чем PHP_INT_MAX,
     * то число будет возвращено в виде строки
     *
     * @param string $sql SQL-запрос с плейсхолдерами вместо значений
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     *
     * @return int|string
     */
    public function getNumRows(string $sql, array $stmt_data = [])
    {
        $stmt = $this->executeQuery($sql, $stmt_data);
        if (!$result = $stmt->get_result()) {
            http_response_code(500);
            exit;
        }

        return $result->num_rows;
    }

    /**
     * Выполняет sql-запрос и возвращает значение поля AUTO_INCREMENT,
     * которое было затронуто предыдущим запросом.
     * Возвращает ноль, если предыдущий запрос не затронул таблицы,
     * содержащие поле AUTO_INCREMENT
     *
     * @param string $sql SQL-запрос с плейсхолдерами вместо значений
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     *
     * @return int
     */
    public function getLastId(string $sql, array $stmt_data = []): int
    {
        $stmt = $this->getPrepareStmt($sql, $stmt_data);
        if (!$stmt->execute()) {
            http_response_code(500);
            exit;
        }

        return $stmt->insert_id;
    }

    /**
     * Создаёт и выполняет подготовленное выражение.
     * Возвращает экземпляр класса mysqli_stmt в случае успешного завершения
     * или завершает скрипт с 500 кодом ответа HTTP в случае возникновения ошибки
     *
     * @param string $sql SQL-запрос с плейсхолдерами вместо значений
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     *
     * @return mysqli_stmt
     */
    public function executeQuery(string $sql, array $stmt_data = []): \mysqli_stmt
    {
        $stmt = $this->getPrepareStmt($sql, $stmt_data);
        if (!$stmt->execute()) {
            http_response_code(500);
            exit;
        }

        return $stmt;
    }

    /**
     * Создаёт подготовленное выражение на основе готового SQL запроса и переданных данных.
     * Возвращает экземпляр класса mysqli_stmt в случае успешного завершения
     * или завершает скрипт с 500 кодом ответа HTTP в случае возникновения ошибки
     *
     * @param string $sql SQL-запрос с плейсхолдерами вместо значений
     * @param array $data Данные для вставки на место плейсхолдеров
     *
     * @return mysqli_stmt Подготовленное выражение
     */
    private function getPrepareStmt(string $sql, array $data): \mysqli_stmt
    {
        if (!$stmt = $this->mysqli->prepare($sql)) {
            http_response_code(500);
            exit;
        }

        if ($data) {
            $types = '';
            $stmt_data = [];

            foreach ($data as $value) {
                $type = 's';

                if (is_int($value)) {
                    $type = 'i';
                } elseif (is_double($value)) {
                    $type = 'd';
                } elseif (is_string($value)) {
                    $type = 's';
                }

                if ($type) {
                    $types .= $type;
                    $stmt_data[] = $value;
                }
            }

            $values = array_merge([$types], $stmt_data);

            if (!$stmt->bind_param(...$values)) {
                http_response_code(500);
                exit;
            }
        }

        return $stmt;
    }

    /**
     * Возвращает типы контента
     * @return array
     */
    public function getContentTypes(): array
    {
        $query = (new QueryBuilder())
            ->select(['id', 'type_name', 'class_name', 'icon_width', 'icon_height'])
            ->from('content_type');

        return $query->all();
    }

    /**
     * Возвращает инпуты (readme.input) связанные с формой,
     * в качестве ключей для каждого инпута используется input.name
     * @param string $form Название формы (form.name)
     *
     * @return array
     */
    public function getFormInputs(string $form): array
    {
        $query = (new QueryBuilder())
            ->select(['id', 'label', 'name', 'placeholder', 'required'], 'i.')
            ->addSelect(['it.name AS type', 'f.name AS form'])
            ->from('input i')
            ->join('LEFT', 'input_type it', 'it.id = i.type_id')
            ->join('LEFT', 'form_input fi', 'fi.input_id = i.id')
            ->join('LEFT', 'form f', 'f.id = fi.form_id')
            ->where('=', 'f.name', '?');
        $form_inputs = $query->all([$form]);
        $input_names = array_column($form_inputs, 'name');

        return array_combine($input_names, $form_inputs);
    }

    /**
     * Проверяет наличие типа контента в БД,
     * возвращает true при наличии или false в случае отсутствия
     * @param string $content_type Тип контента
     *
     * @return bool
     */
    public function isContentTypeExist(string $content_type): bool
    {
        $query = (new QueryBuilder())
            ->select(['class_name'])
            ->from('content_type')
            ->where('=', 'class_name', '?');

        return $query->exists([$content_type]);
    }

    /**
     * Возвращает данные для отрисовки табов
     * (templates/add.php)
     *
     * @return array
     */
    public function getTabsContentData(): array
    {
        $query = (new QueryBuilder())
            ->select(['class_name'])
            ->from('content_type');

        $content_types = $query->all();
        $tabs_content = [];

        for ($i = 0; $i < count($content_types); $i++) {
            $ctype = $content_types[$i]['class_name'];
            $query = (new QueryBuilder())
                ->select(['i.name'])
                ->from('input i')
                ->join('LEFT', 'form_input fi', 'fi.input_id = i.id')
                ->join('LEFT', 'form f', 'f.id = fi.form_id')
                ->where('=', 'f.modifier', '?');

            $input_names = array_column($query->all([$ctype]), 'name');
            $tabs_content[$ctype] = array_filter($input_names, function ($val) {
                return !in_array($val, ['content-type', 'file-photo']);
            });
        }

        return $tabs_content;
    }

    /**
     * Добавляет публикацию в БД и возвращает её идентификатор
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     *
     * @return int Идентификатор добавленной публикации
     */
    public function insertPost(array $stmt_data): int
    {
        $post_fields = getPostFields('insert');
        $query = (new QueryBuilder())
            ->insert('post', $post_fields, array_fill(0, 10, '?'));

        return $this->getLastId($query->getQuery(), $stmt_data);
    }

    /**
     * Проверяет хэштеги на наличие в БД и возвращает существующие
     * @param array $hashtags Хэштеги для проверки
     *
     * @return array Существующие хэштеги
     */
    public function getExistHashtags(array $hashtags): array
    {
        $placeholders = array_fill(0, count($hashtags), '?');
        $placeholders = implode(', ', $placeholders);
        $query = (new QueryBuilder())
            ->select(['id', 'name'])
            ->from('hashtag')
            ->where('IN', 'name', "({$placeholders})");

        return $query->all($hashtags);
    }

    /**
     * Добавляет хэштег в БД и возвращает его идентификатор
     * @param string $hashtag Хэштег
     *
     * @return int Идентификатор добавленного хэштега
     */
    public function insertHashtag(string $hashtag): int
    {
        $query = (new QueryBuilder())
            ->insert('hashtag', ['name'], ['?']);

        return $this->getLastId($query->getQuery(), [$hashtag]);
    }

    /**
     * Добавляет связь публикация-хэштег в БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     */
    public function insertPostHashtag(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->insert('post_hashtag', ['hashtag_id', 'post_id'], ['?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Возвращает подписчиков аутентифицированного пользователя
     * @return array
     */
    public function getSubscribers(): array
    {
        $query = (new QueryBuilder())
            ->select(['u.email', 'u.login'])
            ->from('user u')
            ->join('LEFT', 'subscription s', 's.author_id = u.id')
            ->where('=', 's.user_id', '?');

        return $query->all([$_SESSION['user']['id']]);
    }

    /**
     * Возвращает количество непрочитанных сообщений
     * аутентифицированного пользователя
     * @return string
     */
    public function getUnreadMessageCount(): string
    {
        $query = (new QueryBuilder())
            ->select(['COUNT(id)'])
            ->from('message')
            ->where('=', 'recipient_id', '?')
            ->andWhere('=', 'status', '0');

        return $query->one([$_SESSION['user']['id']])['COUNT(id)'];
    }

    /**
     * Возвращает хэштеги связанные с публикацией
     * @param int $post_id Идентификатор публикации
     *
     * @return array
     */
    public function getPostHashtags(int $post_id): array
    {
        $query = (new QueryBuilder())
            ->select(['h.name'])
            ->from('hashtag h')
            ->join('LEFT', 'post_hashtag ph', 'ph.hashtag_id = h.id')
            ->join('LEFT', 'post p', 'p.id = ph.post_id')
            ->where('=', 'p.id', '?');

        return $query->all([$post_id]);
    }

    /**
     * Возвращает публикации выбранного типа контента для сценария feed.php
     *
     * 1. Ключ 'hashtags' содержит хэштеги публикации (array)
     *
     * @param string $content_type Тип контента
     * @return array
     */
    public function getFeedPosts(string $content_type): array
    {
        $post_fields = getPostFields();
        $user_id = $_SESSION['user']['id'];
        $stmt_data = array_filter([$user_id, $user_id, $content_type]);
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id')
            ->andWhere('=', 'pl2.author_id', '?')
            ->join('LEFT', 'subscription s', 's.user_id = p.author_id')
            ->where('=', 's.author_id', '?')
            ->andFilterWhere($content_type, 'ct.class_name = ?')
            ->groupBy('p.id')
            ->orderBy('p.dt_add ASC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $hashtags = $this->getPostHashtags($posts[$i]['id']);
            $posts[$i]['hashtags'] = $hashtags;
        }

        return $posts;
    }

    /**
     * Возвращает пользователя по электронной почте
     * или null в случае отсутствия
     * @param string $email Электронная почта
     *
     * @return array|null
     */
    public function getUserByEmail(string $email)
    {
        $query = (new QueryBuilder())
            ->select(['id', 'dt_add', 'email', 'login', 'password', 'avatar_path'])
            ->from('user')
            ->where('=', 'email', '?');

        return $query->one([$email]) ?? null;
    }

    /**
     * Проверяет наличие публикации в БД по идентификатору,
     * возвращает true при наличии или false в случае отсутствия
     * @param int $post_id Идентификтор публикации
     *
     * @return bool
     */
    public function isPostExist(int $post_id): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('post')
            ->where('=', 'id', '?');

        return $query->exists([$post_id]);
    }

    /**
     * Проверяет наличие публикации в БД по идентификатору,
     * возвращает идентификатор публикации при наличии
     * или завершает скрипт с 404 кодом ответа HTTP при отсутствии
     * @param int $post_id Идентификатор публикации
     *
     * @return int $post_id
     */
    public function validatePost(int $post_id): int
    {
        if (!$this->isPostExist($post_id)) {
            http_response_code(404);
            exit;
        }

        return $post_id;
    }

    /**
     * Проверяет наличие связи публикация-лайк в БД,
     * возвращает true при наличии или false в случае отсутствия
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [id публикации, id автора]
     *
     * @return bool
     */
    public function isPostLike(array $stmt_data): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('post_like')
            ->where('=', 'post_id', '?')
            ->andWhere('=', 'author_id', '?');

        return $query->exists($stmt_data);
    }

    /**
     * Добавляет связь публикация-лайк в БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [id публикации, id автора]
     */
    public function insertPostLike(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->insert('post_like', ['post_id', 'author_id'], ['?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Удаляет связь публикация-лайк из БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [id публикации, id автора]
     */
    public function deletePostLike(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->delete('post_like')
            ->where('=', 'post_id', '?')
            ->andWhere('=', 'author_id', '?');

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Проверяет наличие пользователя в БД по идентификатору,
     * возвращает true при наличии или false в случае отсутствия
     * @param int $user_id Идентификтор пользователя
     *
     * @return bool
     */
    public function isUserExist(int $user_id): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('user')
            ->where('=', 'id', '?');

        return $query->exists([$user_id]);
    }

    /**
     * Проверяет наличие пользователя в БД по идентификатору,
     * возвращает идентификатор пользователя при наличии
     * или завершает скрипт с 404 кодом ответа HTTP при отсутствии
     * @param int $user_id Идентификатор пользователя
     *
     * @return int $user_id
     */
    public function validateUser(int $user_id): int
    {
        if (!$this->isUserExist($user_id)) {
            http_response_code(404);
            exit;
        }

        return $user_id;
    }

    /**
     * Проверяет контакт (пользователя) на соответствие условиям:
     *
     * 1. На пользователя должна быть подписка
     * 2. С пользователем должна быть переписка
     *
     * возвращает true если выполняется как минимум одно условие
     * или false если не выполняются оба условия
     *
     * @param int $contact_id Идентификатор пользователя
     *
     * @return bool
     */
    public function isContactValid(int $contact_id): bool
    {
        $user_id = $_SESSION['user']['id'];
        $stmt_data = [$user_id, $user_id, $contact_id, $contact_id];
        $query = (new QueryBuilder())
            ->select(['u.id'])
            ->from('user u')
            ->join('LEFT', 'subscription s', 's.user_id = u.id AND s.author_id = ?')
            ->join('LEFT', 'message m', 'm.recipient_id = ? AND m.sender_id = ?')
            ->where('=', 'u.id', '?')
            ->groupBy('u.id')
            ->having('>', 'COUNT(s.id)', '0')
            ->orHaving('>', 'COUNT(m.id)', '0');

        return $query->exists($stmt_data);
    }

    /**
     * Добавляет сообщение в БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     */
    public function insertMessage(array $stmt_data)
    {
        $message_fields = ['content', 'sender_id', 'recipient_id'];
        $query = (new QueryBuilder())
            ->insert('message', $message_fields, ['?', '?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Обновляет статус всех сообщений от выбранного пользователя
     * на "прочитано" (message.status)
     *
     * 0 - не прочитано
     * 1 - прочитано
     *
     * @param int $contact_id Идентификатор пользователя
     */
    public function updateMessagesStatus(int $contact_id)
    {
        $stmt_data = [$contact_id, $_SESSION['user']['id']];
        $query = (new QueryBuilder())
            ->update('message', ['status' => '1'])
            ->where('=', 'sender_id', '?')
            ->andWhere('=', 'recipient_id', '?');

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Возвращает preview последнего отправленного сообщения
     * из переписки с выбранным пользователем
     *
     * 1. Текст сообщения обрезается до максимальной длинны
     * 2. Если отправителем является аутентифицированный пользователь
     * перед сообщением добавляется: 'Вы: '
     *
     * ['text' => текст preview, 'time' => время сообщения]
     *
     * @param int $contact_id Идентификатор пользователя
     * @param int $max_length Максимальная длина preview
     *
     * @return array Preview сообщения
     */

    public function getMessagePreview(int $contact_id, int $max_length = 30): array
    {
        $user_id = $_SESSION['user']['id'];
        $stmt_data = [$user_id, $contact_id, $contact_id, $user_id];
        $query = (new QueryBuilder())
            ->select(['dt_add', 'content', 'sender_id'])
            ->from('message')
            ->where(
                'OR',
                '(recipient_id = ? AND sender_id = ?)',
                '(recipient_id = ? AND sender_id = ?)'
            )
            ->orderBy('dt_add DESC')->limit('1');
        $message = $query->one($stmt_data);
        $preview = mb_substr($message['content'], 0, $max_length);
        $preview = $message['sender_id'] === $user_id ? "Вы: $preview" : $preview;

        return ['text' => $preview, 'time' => $message['dt_add']];
    }

    /**
     * Возвращает сообщения из переписки с выбранным пользователем
     * @param int $contact_id Идентификатор пользователя
     * @return array
     */
    public function getContactMessages(int $contact_id): array
    {
        $user_id = $_SESSION['user']['id'];
        $stmt_data = [$user_id, $contact_id, $contact_id, $user_id];
        $query = (new QueryBuilder())
            ->select(['id', 'dt_add', 'content', 'status', 'sender_id', 'recipient_id'], 'm.')
            ->addSelect(['u.login AS author', 'u.avatar_path'])
            ->from('message m')
            ->join('LEFT', 'user u', 'u.id = m.sender_id')
            ->where(
                'OR',
                '(m.recipient_id = ? AND m.sender_id = ?)',
                '(m.recipient_id = ? AND m.sender_id = ?)'
            )
            ->orderBy('m.dt_add ASC');

        return $query->all($stmt_data);
    }

    /**
     * Возвращает контакты (пользователей с которыми была переписка)
     * аутентифицированного пользователя
     *
     * 1. Ключ 'preview' содержит preview последнего сообщения (array)
     * 2. Ключ 'messages' содержит переписку (array)
     *
     * @return array
     */
    public function getContacts(): array
    {
        $stmt_data = array_fill(0, 4, $_SESSION['user']['id']);
        $query = (new QueryBuilder())
            ->select(['COUNT(DISTINCT m2.id) AS unread_message_count'])
            ->addSelect(['u.id', 'u.login', 'u.avatar_path'])
            ->from('message m')
            ->join('LEFT', 'user u', 'u.id = m.sender_id OR u.id = m.recipient_id')
            ->join('LEFT', 'message m2', 'm2.recipient_id = ? AND m2.sender_id = u.id AND m2.status = 0')
            ->where('AND', '(m.sender_id = ? OR m.recipient_id = ?)', 'u.id != ?')
            ->groupBy('u.id')
            ->orderBy('MAX(m.dt_add) DESC');
        $contacts = $query->all($stmt_data);

        for ($i = 0; $i < count($contacts); $i++) {
            $contact_id = $contacts[$i]['id'];
            $contacts[$i]['preview'] = $this->getMessagePreview($contact_id);
            $contacts[$i]['messages'] = $this->getContactMessages($contact_id);
        }

        return $contacts;
    }

    /**
     * Добавляет пользователя в начало переданного массива контактов
     * при условии, что аутентифицированный пользователь подписан на выбранного,
     * +устанавливает cookie с идентификатором добавленного пользователя.
     * Возвращает true в случае успешного завершения или false
     *
     * @param array &$contacts Массив контактов (по ссылке)
     * @param int $contact_id Идентификатор пользователя
     *
     * @return bool
     */
    public function addNewContact(array &$contacts, int $contact_id): bool
    {
        $stmt_data = [$_SESSION['user']['id'], $contact_id];
        $query = (new QueryBuilder())
            ->select(['u.id', 'u.login', 'u.avatar_path'])
            ->from('user u')
            ->join('LEFT', 'subscription s', 's.user_id = u.id AND s.author_id = ?')
            ->where('=', 'u.id', '?')
            ->groupBy('u.id')
            ->having('>', 'COUNT(s.id)', '0');
        $contact = $query->one($stmt_data) ?? null;

        if ($contact) {
            array_unshift($contacts, $contact);
            return setcookie('new_contact', $contact_id);
        }

        return false;
    }

    /**
     * Возвращает количество публикаций выбранного типа контента
     * @param string $content_type Тип контента
     * @return int
     */
    public function getItemsCount(string $content_type): int
    {
        $stmt_data = array_filter([$content_type]);
        $query = (new QueryBuilder())
            ->select(['COUNT(p.id)'])
            ->from('post p')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->filterWhere($content_type, 'ct.class_name = ?');

        return intval($query->one($stmt_data)['COUNT(p.id)']);
    }

    /**
     * Возвращает публикации для сценария popular.php,
     * используя следующие параметры:
     *
     * 1. Выбранный тип контента
     * 2. Сортировка
     * 3. Направление сортировки
     * 4. offset и максимальное количество публикаций
     *
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [id аутентифицированного пользователя, тип контента, offset, лимит публикаций]
     * @param string Сортировка +направление для sql-запроса
     *
     * @return array
     */
    public function getPopularPosts(array $stmt_data, string $order): array
    {
        $post_fields = getPostFields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id')
            ->andWhere('=', 'pl2.author_id', '?')
            ->filterWhere($stmt_data[1], 'ct.class_name = ?')
            ->groupBy('p.id')
            ->orderBy($order)->limit('?')->offset('?');

        return $query->all(array_filter($stmt_data, function ($val) {
            return $val !== '';
        }));
    }

    /**
     * Добавляет комментарий в БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [текст комментария, id автора, id публикации]
     */
    public function insertComment(array $stmt_data)
    {
        $comment_fields = ['content', 'author_id', 'post_id'];
        $query = (new QueryBuilder())
            ->insert('comment', $comment_fields, ['?', '?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Возвращает идентификатор автора публикации
     * @param int $post_id Идентификатор публикации
     * @return int
     */
    public function getPostAuthorId(int $post_id): int
    {
        $query = (new QueryBuilder())
            ->select(['author_id'])
            ->from('post')
            ->where('=', 'id', '?');

        return intval($query->one([$post_id])['author_id']);
    }

    /**
     * Увеличивает количество просмотров публикации (post.show_count) на 1
     * @param int $post_id Идентификатор публикации
     */
    public function updatePostShowCount(int $post_id)
    {
        $query = (new QueryBuilder())
            ->update('post', ['show_count' => 'show_count + 1'])
            ->where('=', 'id', '?');

        $this->executeQuery($query->getQuery(), [$post_id]);
    }

    /**
     * Возвращает публикацию для сценария post.php,
     * устанавливает ключ 'display_mode' в значение 'details'
     * @param int $post_id Идентификатор публикации
     *
     * @return array
     */
    public function getPostDetails(int $post_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $post_id];
        $post_fields = getPostFields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->where('=', 'p.id', '?')
            ->groupBy('p.id');
        $post = $query->one($stmt_data);
        $post['display_mode'] = 'details';

        return $post;
    }

    /**
     * Возвращает автора публикации (пользователя)
     * @param int $post_id Идентификатор публикации
     * @return array
     */
    public function getPostAuthor(int $post_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $post_id];
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT s.id) AS is_subscription',
                'COUNT(DISTINCT s2.id) AS subscriber_count',
                'COUNT(DISTINCT p2.id) AS publication_count'
            ])
            ->addSelect(['u.dt_add', 'u.login', 'u.avatar_path'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'post p2', 'p2.author_id = p.author_id')
            ->join('LEFT', 'subscription s', 's.user_id = p.author_id AND s.author_id = ?')
            ->join('LEFT', 'subscription s2', 's2.user_id = p.author_id')
            ->where('=', 'p.id', '?')
            ->groupBy('p.id');

        return $query->one($stmt_data);
    }

    /**
     * Возвращает комментарии связанные с публикацией,
     * ограничивая их максимальное количество
     * @param int $post_id Идентификатор публикации
     * @param int $limit Максимальное количество комментариев
     *
     * @return array
     */
    public function getPostComments(int $post_id, int $limit): array
    {
        $stmt_data = array_filter([$post_id, $limit], function ($val) {
            return $val > 0;
        });
        $query = (new QueryBuilder())
            ->select(['c.id', 'c.dt_add', 'c.content', 'c.author_id', 'c.post_id'])
            ->addSelect(['u.login', 'u.avatar_path'])
            ->from('comment c')
            ->join('LEFT', 'user u', 'u.id = c.author_id')
            ->where('=', 'c.post_id', '?')
            ->orderBy('c.dt_add DESC')
            ->filterLimit($limit, '?');

        return $query->all($stmt_data);
    }

    /**
     * Возвращает пользователя для сценария profile.php
     * @param int $profile_id Идентификатор пользователя
     * @return array
     */
    public function getUserProfile(int $profile_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $profile_id];
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT s.id) AS is_subscription',
                'COUNT(DISTINCT s2.id) AS subscriber_count',
                'COUNT(DISTINCT p.id) AS publication_count'
            ])
            ->addSelect(['u.id', 'u.dt_add', 'u.login', 'u.avatar_path'])
            ->from('user u')
            ->join('LEFT', 'post p', 'p.author_id = u.id')
            ->join('LEFT', 'subscription s', 's.user_id = u.id AND s.author_id = ?')
            ->join('LEFT', 'subscription s2', 's2.user_id = u.id')
            ->where('=', 'u.id', '?')
            ->groupBy('u.id');

        return $query->one($stmt_data);
    }

    /**
     * Возвращает публикацию для метода $this->getProfilePosts
     * @param int $post_id Идентификатор публикации
     * @return array
     */
    public function getRepost(int $post_id): array
    {
        $query = (new QueryBuilder())
            ->select(['p.dt_add', 'p.author_id', 'u.login AS author', 'u.avatar_path'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->where('=', 'p.id', '?');

        return $query->one([$post_id]);
    }

    /**
     * Возвращает публикации выбранного автора для сценария profile.php,
     * ограничивая максимальное количество комментариев для каждой публикации
     *
     * 1. Ключ 'hashtags' содержит хэштеги публикации (array)
     * 2. Ключ 'comments' содержит комментарии публикации (array)
     * 3. Ключ 'origin' содержит оригинальную публикацию при условии,
     * что ключ 'is_repost' установлен в true (array)
     *
     * @param int $profile_id Идентификатор пользователя
     * @param int $limit Максимальное количество комментариев
     *
     * @return array
     */
    public function getProfilePosts(int $profile_id, int $limit): array
    {
        $stmt_data = [$_SESSION['user']['id'], $profile_id];
        $post_fields = getPostFields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->where('=', 'p.author_id', '?')
            ->groupBy('p.id')
            ->orderBy('p.dt_add ASC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $post = $posts[$i];
            $posts[$i]['hashtags'] = $this->getPostHashtags($post['id']);
            $posts[$i]['comments'] = $this->getPostComments($post['id'], $limit);

            $is_repost = $post['is_repost'] && $post_id = $post['origin_post_id'];
            $posts[$i]['origin'] = $is_repost ? $this->getRepost($post_id) : [];
        }

        return $posts;
    }

    /**
     * Возвращает публикации выбранного пользователя для сценария profile.php,
     * с минимальным количеством лайков - 1
     * @param int $profile_id Идентификатор пользователя
     * @return array
     */
    public function getProfileLikes(int $profile_id): array
    {
        $post_fields = getPostFields();
        $query = (new QueryBuilder())
            ->select($post_fields, 'p.')
            ->addSelect(['u.id AS user_id', 'u.login AS author', 'u.avatar_path'])
            ->addSelect(['ct.type_name', 'ct.class_name', 'ct.icon_width', 'ct.icon_height'])
            ->addSelect(['pl.dt_add'])
            ->from('post p')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'user u', 'u.id = pl.author_id')
            ->where('=', 'p.author_id', '?')
            ->groupBy('p.id, pl.id, u.id')
            ->having('>', 'COUNT(pl.id)', '0')
            ->orderBy('pl.dt_add DESC');

        return $query->all([$profile_id]);
    }

    /**
     * Возвращает подписки выбранного пользователя
     * @param int $profile_id Идентификатор пользователя
     * @return array
     */
    public function getProfileSubscriptions(int $profile_id): array
    {
        $stmt_data = [$_SESSION['user']['id'], $profile_id];
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT s2.id) AS is_subscription',
                'COUNT(DISTINCT s3.id) AS subscriber_count',
                'COUNT(DISTINCT p.id) AS publication_count'
            ])
            ->addSelect(['u.id', 'u.dt_add', 'u.login', 'u.avatar_path'])
            ->from('subscription s')
            ->join('LEFT', 'user u', 'u.id = s.user_id')
            ->join('LEFT', 'post p', 'p.author_id = u.id')
            ->join('LEFT', 'subscription s2', 's2.user_id = u.id AND s2.author_id = ?')
            ->join('LEFT', 'subscription s3', 's3.user_id = u.id')
            ->where('=', 's.author_id', '?')
            ->groupBy('s.id');

        return $query->all($stmt_data);
    }

     /**
     * Проверяет наличие пользователя в БД по электронной почте,
     * возвращает true при наличии или false в случае отсутствия
     * @param string $email Электронная почта
     *
     * @return bool
     */
    public function isEmailExist(string $email): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('user')
            ->where('=', 'email', '?');

        return $query->exists([$email]);
    }

    /**
     * Добавляет пользователя в БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [электронная почта, логин, хэш пароля, путь до аватарки]
     */
    public function insertUser(array $stmt_data)
    {
        $user_fields = ['email', 'login', 'password', 'avatar_path'];
        $query = (new QueryBuilder())
            ->insert('user', $user_fields, ['?', '?', '?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Возвращает публикацию для сценария repost.php
     * @param int $post_id Идентификатор публикации
     * @return array
     */
    public function getPost(int $post_id): array
    {
        $post_fields = getPostFields('insert');
        $query = (new QueryBuilder())
            ->select($post_fields)
            ->from('post')
            ->where('=', 'id', '?');

        return $query->one([$post_id]);
    }

    /**
     * Возвращает идентификаторы хэштегов связанных с публикацией
     * @param int $post_id Идентификатор публикации
     * @return array
     */
    public function getPostHashtagIds(int $post_id): array
    {
        $query = (new QueryBuilder())
            ->select(['hashtag_id AS id'])
            ->from('post_hashtag')
            ->where('=', 'post_id', '?');

        return $query->all([$post_id]);
    }

    /**
     * Возвращает публикации связанные с хэштегом
     *
     * 1. Ключ 'hashtags' содержит хэштеги публикации (array)
     *
     * @param string $hashtag Хэштег
     * @return array
     */
    public function getPostsByHashtag(string $hashtag): array
    {
        $stmt_data = [$_SESSION['user']['id'], $hashtag];
        $post_fields = getPostFields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->join('LEFT', 'post_hashtag ph', 'ph.post_id = p.id')
            ->join('LEFT', 'hashtag h', 'h.id = ph.hashtag_id')
            ->where('=', 'h.name', '?')
            ->groupBy('p.id')
            ->orderBy('p.dt_add DESC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $hashtags = $this->getPostHashtags($posts[$i]['id']);
            $posts[$i]['hashtags'] = $hashtags;
        }

        return $posts;
    }

    /**
     * Возвращает публикации, используя полнотекстовый поиск по следующим полям:
     * (post.title и post.text_content)
     *
     * 1. Ключ 'hashtags' содержит хэштеги публикации (array)
     *
     * @param string $query Строка запроса
     * @return array
     */
    public function getPostsByQueryString(string $query): array
    {
        $stmt_data = [$query, $_SESSION['user']['id'], $query];
        $post_fields = getPostFields();
        $query = (new QueryBuilder())
            ->select([
                'COUNT(DISTINCT p2.id) AS repost_count',
                'COUNT(DISTINCT c.id) AS comment_count',
                'COUNT(DISTINCT pl.id) AS like_count',
                'COUNT(DISTINCT pl2.id) AS is_like'
            ])
            ->addSelect(['MATCH (p.title, p.text_content) AGAINST (?) AS score'])
            ->addSelect($post_fields, 'p.')
            ->addSelect(['u.login AS author', 'u.avatar_path', 'ct.class_name'])
            ->from('post p')
            ->join('LEFT', 'user u', 'u.id = p.author_id')
            ->join('LEFT', 'content_type ct', 'ct.id = p.content_type_id')
            ->join('LEFT', 'post p2', 'p2.origin_post_id = p.id')
            ->join('LEFT', 'comment c', 'c.post_id = p.id')
            ->join('LEFT', 'post_like pl', 'pl.post_id = p.id')
            ->join('LEFT', 'post_like pl2', 'pl2.post_id = p.id AND pl2.author_id = ?')
            ->where('AGAINST', 'MATCH (p.title, p.text_content)', '(? IN BOOLEAN MODE)')
            ->groupBy('p.id')
            ->orderBy('score DESC');
        $posts = $query->all($stmt_data);

        for ($i = 0; $i < count($posts); $i++) {
            $hashtags = $this->getPostHashtags($posts[$i]['id']);
            $posts[$i]['hashtags'] = $hashtags;
        }

        return $posts;
    }

    /**
     * Проверяет наличие подписки в БД,
     * возвращает true при наличии или false в случае отсутствия
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [автор подписки, %пользователь, на которого подписался автор%]
     *
     * @return bool
     */
    public function isSubscription(array $stmt_data): bool
    {
        $query = (new QueryBuilder())
            ->select(['id'])
            ->from('subscription')
            ->where('=', 'author_id', '?')
            ->andWhere('=', 'user_id', '?');

        return $query->exists($stmt_data);
    }

    /**
     * Добавляет подписку в БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [автор подписки, %пользователь, на которого подписался автор%]
     */
    public function insertSubscription(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->insert('subscription', ['author_id', 'user_id'], ['?', '?']);

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Удаляет подписку из БД
     * @param array $stmt_data Данные для вставки на место плейсхолдеров
     * [автор подписки, %пользователь, на которого подписался автор%]
     */
    public function deleteSubscription(array $stmt_data)
    {
        $query = (new QueryBuilder())
            ->delete('subscription')
            ->where('=', 'author_id', '?')
            ->andWhere('=', 'user_id', '?');

        $this->executeQuery($query->getQuery(), $stmt_data);
    }

    /**
     * Возвращает пользователя для сценария subscription.php
     * @param int $profile_id Идентификатор пользователя
     * @return array
     */
    public function getSubscription(int $profile_id): array
    {
        $query = (new QueryBuilder())
            ->select(['id', 'email', 'login'])
            ->from('user')
            ->where('=', 'id', '?');

        return $query->one([$profile_id]);
    }
}
