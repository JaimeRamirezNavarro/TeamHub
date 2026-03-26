<?php if ($selected_team): ?>

<div class="header" style="margin-bottom:0px;">

    <div>
        <!-- Nombre del proyecto -->
        <h1 class="project-title">
            <?= htmlspecialchars($selected_team['name']) ?>
        </h1>

        <!-- Estado del proyecto -->
        <?php 
            $statusClass = str_replace(' ', '.', $selected_team['status'] ?? 'En.Progreso'); 
        ?>

        <div style="display:flex; align-items:center; gap:12px; margin-top:8px;">
            <span class="project-status status-<?= $statusClass ?>">
                <?= htmlspecialchars($selected_team['status'] ?? 'En Progreso') ?>
            </span>

            <!-- Indicador si el usuario pertenece al equipo -->
            <?php if ($es_miembro): ?>
                <span style="font-size:0.85rem; color:var(--text-secondary); font-weight:500;">
                    Mismo equipo
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contenedor reservado para acciones de admin (separado en otro componente) -->
    <?php if ($user_role === 'admin'): ?>
        <div class="status-modifier">
            <!-- Aquí NO va nada, se renderiza en manager_controls.php -->
        </div>
    <?php endif; ?>

</div>

<?php endif; ?>
