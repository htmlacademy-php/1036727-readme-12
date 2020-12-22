<?php $classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : ''; ?>
<?php $required = $input['required'] == true ? ' <span class="form__input-required">*</span>' : ''; ?>
<div class="adding-post__input-wrapper form__input-wrapper<?= $classname ?>">
    <label class="adding-post__label form__label" for="<?= esc($input['name']) ?>"><?= esc($input['label']) ?><?= $required ?></label>
    <div class="form__input-section">
        <input class="adding-post__input form__input" id="<?= esc($input['name']) ?>" type="text" name="<?= esc($input['name']) ?>" value="<?= get_post_value($input['name']) ?>" placeholder="<?= esc($input['placeholder']) ?>">
        <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
        <div class="form__error-text">
            <h3 class="form__error-title"><?= esc($input['label']) ?></h3>
            <p class="form__error-desc"><?= isset($errors[$input['name']][0]) ? $errors[$input['name']][0] : '' ?></p>
        </div>
    </div>
</div>
