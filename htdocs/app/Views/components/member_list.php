<?php if ($selected_team): ?>

<div class="card">
    <h3 class="card-title">Miembros (<?= count($miembros) ?>)</h3>

    <?php if (empty($miembros)): ?>

        <p style="color:var(--text-muted); font-size: 0.9rem;">
            No hay miembros aún.
        </p>

    <?php else: ?>

        <div class="member-list">

            <?php foreach ($miembros as $m): ?>
                <div class="member-item">

                    <div class="member-info">
                        <div class="user-avatar" style="width:28px; height:28px; font-size:0.75rem;">
                            <?= strtoupper(substr($m['username'], 0, 1)) ?>
                        </div>

                        <div class="member-name">
                            <?= htmlspecialchars($m['username']) ?>

                            <?php $statusClass = str_replace(' ', '-', $m['status']); ?>
                            <span class="status-dot user-status-<?= $statusClass ?>" 
                                  title="<?= $m['status'] ?>"></span>
                        </div>
                    </div>

                    <?php if ($m['role'] === 'admin'): ?>
                        <span class="role-badge role-admin">Jefe</span>
                    <?php else: ?>
                        <span class="role-badge">Trabajador</span>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php endif; ?>
