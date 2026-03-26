<?php
$user = $_SESSION['user'];
$isAdminOrManager = in_array($user['role'], ['admin', 'manager']);
?>
<!-- Gestión del equipo: show -->
<div class="content-header" style="margin-bottom: 28px;">
    <div>
        <h1 class="project-title" style="font-size: 1.4rem;"><?= htmlspecialchars($team['name']) ?></h1>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px;">Gestión de miembros y configuración del proyecto</p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="<?= BASE_PATH ?>/teams/all" 
           style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px; text-decoration:none; font-size:0.8rem; font-weight:500; color:var(--text-muted); transition:all 0.15s;"
           onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)'"
           onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <?php if ($isAdminOrManager): ?>
        <a href="<?= BASE_PATH ?>/teams/<?= $team['id'] ?>/add-member" class="btn btn-brand"
           style="text-decoration:none; gap: 8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Añadir miembro
        </a>
        <form action="<?= BASE_PATH ?>/teams/<?= $team['id'] ?>/delete" method="POST" style="display:inline;"
              onsubmit="return confirm('¿Eliminar este proyecto? Esta acción no se puede deshacer.');">
            <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
            <button type="submit" class="btn" style="background:#fff1f2; border:1px solid #fecaca; color:#e11d48; gap: 8px; transition: all 0.15s;"
                    onmouseover="this.style.background='#ffe4e6'; this.style.borderColor='#fda4af'"
                    onmouseout="this.style.background='#fff1f2'; this.style.borderColor='#fecaca'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Eliminar proyecto
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 20px 24px; border-bottom: 1px solid var(--divider); background: rgba(5,45,212,0.02); display: flex; align-items: center; justify-content: space-between;">
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text); letter-spacing: 0.02em; text-transform: uppercase;">
            Miembros del equipo <span style="margin-left: 8px; padding: 2px 8px; background: var(--border); border-radius: 6px; font-size: 0.75rem; color: var(--text-muted);"><?= count($miembros) ?></span>
        </div>
    </div>

    <?php if (empty($miembros)): ?>
        <div style="padding: 40px; text-align: center;">
            <p style="font-size:0.875rem; color:var(--text-muted); margin: 0;">Este proyecto no tiene miembros aún.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column;">
            <?php foreach ($miembros as $m): ?>
            <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 24px; border-bottom:1px solid var(--divider); transition: background 0.1s;"
                 onmouseover="this.style.background='rgba(5,45,212,0.02)'"
                 onmouseout="this.style.background='transparent'">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="position: relative;">
                        <img src="https://api.dicebear.com/7.x/thumbs/svg?seed=<?= urlencode($m['username']) ?>&backgroundColor=b6e3f4,c0aede,d1d4f9&radius=12"
                             style="width:40px; height:40px; border-radius:12px; border: 1px solid var(--border);" alt="">
                        <span style="position: absolute; bottom: -2px; right: -2px; width: 12px; height: 12px; border-radius: 50%; background: #4FC59F; border: 2px solid #fff;"></span>
                    </div>
                    <div>
                        <div style="font-size:0.925rem; font-weight:600; color:var(--text);"><?= htmlspecialchars($m['username']) ?></div>
                        <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($m['email']) ?></div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.05em; background: var(--surface); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--divider);">
                        <?= htmlspecialchars($m['role'] ?? 'Miembro') ?>
                    </span>
                    
                    <?php if ($isAdminOrManager): ?>
                    <form action="<?= BASE_PATH ?>/teams/<?= $team['id'] ?>/remove-member/<?= $m['id'] ?>" method="POST"
                          onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars($m['username']) ?> del proyecto?');">
                        <button type="submit" style="background:none; border:1px solid var(--border); border-radius:8px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color:var(--text-muted); cursor:pointer; transition:all 0.15s;"
                                onmouseover="this.style.borderColor='#ef4444'; this.style.color='#ef4444'; this.style.background='#fff1f2'"
                                onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'; this.style.background='transparent'">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>