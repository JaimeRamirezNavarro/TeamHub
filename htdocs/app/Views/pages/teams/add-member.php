<?php ob_start(); ?>
<!-- add-member view -->
<div style="max-width: 560px; margin: 0 auto;">

    <div class="content-header" style="margin-bottom: 28px;">
        <div>
            <h1 class="project-title" style="font-size: 1.4rem;">Añadir miembros</h1>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px;">
                Proyecto: <strong style="color: var(--text);"><?= htmlspecialchars($team['name']) ?></strong>
            </p>
        </div>
        <a href="<?= BASE_PATH ?>/teams/<?= $team['id'] ?>"
           style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--surface); border:1px solid var(--border); border-radius:10px; text-decoration:none; font-size:0.8rem; font-weight:500; color:var(--text-muted); transition:all 0.15s;"
           onmouseover="this.style.borderColor='var(--accent)'; this.style.color='var(--accent)'"
           onmouseout="this.style.borderColor='var(--border)'; this.style.color='var(--text-muted)'">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
    </div>

    <?php if (empty($users)): ?>
        <div class="card" style="text-align:center; padding: 60px 24px;">
            <div style="width: 64px; height: 64px; background: rgba(5,45,212,0.05); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#052DD4" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <p style="font-weight:700; color:var(--text); margin-bottom:8px; font-size: 1.1rem;">¡Equipo completo!</p>
            <p style="font-size:0.875rem; color:var(--text-muted); margin: 0;">Todos los usuarios registrados ya forman parte de este proyecto.</p>
        </div>
    <?php else: ?>
        <div class="card" style="padding: 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h3 style="font-size: 0.75rem; font-weight: 700; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.08em; margin: 0;">Selecciona los miembros</h3>
                <span style="font-size: 0.75rem; color: var(--text-muted);"><?= count($users) ?> disponibles</span>
            </div>
            
            <form action="<?= BASE_PATH ?>/teams/<?= $team['id'] ?>/add-member/store" method="POST">
                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:28px; max-height: 400px; overflow-y: auto; padding-right: 4px;">
                    <?php foreach ($users as $u): ?>
                    <label style="display:flex; align-items:center; gap:12px; padding:12px 16px; border:1px solid var(--border); border-radius:12px; cursor:pointer; transition:all 0.2s; user-select:none; position: relative;"
                           onmouseover="this.style.borderColor='#052DD4'; this.style.background='rgba(5,45,212,0.02)';"
                           onmouseout="if(!this.querySelector('input').checked) { this.style.borderColor='var(--border)'; this.style.background='transparent'; }">
                        <input type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>"
                               style="width:18px; height:18px; accent-color:#052DD4; cursor:pointer; flex-shrink:0; margin: 0;"
                               onclick="const parent = this.closest('label'); if(this.checked) { parent.style.borderColor='#052DD4'; parent.style.background='rgba(5,45,212,0.04)'; } else { parent.style.borderColor='var(--border)'; parent.style.background='transparent'; }">
                        <img src="https://api.dicebear.com/7.x/thumbs/svg?seed=<?= urlencode($u['username']) ?>&backgroundColor=b6e3f4,c0aede,d1d4f9&radius=12"
                             style="width:36px; height:36px; border-radius:10px; border: 1px solid var(--border);" alt="">
                        <div style="flex: 1;">
                            <div style="font-size:0.875rem; font-weight:600; color:var(--text);"><?= htmlspecialchars($u['username']) ?></div>
                            <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($u['email']) ?></div>
                        </div>
                        <span style="font-size: 0.65rem; font-weight: 700; color: var(--text-faint); background: var(--surface); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--divider); text-transform: uppercase;">
                            <?= htmlspecialchars($u['role'] ?? 'User') ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn btn-brand" style="flex:1; height: 44px; justify-content: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Añadir miembros
                    </button>
                    <a href="<?= BASE_PATH ?>/teams/<?= $team['id'] ?>" class="btn" 
                       style="background:var(--surface); border:1px solid var(--border); color:var(--text-muted); text-decoration:none; padding: 0 20px; display: flex; align-items: center;">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/main.php';
?>