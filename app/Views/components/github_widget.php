<?php if (!empty($selected_team['github_repo'])): ?>

    <div class="github-widget"
        data-repo="<?= htmlspecialchars($selected_team['github_repo']) ?>"
        data-team-id="<?= htmlspecialchars($selected_team['id']) ?>">

        <div class="github-header">
            <svg height="18" viewBox="0 0 16 16" width="18" aria-hidden="true" fill="currentColor">
                <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59..."></path>
            </svg>

            <a href="https://github.com/<?= htmlspecialchars($selected_team['github_repo']) ?>"
                target="_blank"
                style="color:var(--text-primary); text-decoration:none;">
                <?= htmlspecialchars($selected_team['github_repo']) ?>
            </a>
        </div>

        <div class="github-tabs">
            <div class="github-tab active" data-target="commits" style="display:flex; align-items:center; gap:8px;">
                Commits

                <select id="gh-branch-selector"
                    style="margin:0; padding:2px 20px 2px 8px; font-size:0.75rem; display:none; max-width:140px;
                           background-color: var(--bg-color); color: var(--text-primary);
                           border: 1px solid var(--border-color); border-radius: 4px; outline: none; cursor: pointer;
                           appearance: none;">
                    <option value="">Cargando ramas...</option>
                </select>
            </div>

            <div class="github-tab" data-target="pulls">Pull Requests</div>
            <div class="github-tab" data-target="issues">Issues</div>
        </div>

        <div class="github-content" id="gh-content-box">
            <div class="gh-loader">
                <div class="spinner"></div>
                <br>Cargando datos...
            </div>
        </div>

    </div>

<?php endif; ?>