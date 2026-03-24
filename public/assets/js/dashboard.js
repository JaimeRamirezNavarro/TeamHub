try {
  // GitHub Widget Logic
  document.addEventListener("DOMContentLoaded", () => {
    const ghWidget = document.querySelector(".github-widget");
    if (!ghWidget) return;

    const repo = ghWidget.dataset.repo;
    const tabs = document.querySelectorAll(".github-tab");
    const contentBox = document.getElementById("gh-content-box");
    const branchSelect = document.getElementById("gh-branch-selector");
    let loadedBranches = false;

    // Helper to fetch and populate branches
    const loadBranchesDropdown = async () => {
      if (loadedBranches) return;
      try {
        const res = await fetch(
          `${window.TeamHub_BaseUrl || ""}/api/github?action=branches&repo=${repo}`,
        );
        if (!res.ok) throw new Error("API Error");
        const data = await res.json();
        if (Array.isArray(data) && data.length > 0) {
          let html = "";
          data.forEach((b) => {
            const isMain = b.name === "main" || b.name === "master";
            html += `<option value="${b.name}" ${isMain ? "selected" : ""}>${b.name}</option>`;
          });
          branchSelect.innerHTML = html;
          branchSelect.style.display = "inline-block";
          loadedBranches = true;

          if (
            document
              .querySelector('.github-tab[data-target="commits"]')
              .classList.contains("active")
          ) {
            loadGitHubData("commits", branchSelect.value);
          }
        } else {
          branchSelect.style.display = "none";
        }
      } catch (e) {
        branchSelect.style.display = "none";
      }
    };

    const loadGitHubData = async (action, branch = null) => {
      contentBox.innerHTML =
        '<div class="gh-loader"><div class="spinner"></div><br>Cargando...</div>';
      try {
        let url = `${window.TeamHub_BaseUrl || ""}/api/github?action=${action}&repo=${repo}`;
        if (action === "commits" && branch) {
          url += `&branch=${encodeURIComponent(branch)}`;
        }

        const res = await fetch(url);
        if (!res.ok) throw new Error("API Error");
        const data = await res.json();

        if (!Array.isArray(data) || data.length === 0) {
          contentBox.innerHTML =
            '<div class="empty-state"><span>No hay elementos recientes</span></div>';
          return;
        }

        let html = "";
        data.forEach((item) => {
          if (action === "commits") {
            const msg = item.commit.message.split("\n")[0];
            const author = item.commit.author.name;
            const date = new Date(item.commit.author.date).toLocaleDateString(
              undefined,
              { month: "short", day: "numeric" },
            );
            html += `
                        <div class="gh-item">
                            <a href="${item.html_url}" target="_blank" class="gh-title">${msg}</a>
                            <div class="gh-meta">Commit por <span style="font-weight:500;color:var(--text-secondary)">${author}</span> el ${date}</div>
                        </div>`;
          } else if (action === "pulls") {
            const title = item.title;
            const user = item.user.login;
            const stateBadge =
              item.state === "open"
                ? '<span class="gh-badge open">Abierto</span>'
                : '<span class="gh-badge merged">Fusionado</span>';
            html += `
                        <div class="gh-item">
                            <a href="${item.html_url}" target="_blank" class="gh-title">${title}</a>
                            <div class="gh-meta">${stateBadge} #${item.number} por ${user}</div>
                        </div>`;
          } else if (action === "issues") {
            if (item.pull_request) return;
            const title = item.title;
            const user = item.user.login;
            const stateBadge =
              item.state === "open"
                ? '<span class="gh-badge open">Abierto</span>'
                : '<span class="gh-badge closed">Cerrado</span>';
            html += `
                        <div class="gh-item">
                            <a href="${item.html_url}" target="_blank" class="gh-title">${title}</a>
                            <div class="gh-meta">${stateBadge} #${item.number} por ${user}</div>
                        </div>`;
          } else if (action === "branches") {
            const name = item.name;
            html += `
                        <div class="gh-item" style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div class="gh-title" style="margin-bottom:0;">
                                    <svg viewBox="0 0 16 16" width="14" height="14" style="fill: currentColor; vertical-align: middle; margin-right: 4px;"><path fill-rule="evenodd" d="M11.75 2.5a.75.75 0 100 1.5.75.75 0 000-1.5zm-2.25.75a2.25 2.25 0 113 2.122V6A2.5 2.5 0 0110 8.5H6a1 1 0 00-1 1v1.128a2.251 2.251 0 11-1.5 0V5.372a2.25 2.25 0 111.5 0v1.836A2.492 2.492 0 016 7h4a1 1 0 001-1v-1.378A2.25 2.25 0 019.5 3.25zM4.25 12a.75.75 0 100 1.5.75.75 0 000-1.5zM3.5 3.25a.75.75 0 111.5 0 .75.75 0 01-1.5 0z"></path></svg>
                                    ${name}
                                </div>
                            </div>
                        </div>`;
          }
        });
        contentBox.innerHTML = html;
      } catch (err) {
        contentBox.innerHTML =
          '<div class="gh-loader" style="color:#ff5252;">Error o Repositorio no encontrado (o privado).</div>';
      }
    };

    tabs.forEach((tab) => {
      tab.addEventListener("click", (e) => {
        if (e.target.tagName === "SELECT" || e.target.tagName === "OPTION")
          return;

        tabs.forEach((t) => t.classList.remove("active"));
        tab.classList.add("active");

        const target = tab.dataset.target;
        if (
          target === "commits" &&
          branchSelect &&
          branchSelect.style.display !== "none"
        ) {
          loadGitHubData(target, branchSelect.value);
        } else {
          loadGitHubData(target);
        }
      });
    });

    if (branchSelect) {
      branchSelect.addEventListener("change", () => {
        loadGitHubData("commits", branchSelect.value);
      });
    }

    loadGitHubData("commits");
    loadBranchesDropdown();
  });

  // Roadmap Logic
  let roadmapIsUnloading = false;
  window.addEventListener("beforeunload", () => {
    roadmapIsUnloading = true;
  });

  document.addEventListener("DOMContentLoaded", async () => {
    const roadmapWidget = document.getElementById("roadmap-widget");
    if (!roadmapWidget) return;

    const teamId = roadmapWidget.dataset.teamId;
    if (!teamId) return;

    const contentBox = document.getElementById("roadmap-content");
    const refreshBtn = document.getElementById("btn-refresh-roadmap");

    const fetchRoadmap = async (forceRefresh = false) => {
      contentBox.innerHTML = `
            <div class="gh-loader" style="padding: 40px 0;">
                <div class="spinner" style="border-top-color:#8b5cf6; width:40px; height:40px; border-width:4px;"></div>
                <br><span style="background: linear-gradient(90deg, #8b5cf6, #3b82f6); -webkit-background-clip: text; color: transparent; font-weight:600; font-size:1.1rem; display:inline-block; margin-top:16px;">Se esta generando la hoja de ruta</span>
            </div>
        `;
      if (refreshBtn) refreshBtn.disabled = true;

      try {
        const endpoint = forceRefresh
          ? `${window.TeamHub_BaseUrl || ""}/api/roadmap?team_id=${teamId}&force_refresh=true`
          : `${window.TeamHub_BaseUrl || ""}/api/roadmap?team_id=${teamId}`;

        const res = await fetch(endpoint);
        if (!res.ok) {
          const errText = await res.text();
          throw new Error(`HTTP ${res.status}: ${errText}`);
        }

        const data = await res.json();
        if (data.error) throw new Error(data.error);

        const roadmap = data.roadmap;
        let html = '<div class="roadmap-container">';
        let hasActivePhase = false;

        Object.keys(roadmap).forEach((key, index) => {
          const phase = roadmap[key];
          let statusClass = "";

          if (phase.completado) {
            statusClass = "completed";
          } else if (!hasActivePhase && phase.avance > 0) {
            statusClass = "active";
            hasActivePhase = true;
          } else if (!hasActivePhase && phase.avance === 0) {
            statusClass = "active";
            hasActivePhase = true;
          }

          html += `
                    <div class="roadmap-phase ${statusClass}">
                        <div class="phase-dot">${phase.completado ? "✓" : index + 1}</div>
                        <div class="phase-content">
                            <div class="phase-title">${phase.nombre}</div>
                            <div class="phase-desc">${phase.desc}</div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div class="phase-progress-bar">
                                    <div class="phase-progress-fill" style="width: ${phase.avance}%"></div>
                                </div>
                                <span style="font-size:0.75rem; color:var(--text-muted); font-weight:600;">${phase.avance}%</span>
                            </div>
                        </div>
                    </div>
                `;
        });

        html += "</div>";

        if (data.github) {
          html += `
                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed var(--border-color); display:flex; gap:16px; font-size:0.8rem;">
                    <div style="color:var(--text-secondary)"><strong style="color:var(--text-primary)">${data.github.commits}</strong> Commits Recientes</div>
                    <div style="color:var(--text-secondary)"><strong style="color:var(--text-primary)">${data.github.prs_closed}</strong> PRs Cerrados</div>
                    ${data.github.active ? '<div style="color:var(--success-color); font-weight:600;">Proyecto Activo</div>' : ""}
                </div>`;
        }

        contentBox.innerHTML = html;
        if (refreshBtn) refreshBtn.disabled = false;
      } catch (err) {
        if (!roadmapIsUnloading) {
          contentBox.innerHTML = `<div class="empty-state">No se pudo cargar la hoja de ruta.<br><small style="color:red; font-size:0.8rem; margin-top:8px; display:inline-block;">${err.message}</small></div>`;
        }
        if (refreshBtn) refreshBtn.disabled = false;
      }
    };

    if (refreshBtn) {
      refreshBtn.addEventListener("click", () => {
        fetchRoadmap(true);
      });
    }

    fetchRoadmap(false);
  });
} catch (err) {
  console.error("Error en dashboard.js:", err);
}
