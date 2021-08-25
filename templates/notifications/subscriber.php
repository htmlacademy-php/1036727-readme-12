<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <h1>Уведомление о новом подписчике</h1>

    <?php $link = "http://readme.net/profile.php?id={$_SESSION['user']['id']}"; ?>
    <p>
        Здравствуйте, <?= esc($recipient['login']) ?>.
        На вас подписался новый пользователь <?= esc($_SESSION['user']['login']) ?>.
        Вот ссылка на его профиль: <a href="<?= esc($link) ?>"><?= esc($link) ?></a>
    </p>

</body>
</html>
