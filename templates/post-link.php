<?php if ($full_version): ?>
<div class="post-link__wrapper" style="border-top: none;">
    <a class="post-link__external" href="http://<?= esc($post['link']) ?>" title="Перейти по ссылке">
        <div class="post-link__icon-wrapper">
            <img src="img/logo-vita.jpg" alt="Иконка">
        </div>
        <div class="post-link__info">
            <h3><?= esc($post['title']) ?></h3>
            <p>Семейная стоматология в Адлере</p>
            <span><?= esc($post['link']) ?></span>
        </div>
        <svg class="post-link__arrow" width="11" height="16">
            <use xlink:href="#icon-arrow-right-ad"></use>
        </svg>
    </a>
</div>
<?php else: ?>
<div class="post-link__wrapper">
    <a class="post-link__external" href="http://<?= esc($post['link']) ?>" title="Перейти по ссылке">
        <div class="post-link__info-wrapper">
            <div class="post-link__icon-wrapper">
                <img src="https://www.google.com/s2/favicons?domain=vitadental.ru" alt="Иконка">
            </div>
            <div class="post-link__info">
                <h3><?= esc($post['title']) ?></h3>
            </div>
        </div>
        <span><?= esc($post['link']) ?></span>
    </a>
</div>
<?php endif; ?>
