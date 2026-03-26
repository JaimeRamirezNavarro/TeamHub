
        <?php if ($selected_team): ?>

            <!-- Header -->
            <div class="content-header">
                <div>
                    <h1 class="project-title"><?= htmlspecialchars($selected_team['name']) ?></h1>
                    <?php
                        $s = $selected_team['status'] ?? 'En Progreso';
                        $bclass = match(true) {
                            str_contains($s, 'Comple') => 'badge-green',
                            str_contains($s, 'Progres') => 'badge-blue',
                            str_contains($s, 'Pagus') || str_contains($s, 'Pausad') => 'badge-amber',
                            str_contains($s, 'Cancel') => 'badge-red',
                            default => 'badge-zinc'
                        };
                    ?>
                    <span class="status-badge <?= $bclass ?>"><?= htmlspecialchars($s) ?></span>
                    <?php if ($es_miembro): ?>
                        <span style="margin-left:10px; font-size:0.8rem; color:#4FC59F; font-weight:500;">● Eres miembro</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bento Grid -->
            <div class="bento-grid">

                <!-- Left column -->
                <div class="bento-col">
                    <!-- Description -->
                    <div class="card">
                        <div class="card-title">Descripción</div>
                        <div class="card-body"><?= nl2br(htmlspecialchars($selected_team['description'])) ?></div>
                    </div>

                    <!-- Roadmap Widget -->
                    <div class="card" id="roadmap-widget" data-team-id="<?= $selected_team['id'] ?>" style="padding-bottom:20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#052DD4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                                <span class="card-title" style="margin-bottom:0;">Hoja de Ruta</span>
                            </div>
                            <button id="btn-refresh-roadmap" style="display:flex; align-items:center; gap:5px; padding:5px 12px; background:var(--surface); border:1px solid var(--border); border-radius:8px; font-size:0.75rem; font-weight:500; color:var(--text-muted); cursor:pointer; transition:all 0.15s;" onmouseover="this.style.borderColor='#052DD4';this.style.color='#052DD4'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-muted)'">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                                Actualizar
                            </button>
                        </div>
                        <div id="roadmap-content">
                            <div class="gh-loader"><div style="width:32px;height:32px;border:3px solid var(--border);border-top-color:#052DD4;border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto;"></div><br>Generando hoja de ruta...</div>
                        </div>
                    </div>

                    <!-- Admin controls -->
                    <?php if ($user_role === 'admin'): ?>
                    <div class="card">
                        <div class="card-title">Gestión del proyecto</div>
                        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:12px;">Cambia el estado del proyecto como administrador.</p>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <select name="new_status" class="form-select">
                                <option value="En Progreso" <?= ($selected_team['status'] ?? '') == 'En Progreso' ? 'selected' : '' ?>>En Progreso</option>
                                <option value="Completado" <?= ($selected_team['status'] ?? '') == 'Completado' ? 'selected' : '' ?>>Completado</option>
                                <option value="Pausado" <?= ($selected_team['status'] ?? '') == 'Pausado' ? 'selected' : '' ?>>Pausado</option>
                                <option value="Cancelado" <?= ($selected_team['status'] ?? '') == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Actualizar</button>
                        </form>

                        <hr style="border:none; border-top:1px solid var(--border); margin:20px 0;">
                        <div style="font-size:0.85rem; font-weight:600; color:var(--text); margin-bottom:4px;">Repositorio GitHub</div>
                        <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:10px;">Formato: <code style="background:var(--divider); padding:1px 5px; border-radius:4px;">usuario/repo</code></p>
                        <form method="POST" class="status-form">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <input type="hidden" name="update_github" value="1">
                            <input type="text" name="github_repo" class="form-select" placeholder="Ej. facebook/react" value="<?= htmlspecialchars($selected_team['github_repo'] ?? '') ?>">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right column -->
                <div class="bento-col">
                    <!-- Actions -->
                    <div class="card">
                        <div class="card-title">Acciones</div>
                        <form method="POST">
                            <input type="hidden" name="team_id" value="<?= $selected_team['id'] ?>">
                            <?php if ($es_miembro): ?>
                                <button type="submit" name="leave_team" class="btn btn-danger btn-full">Abandonar proyecto</button>
                            <?php else: ?>
                                <button type="submit" name="join_team" class="btn btn-brand btn-full">Unirse al proyecto</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- Members -->
                    <div class="card">
                        <div class="card-title">Miembros (<?= count($miembros) ?>)</div>
                        <?php if (empty($miembros)): ?>
                            <p style="font-size:0.875rem; color:var(--text-muted);">No hay miembros aún.</p>
                        <?php else: ?>
                            <div class="member-list">
                                <?php foreach ($miembros as $m): ?>
                                <div class="member-item">
                                    <div class="member-info">
                                        <img src="https://api.dicebear.com/7.x/thumbs/svg?seed=<?= urlencode($m['username']) ?>&backgroundColor=b6e3f4,c0aede,d1d4f9&radius=12"
                                             class="member-avatar" alt="Avatar">
                                        <span class="member-name"><?= htmlspecialchars($m['username']) ?></span>
                                        <?php $sc = 'user-status-' . str_replace(' ', '-', $m['status']); ?>
                                        <span class="member-status-dot <?= $sc ?>"></span>
                                    </div>
                                    <?php if ($m['role'] === 'admin'): ?>
                                        <span class="role-tag role-admin">Jefe</span>
                                    <?php else: ?>
                                        <span class="role-tag role-member">Miembro</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- GitHub Widget -->
                    <?php if (!empty($selected_team['github_repo'])): ?>
                    <div class="github-widget" data-repo="<?= htmlspecialchars($selected_team['github_repo']) ?>">
                        <div class="github-header">
                            <svg height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
                            <a href="https://github.com/<?= htmlspecialchars($selected_team['github_repo']) ?>" target="_blank" style="color:var(--text); text-decoration:none; font-size:0.85rem;"><?= htmlspecialchars($selected_team['github_repo']) ?></a>
                        </div>
                        <div class="github-tabs">
                            <div class="github-tab active" data-target="commits">Commits</div>
                            <div class="github-tab" data-target="pulls">Pull Requests</div>
                            <div class="github-tab" data-target="issues">Issues</div>
                        </div>
                        <div class="github-content" id="gh-content-box"><div class="gh-loader">Cargando...</div></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d4d4d8" stroke-width="1.5" style="margin: 0 auto 12px;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 2H8L2 7h20z"/></svg>
                <p style="font-weight:600; color:#52525b;">Selecciona un proyecto</p>
                <p style="margin-top:4px; font-size:0.875rem;">Elige un proyecto del panel lateral para ver sus detalles.</p>
            </div>
        <?php endif; ?>
    