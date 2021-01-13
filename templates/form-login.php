<div class="container">
    <h1 class="page__title page__title--login">Вход</h1>
</div>
<section class="login container">
    <h2 class="visually-hidden">Форма авторизации</h2>
    <form style="padding-bottom: 2px;" class="login__form form" action="/login.php" method="post">
        <div class="form__text-inputs-wrapper">
            <div class="form__text-inputs">
                <?php $data = ['errors' => $errors, 'input' => $inputs['email']]; ?>
                <?= include_template('inc/input-text.php', $data) ?>

                <?php $data = ['errors' => $errors, 'input' => $inputs['password']]; ?>
                <?= include_template('inc/input-text.php', $data) ?>
            </div>
            <?php if (!empty($errors)): ?>
            <div class="form__invalid-block" style="margin-bottom: 22px; padding-bottom: 18px;">
                <b class="form__invalid-slogan">Пожалуйста, исправьте следующие ошибки:</b>
                <ul class="form__invalid-list">
                    <?php foreach ($errors as $error): ?>
                    <li class="form__invalid-item"><?= "{$error[1]}. {$error[0]}." ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        <button class="login__submit button button--main" type="submit">Отправить</button>
    </form>
</section>
