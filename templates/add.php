<div class="page__main-section">
    <div class="container">
        <h1 class="page__title page__title--adding-post">Добавить публикацию</h1>
    </div>
    <div class="adding-post container">
        <div class="adding-post__tabs-wrapper tabs">
            <div class="adding-post__tabs filters">
                <ul class="adding-post__tabs-list filters__list tabs__list">

                    <?php foreach ($content_types as $ctype): ?>
                        <li class="adding-post__tabs-item filters__item">
                            <?php $classname = isset($_GET['tab']) && $_GET['tab'] === $ctype['class_name'] ? 'filters__button--active tabs__item--active' : ''; ?>
                            <?php $url = '/add.php?tab=' . esc($ctype['class_name']); ?>
                            <a class="adding-post__tabs-link filters__button filters__button--<?= esc($ctype['class_name']) ?> tabs__item button <?= $classname ?>" href="<?= $url ?>">
                                <svg class="filters__icon" width="<?= esc($ctype['icon_width']) ?>" height="<?= esc($ctype['icon_height']) ?>">
                                    <use xlink:href="#icon-filter-<?= esc($ctype['class_name']) ?>"></use>
                                </svg>
                                <span><?= esc($ctype['type_name']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </div>

            <div class="adding-post__tab-content">

                <?php foreach ($tabs_content as $ctype => $input_keys): ?>
                    <?php $classname = isset($_GET['tab']) && $_GET['tab'] === $ctype ? ' tabs__content--active' : ''; ?>
                    <section class="adding-post__<?= $ctype ?> tabs__content<?= $classname ?>">
                        <h2 class="visually-hidden">Форма добавления <?= getTabContentModifier($ctype) ?></h2>

                        <?php $inputs_exists = !array_diff_key(array_flip($input_keys), $inputs); ?>

                        <?php if ($inputs_exists): ?>
                            <form class="adding-post__form form" action="/add.php?tab=<?= $ctype ?>" method="post" enctype="multipart/form-data">
                                <div class="form__text-inputs-wrapper">
                                    <div class="form__text-inputs">

                                        <?php
                                        foreach ($input_keys as $key):
                                            $data = ['errors' => $errors, 'input' => $inputs[$key]];

                                            if (in_array($key, ['post-text', 'cite-text'])):
                                                echo includeTemplate('_partials/textarea.php', $data);
                                            else:
                                                echo includeTemplate('_partials/input-text.php', $data);
                                            endif;

                                        endforeach;
                                        ?>

                                        <input type="hidden" name="content-type" value="<?= $ctype ?>">
                                    </div>

                                    <?php
                                    if (!empty($errors)):

                                        $data = ['errors' => $errors];
                                        echo includeTemplate('_partials/invalid-block.php', $data);

                                    endif;
                                    ?>

                                </div>

                                <?php if ($ctype === 'photo' && isset($inputs['file-photo'])): ?>
                                    <div class="adding-post__input-file-container form__input-container form__input-container--file">
                                        <?= includeTemplate('_partials/input-file.php', ['input' => $inputs['file-photo']]) ?>
                                        <div class="adding-post__file adding-post__file--photo form__file dropzone-previews"></div>
                                    </div>
                                <?php endif; ?>

                                <div class="adding-post__buttons">
                                    <button class="adding-post__submit button button--main" type="submit">Опубликовать</button>
                                    <a class="adding-post__close" href="<?= getAddingPostCloseUrl() ?>">Закрыть</a>
                                </div>
                            </form>
                        <?php endif; ?>

                    </section>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
