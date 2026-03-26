<?php if ($selected_team): ?>

<div class="project-description" style="margin-top: 10px;">

    <?= nl2br(htmlspecialchars($selected_team['description'] ?? 'Sin descripción disponible')) ?>

</div>

<?php endif; ?>
