<h1 class="visually-hidden">Профиль</h1>
<div class="profile profile--default">
    <div class="profile__user-wrapper">
        <div class="profile__user user container">
            <div class="profile__user-info user__info">
                <div class="profile__avatar user__avatar">
                    <?php if (!empty($user['avatar_path'])): ?>
                    <?php $style = 'width: 100px; height: 100px; object-fit: cover;'; ?>
                    <img style="<?= $style ?>" class="profile__picture user__picture" src="uploads/<?= esc($user['avatar_path']) ?>" alt="Аватар пользователя">
                    <?php endif; ?>
                </div>
                <div class="profile__name-wrapper user__name-wrapper">
                    <span class="profile__name user__name"><?= esc($user['login']) ?></span>
                    <time class="profile__user-time user__time" datetime="<?= esc($user['dt_add']) ?>"><?= get_relative_time($user['dt_add']) ?> на сайте</time>
                </div>
            </div>
            <div class="profile__rating user__rating">
                <p class="profile__rating-item user__rating-item user__rating-item--publications">
                    <span class="user__rating-amount"><?= get_publication_count($link, $user['id'], true) ?></span>
                    <span class="profile__rating-text user__rating-text"><?= get_publication_count($link, $user['id']) ?></span>
                </p>
                <p class="profile__rating-item user__rating-item user__rating-item--subscribers">
                    <span class="user__rating-amount"><?= get_subscriber_count($link, $user['id'], true) ?></span>
                    <span class="profile__rating-text user__rating-text"><?= get_subscriber_count($link, $user['id']) ?></span>
                </p>
            </div>
            <div class="profile__user-buttons user__buttons">
                <?php if ($user['id'] !== $_SESSION['user']['id']): ?>
                <?php $button_text = get_subscription_status($link, $user['id']) ? 'Отписаться' : 'Подписаться'; ?>
                <a class="profile__user-button user__button user__button--subscription button button--main" href="/subscription.php?id=<?= esc($user['id']) ?>"><?= $button_text ?></a>
                <?php if (get_subscription_status($link, $user['id'])): ?>
                <a class="profile__user-button user__button user__button--writing button button--green" href="/messages.php?contact=<?= esc($user['id']) ?>">Сообщение</a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="profile__tabs-wrapper tabs">
        <div class="container">
            <div class="profile__tabs filters">
                <b class="profile__tabs-caption filters__caption">Показать:</b>
                <ul class="profile__tabs-list filters__list tabs__list">
                    <li class="profile__tabs-item filters__item">
                        <?php $user_id = esc($user['id']); ?>
                        <?php $classname = isset($_GET['tab']) && $_GET['tab'] === 'posts' ? ' filters__button--active tabs__item--active' : ''; ?>
                        <?php $href = isset($_GET['tab']) && $_GET['tab'] === 'posts' ? '' : " href=\"/profile.php?id={$user_id}&tab=posts\""; ?>
                        <a class="profile__tabs-link filters__button tabs__item button<?= $classname ?>"<?= $href ?>>Посты</a>
                    </li>
                    <li class="profile__tabs-item filters__item">
                        <?php $classname = isset($_GET['tab']) && $_GET['tab'] === 'likes' ? ' filters__button--active tabs__item--active' : ''; ?>
                        <?php $href = isset($_GET['tab']) && $_GET['tab'] === 'likes' ? '' : " href=\"/profile.php?id={$user_id}&tab=likes\""; ?>
                        <a class="profile__tabs-link filters__button tabs__item button<?= $classname ?>"<?= $href ?>>Лайки</a>
                    </li>
                    <li class="profile__tabs-item filters__item">
                        <?php $classname = isset($_GET['tab']) && $_GET['tab'] === 'subscriptions' ? ' filters__button--active tabs__item--active' : ''; ?>
                        <?php $href = isset($_GET['tab']) && $_GET['tab'] === 'subscriptions' ? '' : " href=\"/profile.php?id={$user_id}&tab=subscriptions\""; ?>
                        <a class="profile__tabs-link filters__button tabs__item button<?= $classname ?>"<?= $href ?>>Подписки</a>
                    </li>
                </ul>
            </div>
            <div class="profile__tab-content">
                <section class="profile__posts tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'posts'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Публикации</h2>
                    <?php foreach ($posts as $post): ?>
                    <article id="article-<?= esc($post['id']) ?>" class="profile__post post post-<?= esc($post['class_name']) ?>">
                        <header class="post__header">
                            <?php $style = $post['class_name'] === 'text' ? 'padding: 29px 40px 26px;' : ''; ?>
                            <h2 style="<?= $style ?>"><a href="/post.php?id=<?= esc($post['id']) ?>"><?= esc($post['title']) ?></a></h2>
                        </header>
                        <div style="min-height: 110px;" class="post__main">
                            <?php $post['display_mode'] = 'feed'; ?>
                            <?php if ($post['class_name'] === 'quote'): ?>
                            <?= include_template('inc/post-quote.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'link'): ?>
                            <?= include_template('inc/post-link.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'photo'): ?>
                            <?= include_template('inc/post-photo.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'video'): ?>
                            <?= include_template('inc/post-video.php', ['post' => $post]) ?>

                            <?php elseif ($post['class_name'] === 'text'): ?>
                            <?php $post['style'] = 'margin-top: 0;'; ?>
                            <?= include_template('inc/post-text.php', ['post' => $post]) ?>
                            <?php endif; ?>
                        </div>
                        <footer class="post__footer">
                            <div class="post__indicators">
                                <div class="post__buttons">
                                    <a class="post__indicator post__indicator--likes<?= get_likes_indicator_class($link, $post['id']) ?> button" href="/like.php?id=<?= esc($post['id']) ?>" title="Лайк">
                                        <svg class="post__indicator-icon" width="20" height="17">
                                            <use xlink:href="#icon-heart"></use>
                                        </svg>
                                        <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                            <use xlink:href="#icon-heart-active"></use>
                                        </svg>
                                        <span><?= get_likes_count($link, $post['id']) ?></span>
                                        <span class="visually-hidden">количество лайков</span>
                                    </a>
                                    <a class="post__indicator post__indicator--repost button" href="#" title="Репост">
                                        <svg class="post__indicator-icon" width="19" height="17">
                                            <use xlink:href="#icon-repost"></use>
                                        </svg>
                                        <span><?= get_repost_count($link, $post['id']) ?></span>
                                        <span class="visually-hidden">количество репостов</span>
                                    </a>
                                </div>
                                <time class="post__time" datetime="<?= esc($post['dt_add']) ?>"><?= get_relative_time($post['dt_add']) ?> назад</time>
                            </div>
                            <?php if ($hashtags = get_post_hashtags($link, $post['id'])): ?>
                            <ul class="post__tags">
                                <?php foreach ($hashtags as $hashtag): ?>
                                <li><a href="/search.php?q=%23<?= esc($hashtag['name']) ?>">#<?= esc($hashtag['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </footer>
                        <?php if (!empty($post['COUNT(c.id)'])): ?>
                        <?php if (isset($_GET['comments']) && $_GET['comments'] === $post['id']): ?>
                        <div style="padding-bottom: 11px;" class="comments__list-wrapper">
                            <ul class="comments__list">
                                <?php foreach (get_post_comments($link, $post['id']) as $comment): ?>
                                <li class="comments__item user">
                                    <a class="user__avatar-link" href="/profile.php?id=<?= esc($comment['author_id']) ?>&tab=posts">
                                        <div class="comments__avatar">
                                            <?php if (!empty($comment['avatar_path'])): ?>
                                            <?php $style = 'width: 40px; height: 40px; object-fit: cover;'; ?>
                                            <img style="<?= $style ?>" class="comments__picture" src="uploads/<?= esc($comment['avatar_path']) ?>" width="40" height="40" alt="Аватар пользователя">
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                    <div class="comments__info">
                                        <div class="comments__name-wrapper">
                                            <a class="comments__user-name" href="/profile.php?id=<?= esc($comment['author_id']) ?>&tab=posts">
                                                <span><?= esc($comment['login']) ?></span>
                                            </a>
                                            <time class="comments__time" datetime="<?= esc($comment['dt_add']) ?>"><?= get_relative_time($comment['dt_add']) ?> назад</time>
                                        </div>
                                        <p class="comments__text"><?= esc($comment['content']) ?></p>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <!-- <a class="comments__more-link" href="#">
                                <span>Показать все комментарии</span>
                                <sup class="comments__amount">45</sup>
                            </a> -->
                        </div>
                        <?php $url = "/profile.php?id={$user['id']}&tab=posts&comments={$post['id']}#article-{$post['id']}"; ?>
                        <form class="comments__form form" action="<?= esc($url) ?>" method="post">
                            <div class="comments__my-avatar">
                                <?php if (!empty($_SESSION['user']['avatar_path'])): ?>
                                <?php $style = 'width: 40px; height: 40px; object-fit: cover;'; ?>
                                <img style="<?= $style ?>" class="comments__picture" src="uploads/<?= esc($_SESSION['user']['avatar_path']) ?>" width="40" height="40" alt="Аватар пользователя">
                                <?php endif; ?>
                            </div>
                            <?php $input = $inputs['comment'] ?>
                            <?php $classname = isset($errors[$input['name']][0]) ? ' form__input-section--error' : ''; ?>
                            <div class="form__input-section<?= $classname ?>">
                                <textarea class="comments__textarea form__textarea form__input" name="<?= esc($input['name']) ?>" placeholder="<?= esc($input['placeholder']) ?>"><?= esc(get_post_value($input['name'])) ?></textarea>
                                <label class="visually-hidden"><?= esc($input['label']) ?></label>
                                <button class="form__error-button button" type="button">!</button>
                                <div class="form__error-text">
                                    <h3 class="form__error-title"><?= esc($input['label']) ?></h3>
                                    <p class="form__error-desc"><?= $errors[$input['name']][0] ?? '' ?></p>
                                </div>
                            </div>
                            <input type="hidden" name="post-id" value="<?= esc($post['id']) ?>">
                            <button class="comments__submit button button--green" type="submit">Отправить</button>
                        </form>
                        <?php else: ?>
                        <div class="comments">
                            <?php $style = !$hashtags ? 'margin-top: 4px;' : ''; ?>
                            <?php $url = "/profile.php?id={$user['id']}&tab=posts&comments={$post['id']}#article-{$post['id']}"; ?>
                            <a style="<?= $style ?>" class="comments__button button" href="<?= esc($url) ?>">Показать комментарии</a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </section>

                <section class="profile__likes tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'likes'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Лайки</h2>
                    <ul class="profile__likes-list">
                        <li class="post-mini post-mini--photo post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <div class="post-mini__action">
                                        <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                                        <time class="post-mini__time user__additional" datetime="2014-03-20T20:20">5 минут назад</time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-mini__preview">
                                <a class="post-mini__link" href="#" title="Перейти на публикацию">
                                    <div class="post-mini__image-wrapper">
                                        <img class="post-mini__image" src="img/rock-small.png" width="109" height="109" alt="Превью публикации">
                                    </div>
                                    <span class="visually-hidden">Фото</span>
                                </a>
                            </div>
                        </li>
                        <li class="post-mini post-mini--text post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <div class="post-mini__action">
                                        <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                                        <time class="post-mini__time user__additional" datetime="2014-03-20T20:05">15 минут назад</time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-mini__preview">
                                <a class="post-mini__link" href="#" title="Перейти на публикацию">
                                    <span class="visually-hidden">Текст</span>
                                    <svg class="post-mini__preview-icon" width="20" height="21">
                                        <use xlink:href="#icon-filter-text"></use>
                                    </svg>
                                </a>
                            </div>
                        </li>
                        <li class="post-mini post-mini--video post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <div class="post-mini__action">
                                        <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                                        <time class="post-mini__time user__additional" datetime="2014-03-20T18:20">2 часа назад</time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-mini__preview">
                                <a class="post-mini__link" href="#" title="Перейти на публикацию">
                                    <div class="post-mini__image-wrapper">
                                        <img class="post-mini__image" src="img/coast-small.png" width="109" height="109" alt="Превью публикации">
                                        <span class="post-mini__play-big">
                                            <svg class="post-mini__play-big-icon" width="12" height="13">
                                                <use xlink:href="#icon-video-play-big"></use>
                                            </svg>
                                        </span>
                                    </div>
                                    <span class="visually-hidden">Видео</span>
                                </a>
                            </div>
                        </li>
                        <li class="post-mini post-mini--quote post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <div class="post-mini__action">
                                        <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                                        <time class="post-mini__time user__additional" datetime="2014-03-15T20:05">5 дней назад</time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-mini__preview">
                                <a class="post-mini__link" href="#" title="Перейти на публикацию">
                                    <span class="visually-hidden">Цитата</span>
                                    <svg class="post-mini__preview-icon" width="21" height="20">
                                        <use xlink:href="#icon-filter-quote"></use>
                                    </svg>
                                </a>
                            </div>
                        </li>
                        <li class="post-mini post-mini--link post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <div class="post-mini__action">
                                        <span class="post-mini__activity user__additional">Лайкнул вашу публикацию</span>
                                        <time class="post-mini__time user__additional" datetime="2014-03-20T20:05">в далеком 2007-ом</time>
                                    </div>
                                </div>
                            </div>
                            <div class="post-mini__preview">
                                <a class="post-mini__link" href="#" title="Перейти на публикацию">
                                    <span class="visually-hidden">Ссылка</span>
                                    <svg class="post-mini__preview-icon" width="21" height="18">
                                        <use xlink:href="#icon-filter-link"></use>
                                    </svg>
                                </a>
                            </div>
                        </li>
                    </ul>
                </section>

                <section class="profile__subscriptions tabs__content<?php if (isset($_GET['tab']) && $_GET['tab'] === 'subscriptions'): ?> tabs__content--active<?php endif; ?>">
                    <h2 class="visually-hidden">Подписки</h2>
                    <ul class="profile__subscriptions-list">
                        <li class="post-mini post-mini--photo post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <time class="post-mini__time user__additional" datetime="2014-03-20T20:20">5 лет на сайте</time>
                                </div>
                            </div>
                            <div class="post-mini__rating user__rating">
                                <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                                    <span class="post-mini__rating-amount user__rating-amount">556</span>
                                    <span class="post-mini__rating-text user__rating-text">публикаций</span>
                                </p>
                                <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                                    <span class="post-mini__rating-amount user__rating-amount">1856</span>
                                    <span class="post-mini__rating-text user__rating-text">подписчиков</span>
                                </p>
                            </div>
                            <div class="post-mini__user-buttons user__buttons">
                                <button class="post-mini__user-button user__button user__button--subscription button button--main" type="button">Подписаться</button>
                            </div>
                        </li>
                        <li class="post-mini post-mini--photo post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <time class="post-mini__time user__additional" datetime="2014-03-20T20:20">5 лет на сайте</time>
                                </div>
                            </div>
                            <div class="post-mini__rating user__rating">
                                <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                                    <span class="post-mini__rating-amount user__rating-amount">556</span>
                                    <span class="post-mini__rating-text user__rating-text">публикаций</span>
                                </p>
                                <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                                    <span class="post-mini__rating-amount user__rating-amount">1856</span>
                                    <span class="post-mini__rating-text user__rating-text">подписчиков</span>
                                </p>
                            </div>
                            <div class="post-mini__user-buttons user__buttons">
                                <button class="post-mini__user-button user__button user__button--subscription button button--quartz" type="button">Отписаться</button>
                            </div>
                        </li>
                        <li class="post-mini post-mini--photo post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <time class="post-mini__time user__additional" datetime="2014-03-20T20:20">5 лет на сайте</time>
                                </div>
                            </div>
                            <div class="post-mini__rating user__rating">
                                <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                                    <span class="post-mini__rating-amount user__rating-amount">556</span>
                                    <span class="post-mini__rating-text user__rating-text">публикаций</span>
                                </p>
                                <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                                    <span class="post-mini__rating-amount user__rating-amount">1856</span>
                                    <span class="post-mini__rating-text user__rating-text">подписчиков</span>
                                </p>
                            </div>
                            <div class="post-mini__user-buttons user__buttons">
                                <button class="post-mini__user-button user__button user__button--subscription button button--main" type="button">Подписаться</button>
                            </div>
                        </li>
                        <li class="post-mini post-mini--photo post user">
                            <div class="post-mini__user-info user__info">
                                <div class="post-mini__avatar user__avatar">
                                    <a class="user__avatar-link" href="#">
                                        <img class="post-mini__picture user__picture" src="img/userpic-petro.jpg" alt="Аватар пользователя">
                                    </a>
                                </div>
                                <div class="post-mini__name-wrapper user__name-wrapper">
                                    <a class="post-mini__name user__name" href="#">
                                        <span>Петр Демин</span>
                                    </a>
                                    <time class="post-mini__time user__additional" datetime="2014-03-20T20:20">5 лет на сайте</time>
                                </div>
                            </div>
                            <div class="post-mini__rating user__rating">
                                <p class="post-mini__rating-item user__rating-item user__rating-item--publications">
                                    <span class="post-mini__rating-amount user__rating-amount">556</span>
                                    <span class="post-mini__rating-text user__rating-text">публикаций</span>
                                </p>
                                <p class="post-mini__rating-item user__rating-item user__rating-item--subscribers">
                                    <span class="post-mini__rating-amount user__rating-amount">1856</span>
                                    <span class="post-mini__rating-text user__rating-text">подписчиков</span>
                                </p>
                            </div>
                            <div class="post-mini__user-buttons user__buttons">
                                <button class="post-mini__user-button user__button user__button--subscription button button--main" type="button">Подписаться</button>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</div>
