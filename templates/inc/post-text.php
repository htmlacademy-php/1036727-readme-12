<?php if (isset($post['details']) && $post['details'] == true): ?>
<p style="margin: 0;"><?= nl2br(esc($post['text_content']), false) ?></p>
<?php else: ?>
<?= nl2br(get_text_content(esc($post['text_content']), $post['id']), false) ?>
<?php endif; ?>
