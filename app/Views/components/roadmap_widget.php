<?php if ($selected_team): ?>

<div class="roadmap-widget" id="roadmap-widget" data-team-id="<?= $selected_team['id'] ?>">

    <div class="roadmap-title" style="justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:10px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="color:var(--accent-color)">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>

            Hoja de Ruta del Proyecto

            <button id="btn-refresh-roadmap" class="btn btn-dark"
                    style="margin-left: 10px; padding: 4px 10px; font-size: 0.75rem; display:flex; align-items:center; gap:4px; height: 26px;"
                    title="Regenerar Hoja de Ruta">

                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>

                Actualizar
            </button>
        </div>
    </div>

    <div id="roadmap-content">
        <div class="gh-loader" style="padding: 40px 0;">
            <div class="spinner" style="border-top-color:#8b5cf6; width:40px; height:40px; border-width:4px;"></div>
            <br>
            <span style="background: linear-gradient(90deg, #8b5cf6, #3b82f6);
                         -webkit-background-clip: text; color: transparent;
                         font-weight:600; font-size:1.1rem; display:inline-block; margin-top:16px;">
                Se está generando la hoja de ruta
            </span>
        </div>
    </div>

</div>

<?php endif; ?>
