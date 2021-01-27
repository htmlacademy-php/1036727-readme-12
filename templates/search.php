<?php if (!empty($posts)): ?>
<h1 class="visually-hidden">Страница результатов поиска</h1>
<section class="search">
    <h2 class="visually-hidden">Результаты поиска</h2>
    <div class="search__query-wrapper">
        <div class="search__query container">
            <span>Вы искали:</span>
            <span class="search__query-text"><?= esc(trim($_GET['q'] ?? '')) ?></span>
        </div>
    </div>
    <div class="search__results-wrapper">
        <div class="container">
            <div class="search__content">
                <?php foreach ($posts as $post): ?>
                <article class="search__post post post-<?= esc($post['class_name']) ?>">
                    <header class="post__header post__author">
                        <a class="post__author-link" href="#" title="Автор">
                            <div class="post__avatar-wrapper">
                                <?php if (!empty($post['avatar_path'])): ?>
                                <?php $style = 'width: 60px; height: 60px; object-fit: cover;'; ?>
                                <img style="<?= $style ?>" class="post__author-avatar" src="uploads/<?= esc($post['avatar_path']) ?>" alt="Аватар пользователя" width="60" height="60">
                                <?php endif; ?>
                            </div>
                            <div class="post__info">
                                <b class="post__author-name"><?= esc($post['author']) ?></b>
                                <span class="post__time"><?= get_post_time($post['dt_add']) ?></span>
                            </div>
                        </a>
                    </header>
                    <div style="min-height: 141px;" class="post__main">
                        <?php if (in_array($post['class_name'], ['photo', 'text'])): ?>
                        <h2><a href="/post.php?id=<?= $post['id'] ?>"><?= esc($post['title']) ?></a></h2>
                        <?php endif; ?>

                        <?php $post['display_mode'] = 'feed'; ?>
                        <?php if ($post['class_name'] == 'quote'): ?>
                        <?= include_template('inc/post-quote.php', ['post' => $post]) ?>

                        <?php elseif ($post['class_name'] == 'link'): ?>
                        <?= include_template('inc/post-link.php', ['post' => $post]) ?>

                        <?php elseif ($post['class_name'] == 'photo'): ?>
                        <?= include_template('inc/post-photo.php', ['post' => $post]) ?>

                        <?php elseif ($post['class_name'] == 'video'): ?>
                        <?= include_template('inc/post-video.php', ['post' => $post]) ?>

                        <?php elseif ($post['class_name'] == 'text'): ?>
                        <?= include_template('inc/post-text.php', ['post' => $post]) ?>
                        <?php endif; ?>
                    </div>
                    <footer class="post__footer post__indicators">
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
                        </div>
                    </footer>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<h1 class="visually-hidden">Страница результатов поиска (нет результатов)</h1>
    <section class="search">
        <h2 class="visually-hidden">Результаты поиска</h2>
        <div class="search__query-wrapper">
            <div class="search__query container">
                <span>Вы искали:</span>
                <span class="search__query-text"><?= esc(trim($_GET['q'] ?? '')) ?></span>
            </div>
        </div>
        <div class="search__results-wrapper">
            <div class="search__no-results container">
                <p class="search__no-results-info">К сожалению, ничего не найдено.</p>
                <p class="search__no-results-desc">Попробуйте изменить поисковый запрос или просто зайти в раздел &laquo;Популярное&raquo;, там живет самый крутой контент.</p>
            <div class="search__links">
                <a class="search__popular-link button button--main" href="/popular.php">Популярное</a>
                <?php $ref = $_SERVER['HTTP_REFERER'] ?? '/feed.php'; ?>
                <a class="search__back-link" href="<?= $ref ?>">Вернуться назад</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
