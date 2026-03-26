<!-- Todos los proyectos -->
<div class="content-header" style="margin-bottom: 28px;">
    <div>
        <h1 class="project-title" style="font-size: 1.4rem;">Todos los proyectos</h1>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 4px;">
            <?= count($equipos) ?> proyecto<?= count($equipos) !== 1 ? 's' : '' ?> en el sistema
        </p>
    </div>
    <a href="<?= BASE_PATH ?>/teams/create" class="btn btn-brand" style="gap: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Crear proyecto
    </a>
</div>

<?php if (empty($equipos)): ?>
    <div class="card" style="text-align:center; padding: 48px 24px;">
        <div style="width: 64px; height: 64px; background: rgba(5,45,212,0.05); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#052DD4" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2H8L2 7h20z"/></svg>
        </div>
        <p style="font-weight:700; color:var(--text); margin-bottom:4px; font-size: 1.1rem;">No hay proyectos aún</p>
        <p style="font-size:0.875rem; color:var(--text-muted); margin-bottom:24px;">Crea el primer proyecto para empezar a gestionar tu equipo.</p>
        <a href="<?= BASE_PATH ?>/teams/create" class="btn btn-brand">Crear primer proyecto</a>
    </div>

<?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
        <?php foreach ($equipos as $equipo):
            $s = $equipo['status'] ?? 'En Progreso';
            $dot = match(true) {
                str_contains($s, 'Comple') => '#4FC59F',
                str_contains($s, 'Progres') => '#052DD4',
                str_contains($s, 'Pausad') => '#f59e0b',
                str_contains($s, 'Cancel') => '#ef4444',
                default => '#888888'
            };
        ?>
        <a href="<?= BASE_PATH ?>/teams/<?= $equipo['id'] ?>" class="card" 
           style="text-decoration:none; display:flex; flex-direction:column; padding: 20px; cursor:pointer; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#052DD4'; this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px -8px rgba(5,45,212,0.15)'"
           onmouseout="this.style.borderColor='var(--border)'; this.style.transform=''; this.style.boxShadow='none'">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div style="font-size:1rem; font-weight:700; color:var(--text); letter-spacing: -0.01em;"><?= htmlspecialchars($equipo['name']) ?></div>
                <span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:8px; font-size:0.7rem; font-weight:700; background:<?= $dot ?>15; color:<?= $dot ?>; border:1px solid <?= $dot ?>30; text-transform: uppercase; letter-spacing: 0.05em;">
                    <span style="width:6px; height:6px; border-radius:50%; background:<?= $dot ?>; display:inline-block;"></span>
                    <?= htmlspecialchars($s) ?>
                </span>
            </div>
            
            <p style="font-size:0.875rem; color:var(--text-muted); line-height:1.6; margin:0; flex-grow: 1;">
                <?= htmlspecialchars($equipo['description'] ?: 'Sin descripción detallada disponible.') ?>
            </p>
            
            <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--divider); font-size:0.75rem; color:var(--text-muted); display:flex; align-items:center; justify-content: space-between;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:0.6"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Ver equipo</span>
                </div>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="opacity:0.4"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>