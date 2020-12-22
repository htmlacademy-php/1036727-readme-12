<?php if (isset($post['details']) && $post['details'] == true): ?>
<div class="post-video__block">
    <div class="post-video__preview">
        <?=embed_youtube_video(esc($post['video_path'])); ?>
        <!-- <img src="img/coast.jpg" alt="Превью к видео" width="760" height="396"> -->
    </div>
    <!-- <div class="post-video__control">
        <button class="post-video__play post-video__play--paused button button--video" type="button"><span class="visually-hidden">Запустить видео</span></button>
        <div class="post-video__scale-wrapper">
            <div class="post-video__scale">
                <div class="post-video__bar">
                    <div class="post-video__toggle"></div>
                </div>
            </div>
        </div>
        <button class="post-video__fullscreen post-video__fullscreen--inactive button button--video" type="button"><span class="visually-hidden">Полноэкранный режим</span></button>
    </div>
    <button class="post-video__play-big button" type="button">
        <svg class="post-video__play-big-icon" width="27" height="28">
            <use xlink:href="#icon-video-play-big"></use>
        </svg>
        <span class="visually-hidden">Запустить проигрыватель</span>
    </button> -->
</div>
<?php else: ?>
<div class="post-video__block">
    <div class="post-video__preview">
        <?=embed_youtube_cover(esc($post['video_path'])); ?>
        <!-- <img src="img/coast-medium.jpg" alt="Превью к видео" width="360" height="188"> -->
    </div>
    <a href="/post.php?id=<?= $post['id'] ?>" class="post-video__play-big button">
        <svg class="post-video__play-big-icon" width="14" height="14">
            <use xlink:href="#icon-video-play-big"></use>
        </svg>
        <span class="visually-hidden">Запустить проигрыватель</span>
    </a>
</div>
<?php endif; ?>
