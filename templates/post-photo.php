<?php if ($full_version): ?>
<div class="post-photo__image-wrapper">
    <img src="img/<?= esc($post['image_path']) ?>" alt="Фото от пользователя" width="760" height="507">
</div>
<?php else: ?>
<div class="post-photo__image-wrapper">
    <img src="img/<?= esc($post['image_path']) ?>" alt="Фото от пользователя" width="360" height="240">
</div>
<?php endif; ?>
