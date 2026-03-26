document.addEventListener("DOMContentLoaded", () => {
    const widget = document.querySelector(".github-widget");
    if (!widget) return;

    const repo = widget.dataset.repo;
    const tabs = widget.querySelectorAll(".github-tab");
    const contentBox = widget.querySelector("#gh-content-box");
    const branchSelector = widget.querySelector("#gh-branch-selector");

    // -------------------------
    // 1. Cargar ramas
    // -------------------------
    fetch(`${window.TeamHub_BaseUrl || ""}/api/github?repo=${repo}&action=branches`)
        .then(r => r.json())
        .then(branches => {
            branchSelector.innerHTML = "";
            branches.forEach(b => {
                const opt = document.createElement("option");
                opt.value = b.name;
                opt.textContent = b.name;
                branchSelector.appendChild(opt);
            });
            branchSelector.style.display = "block";
        });

    // -------------------------
    // 2. Función para cargar contenido
    // -------------------------
    function loadContent(type, branch = "") {
        contentBox.innerHTML = `
            <div class="gh-loader">
                <div class="spinner"></div>
                <br>Cargando ${type}...
            </div>
        `;

        let url = `${window.TeamHub_BaseUrl || ""}/api/github?repo=${repo}&action=${type}`;
        if (type === "commits" && branch) {
            url += `&branch=${branch}`;
        }

        fetch(url)
            .then(r => r.json())
            .then(data => {
                renderContent(type, data);
            });
    }

    // -------------------------
    // 3. Renderizar contenido
    // -------------------------
    function renderContent(type, data) {
        contentBox.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {
            contentBox.innerHTML = "<p>No hay datos disponibles.</p>";
            return;
        }

        data.slice(0, 10).forEach(item => {
            const div = document.createElement("div");
            div.className = "gh-item";

            if (type === "commits") {
                div.innerHTML = `
                    <strong>${item.commit.author.name}</strong>
                    <p>${item.commit.message}</p>
                `;
            }

            if (type === "pulls") {
                div.innerHTML = `
                    <strong>#${item.number} ${item.title}</strong>
                    <p>Autor: ${item.user.login}</p>
                `;
            }

            if (type === "issues") {
                div.innerHTML = `
                    <strong>#${item.number} ${item.title}</strong>
                    <p>Estado: ${item.state}</p>
                `;
            }

            contentBox.appendChild(div);
        });
    }

    // -------------------------
    // 4. Tabs
    // -------------------------
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const type = tab.dataset.target;
            loadContent(type, branchSelector.value);
        });
    });

    // -------------------------
    // 5. Cambio de rama
    // -------------------------
    branchSelector.addEventListener("change", () => {
        loadContent("commits", branchSelector.value);
    });

    // Cargar commits por defecto
    loadContent("commits");
});
