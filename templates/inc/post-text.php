<?php if (isset($post['display_mode']) && $post['display_mode'] === 'details'): ?>
<p style="margin: 0;"><?= nl2br(esc($post['text_content']), false) ?></p>

<?php elseif (isset($post['display_mode']) && $post['display_mode'] === 'feed'): ?>
<?= nl2br(get_text_content(esc($post['text_content']), $post['id']), false) ?>

<?php else: ?>
<?= nl2br(get_text_content(esc($post['text_content']), $post['id'], true), false) ?>
<?php endif; ?>
