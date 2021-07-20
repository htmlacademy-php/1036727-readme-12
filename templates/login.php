<div class="container">
    <h1 class="page__title page__title--login">Вход</h1>
</div>

<section class="login container">
    <h2 class="visually-hidden">Форма авторизации</h2>

    <?php
    $keys = ['email', 'passwd'];
    $inputs_exists = !array_diff_key(array_flip($keys), $inputs);
    ?>

    <?php if ($inputs_exists): ?>
        <form style="padding-bottom: 2px;" class="login__form form" action="/login.php" method="post">
            <div class="form__text-inputs-wrapper">

                <div class="form__text-inputs">
                    <?php $data = ['errors' => $errors, 'input' => $inputs['email']]; ?>
                    <?= include_template('_partials/input-text.php', $data) ?>

                    <?php $data = ['errors' => $errors, 'input' => $inputs['passwd']]; ?>
                    <?= include_template('_partials/input-text.php', $data) ?>
                </div>

                <?php if (!empty($errors)): ?>
                    <?php $style = 'margin-bottom: 22px; '; ?>
                    <?php $data = ['errors' => $errors, 'style' => $style]; ?>
                    <?= include_template('_partials/invalid-block.php', $data) ?>
                <?php endif; ?>

            </div>
            <button class="login__submit button button--main" type="submit">Отправить</button>
        </form>
    <?php endif; ?>

</section>
