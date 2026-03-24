<?php if ($selected_team): ?>

    <div class="main-container">

        <?php include __DIR__ . '/../components/project_header.php'; ?>

        <div class="content-grid">

            <div style="display:flex; flex-direction:column; gap:24px;">

                <?php include __DIR__ . '/../components/project_description.php'; ?>
                <?php include __DIR__ . '/../components/roadmap_widget.php'; ?>

                <?php if ($user_role === 'admin'): ?>
                    <?php include __DIR__ . '/../components/manager_controls.php'; ?>
                <?php endif; ?>

            </div>

            <div style="display:flex; flex-direction:column; gap:24px;">

                <?php include __DIR__ . '/../components/join_leave.php'; ?>
                <?php include __DIR__ . '/../components/member_list.php'; ?>
                <?php include __DIR__ . '/../components/github_widget.php'; ?>

            </div>

        </div>

    </div>

<?php else: ?>

    <div style="display:flex; justify-content:center; align-items:center; height:100%; color:#555;">
        <h2>Selecciona un proyecto de la izquierda para ver detalles</h2>
    </div>

<?php endif; ?>