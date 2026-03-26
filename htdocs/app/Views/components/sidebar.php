<aside class="sidebar">
        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
            </div>
            <span class="brand-name">TeamHub</span>
        </div>

        <!-- User Profile -->
        <div class="user-profile">
            <div class="user-avatar-wrap">
                <img src="https://api.dicebear.com/7.x/thumbs/svg?seed=<?= urlencode($usuario["username"]) ?>&backgroundColor=b6e3f4,c0aede,d1d4f9&radius=12"
                     class="user-avatar-img" alt="Avatar">
                <span class="user-status-pip"></span>
            </div>
            <div style="flex:1; min-width:0;">
                <div class="user-name"><?= htmlspecialchars($usuario["username"]) ?></div>
                <form method="POST">
                    <input type="hidden" name="update_user_status" value="1">
                    <select name="new_user_status" class="user-status-select" onchange="this.form.submit()">
                        <option value="Oficina" <?= $usuario["status"] == 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                        <option value="Teletrabajo" <?= $usuario["status"] == 'Teletrabajo' ? 'selected' : '' ?>>Teletrabajo</option>
                        <option value="Reunión" <?= $usuario["status"] == 'Reunión' ? 'selected' : '' ?>>Reunión</option>
                        <option value="Desconectado" <?= $usuario["status"] == 'Desconectado' ? 'selected' : '' ?>>Desconectado</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Online users widget -->
        <?php include __DIR__ . '/online_widget.php'; ?>

        <!-- Projects -->
        <div class="section-label">Proyectos</div>
        <ul class="project-list">
            <?php foreach ($equipos as $equipo): ?>
            <li class="project-item">
                <a href="?team_id=<?= $equipo['id'] ?>" class="project-link <?= $selected_team_id == $equipo['id'] ? 'active' : '' ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:0.6">
                        <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2H8L2 7h20z"/>
                    </svg>
                    <?= htmlspecialchars($equipo['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <?php if (in_array($usuario['role'] ?? '', ['admin', 'manager'])): ?>
        <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 6px;">
            <a href="<?= BASE_PATH ?>/teams/create" class="project-link" style="background: rgba(5,45,212,0.06); color: #052DD4; font-weight: 600; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Crear proyecto
            </a>
            <a href="<?= BASE_PATH ?>/teams/all" class="project-link" style="gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:0.6"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Ver todos los proyectos
            </a>
        </div>
        <?php endif; ?>

        <!-- Logout -->
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--divider);">
            <div class="theme-toggle" style="margin-bottom: 12px; justify-content: flex-start; gap: 10px;">
                <span>Modo oscuro</span>
                <button type="button" class="theme-square-btn" id="themeBtn">
                    🌙
                </button>
            </div>
            <a href="/logout" class="logout-btn" style="text-decoration: none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Cerrar sesión
            </a>
        </div>
    </aside>