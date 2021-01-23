<div class="container">
    <h1 class="page__title page__title--feed">Моя лента</h1>
</div>
<div class="page__main-wrapper container">
    <section class="feed">
        <h2 class="visually-hidden">Лента</h2>
        <div class="feed__main-wrapper">
            <div class="feed__wrapper">
                <article class="feed__post post post-photo">
                    <header class="post__header post__author">
                        <a class="post__author-link" href="#" title="Автор">
                            <div class="post__avatar-wrapper">
                                <img class="post__author-avatar" src="img/userpic-elvira.jpg" alt="Аватар пользователя" width="60" height="60">
                            </div>
                            <div class="post__info">
                                <b class="post__author-name">Эльвира Хайпулинова</b>
                                <span class="post__time">15 минут назад</span>
                            </div>
                        </a>
                    </header>
                    <div class="post__main">
                        <h2><a href="#">Наконец, обработала фотки!</a></h2>
                        <div class="post-photo__image-wrapper">
                            <img src="img/rock.jpg" alt="Фото от пользователя" width="760" height="396">
                        </div>
                    </div>
                    <footer class="post__footer post__indicators">
                        <div class="post__buttons">
                            <a class="post__indicator post__indicator--likes button" href="#" title="Лайк">
                                <svg class="post__indicator-icon" width="20" height="17">
                                    <use xlink:href="#icon-heart"></use>
                                </svg>
                                <svg class="post__indicator-icon post__indicator-icon--like-active" width="20" height="17">
                                    <use xlink:href="#icon-heart-active"></use>
                                </svg>
                                <span>250</span>
                                <span class="visually-hidden">количество лайков</span>
                            </a>
                            <a class="post__indicator post__indicator--comments button" href="#" title="Комментарии">
                                <svg class="post__indicator-icon" width="19" height="17">
                                    <use xlink:href="#icon-comment"></use>
                                </svg>
                                <span>25</span>
                                <span class="visually-hidden">количество комментариев</span>
                            </a>
                            <a class="post__indicator post__indicator--repost button" href="#" title="Репост">
                                <svg class="post__indicator-icon" width="19" height="17">
                                    <use xlink:href="#icon-repost"></use>
                                </svg>
                                <span>5</span>
                                <span class="visually-hidden">количество репостов</span>
                            </a>
                        </div>
                    </footer>
                </article>
            </div>
        </div>
        <ul class="feed__filters filters">
            <li class="feed__filters-item filters__item">
                <?php $classname = !isset($_GET['filter']) ? ' filters__button--active' : ''; ?>
                <a class="filters__button<?= $classname ?>" href="/feed.php">
                    <span>Все</span>
                </a>
            </li>
            <?php foreach ($content_types as $type): ?>
            <li class="feed__filters-item filters__item">
                <?php $classname = isset($_GET['filter']) && $_GET['filter'] === $type['class_name'] ? ' filters__button--active' : ''; ?>
                <a class="filters__button filters__button--<?= esc($type['class_name']) ?> button<?= $classname ?>" href="/feed.php?filter=<?= esc($type['class_name']) ?>">
                    <span class="visually-hidden"><?= esc($type['type_name']) ?></span>
                    <svg class="filters__icon" width="<?= esc($type['icon_width']) ?>" height="<?= esc($type['icon_height']) ?>">
                        <use xlink:href="#icon-filter-<?= esc($type['class_name']) ?>"></use>
                    </svg>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <aside class="promo">
        <article class="promo__block promo__block--barbershop">
            <h2 class="visually-hidden">Рекламный блок</h2>
            <p class="promo__text">Все еще сидишь на окладе в офисе? Открой свой барбершоп по нашей франшизе!</p>
            <a class="promo__link" href="#">Подробнее</a>
        </article>
        <article class="promo__block promo__block--technomart">
            <h2 class="visually-hidden">Рекламный блок</h2>
            <p class="promo__text">Товары будущего уже сегодня в онлайн-сторе Техномарт!</p>
            <a class="promo__link" href="#">Перейти в магазин</a>
        </article>
        <article class="promo__block">
            <h2 class="visually-hidden">Рекламный блок</h2>
            <p class="promo__text">Здесь<br>могла быть<br>ваша реклама</p>
            <a class="promo__link" href="#">Разместить</a>
        </article>
    </aside>
</div>
