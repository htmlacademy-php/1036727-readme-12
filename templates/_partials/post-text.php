<?php if (isset($post['display_mode']) && $post['display_mode'] === 'details'): ?>
    <p style="margin: 50px 0;"><?= nl2br(esc($post['text_content']), false) ?></p>

<?php elseif (isset($post['display_mode']) && $post['display_mode'] === 'feed'): ?>
    <?php $style = isset($post['style']) ? $post['style'] : ''; ?>
    <?= nl2br(cropTextContent(esc($post['text_content']), $post['id'], $style), false) ?>

<?php else: ?>
    <?php $style = 'margin-top: 0;'; ?>
    <?= nl2br(cropTextContent(esc($post['text_content']), $post['id'], $style), false) ?>
<?php endif; ?>
