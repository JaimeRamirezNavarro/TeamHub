// Aplicar tema y conectar el botón — se ejecuta inmediatamente porque
// el script se carga DESPUÉS del sidebar, por lo que #themeBtn ya existe en el DOM.
(function () {
    const savedTheme = localStorage.getItem("th");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    const isDarkGlobal = savedTheme !== null ? savedTheme === '1' : prefersDark;

    // Aplicar clases iniciales
    if (isDarkGlobal) {
        document.documentElement.classList.add("dark", "dark-theme");
        document.body.classList.add("dark", "dark-theme");
    } else {
        document.documentElement.classList.remove("dark", "dark-theme");
        document.body.classList.remove("dark", "dark-theme");
    }

    const themeBtn = document.getElementById("themeBtn");

    function updateToggle(isDark) {
        if (themeBtn) {
            themeBtn.textContent = isDark ? "☀️" : "🌙";
        }
    }

    updateToggle(isDarkGlobal);

    if (themeBtn) {
        themeBtn.addEventListener("click", function () {
            const isNowDark = !document.body.classList.contains("dark");

            document.documentElement.classList.toggle("dark", isNowDark);
            document.documentElement.classList.toggle("dark-theme", isNowDark);
            document.body.classList.toggle("dark", isNowDark);
            document.body.classList.toggle("dark-theme", isNowDark);

            localStorage.setItem("th", isNowDark ? "1" : "0");
            updateToggle(isNowDark);
        });
    }
})();
