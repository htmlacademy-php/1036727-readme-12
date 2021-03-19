<div class="container">
    <h1 class="page__title page__title--publication"><?= esc($post['title']) ?></h1>
    <section class="post-details">
        <h2 class="visually-hidden">Публикация</h2>
        <div class="post-details__wrapper post-<?= esc($post['class_name']) ?>">
            <div class="post-details__main-block post post--details" style="border-top: none; border-top-right-radius: 0;">
                <?php if ($post['class_name'] === 'quote'): ?>
                <div class="post__main">
                    <?= include_template('inc/post-quote.php', ['post' => $post]) ?>
                </div>

                <?php elseif ($post['class_name'] === 'link'): ?>
                <?= include_template('inc/post-link.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'photo'): ?>
                <?= include_template('inc/post-photo.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'video'): ?>
                <?= include_template('inc/post-video.php', ['post' => $post]) ?>

                <?php elseif ($post['class_name'] === 'text'): ?>
                <div class="post__main" style="border-bottom: 1px solid #dee5fc;">
                    <?= include_template('inc/post-text.php', ['post' => $post]) ?>
                </div>
                <?php endif; ?>
                <div class="post__indicators">
                    <div class="post__buttons">
                        <a class="post__indicator post__indicator--likes<?= get_likes_indicator_class($link, $post['id']) ?> button" href="/like.php?id=<?= $post['id'] ?>" title="Лайк">
                            <svg class="post__indicator-icon" width="20" height="17">
                                <use xlink:href="#icon-heart"></use>
                            </svg>
                            <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                <use xlink:href="#icon-heart-active"></use>
                            </svg>
                            <span><?= get_likes_count($link, $post['id']) ?></span>
                            <span class="visually-hidden">количество лайков</span>
                        </a>
                        <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                            <svg class="post__indicator-icon" width="19" height="17">
                                <use xlink:href="#icon-comment"></use>
                            </svg>
                            <span><?= get_comment_count($link, $post['id']) ?></span>
                            <span class="visually-hidden">количество комментариев</span>
                        </a>
                        <a class="post__indicator post__indicator--repost button" href="#" title="Репост">
                            <svg class="post__indicator-icon" width="19" height="17">
                                <use xlink:href="#icon-repost"></use>
                            </svg>
                            <span><?= get_repost_count($link, $post['id']) ?></span>
                            <span class="visually-hidden">количество репостов</span>
                        </a>
                    </div>
                    <span class="post__view"><?= get_show_count($post['show_count']) ?></span>
                </div>
                <?php if (!empty($hashtags)): ?>
                <ul class="post__tags">
                    <?php foreach ($hashtags as $hashtag): ?>
                    <li><a href="/search.php?q=%23<?= $hashtag['name'] ?>">#<?= esc($hashtag['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <div class="comments">
                    <form class="comments__form form" action="/post.php?id=<?= $post['id'] ?>" method="post">
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
                    <div class="comments__list-wrapper">
                        <ul class="comments__list">
                            <?php foreach ($comments as $comment): ?>
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
                        <?php if (!empty($comments)): ?>
                        <a class="comments__more-link" href="#">
                            <span>Показать все комментарии</span>
                            <sup class="comments__amount"><?= get_comment_count($link, $post['id']) ?></sup>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="post-details__user user">
                <div class="post-details__user-info user__info">
                    <a class="post-details__avatar-link user__avatar-link" href="/profile.php?id=<?= esc($post['author_id']) ?>&tab=posts">
                        <div class="post-details__avatar user__avatar">
                            <?php if (!empty($post['avatar_path'])): ?>
                            <?php $style = 'width: 60px; height: 60px; object-fit: cover;'; ?>
                            <img style="<?= $style ?>" class="post-details__picture user__picture" src="uploads/<?= esc($post['avatar_path']) ?>" alt="Аватар пользователя">
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="post-details__name-wrapper user__name-wrapper">
                        <a class="post-details__name user__name" href="/profile.php?id=<?= esc($post['author_id']) ?>&tab=posts">
                            <span><?= esc($post['author']) ?></span>
                        </a>
                        <time class="post-details__time user__time" datetime="<?= esc($post['dt_reg']) ?>"><?= get_relative_time($post['dt_reg']) ?> на сайте</time>
                    </div>
                </div>
                <div class="post-details__rating user__rating">
                    <p class="post-details__rating-item user__rating-item user__rating-item--subscribers">
                        <span class="post-details__rating-amount user__rating-amount"><?= get_subscriber_count($link, $post['author_id'], true) ?></span>
                        <span class="post-details__rating-text user__rating-text"><?= get_subscriber_count($link, $post['author_id']) ?></span>
                    </p>
                    <p class="post-details__rating-item user__rating-item user__rating-item--publications">
                        <span class="post-details__rating-amount user__rating-amount"><?= get_publication_count($link, $post['author_id'], true) ?></span>
                        <span class="post-details__rating-text user__rating-text"><?= get_publication_count($link, $post['author_id']) ?></span>
                    </p>
                </div>
                <div class="post-details__user-buttons user__buttons">
                    <?php if ($post['author_id'] !== $_SESSION['user']['id']): ?>
                    <?php $button_text = get_subscription_status($link, $post['author_id']) ? 'Отписаться' : 'Подписаться'; ?>
                    <a class="user__button user__button--subscription button button--main" href="/subscription.php?id=<?= esc($post['author_id']) ?>"><?= $button_text ?></a>
                    <?php if (get_subscription_status($link, $post['author_id'])): ?>
                    <a class="user__button user__button--writing button button--green" href="/messages.php?contact=<?= esc($post['author_id']) ?>">Сообщение</a>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
