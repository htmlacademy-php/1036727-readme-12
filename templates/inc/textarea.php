<?php $classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : ''; ?>
<?php $required = $input['required'] == true ? ' <span class="form__input-required">*</span>' : ''; ?>
<div class="adding-post__textarea-wrapper form__textarea-wrapper<?= $classname ?>">
    <label class="adding-post__label form__label" for="<?= $input['name'] ?>"><?= $input['label'] ?><?= $required ?></label>
    <div class="form__input-section">
        <textarea class="adding-post__textarea form__textarea form__input" id="<?= $input['name'] ?>" name="<?= $input['name'] ?>" placeholder="<?= $input['placeholder'] ?>"><?= get_post_value($input['name']) ?></textarea>
        <button class="form__error-button button" type="button">!<span class="visually-hidden">Информация об ошибке</span></button>
        <div class="form__error-text">
            <h3 class="form__error-title"><?= $input['label'] ?></h3>
            <p class="form__error-desc"><?= isset($errors[$input['name']][0]) ? $errors[$input['name']][0] : '' ?></p>
        </div>
    </div>
</div>
