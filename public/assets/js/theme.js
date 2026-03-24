// Aplicar tema inmediatamente (evita parpadeo)
document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("teamhub-theme");
    const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

    if (savedTheme === "dark" || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add("dark-theme");
        document.body.classList.add("dark-theme");
    }

    const themeBtn = document.getElementById("theme-toggle");
    const iconLight = document.getElementById("theme-icon-light");
    const iconDark = document.getElementById("theme-icon-dark");


    function updateIcon() {
        const isDark = document.body.classList.contains("dark-theme");

        if (isDark) {
            iconLight.style.display = "block";
            iconDark.style.display = "none";
        } else {
            iconLight.style.display = "none";
            iconDark.style.display = "block";
        }
    }

    updateIcon();

    themeBtn.addEventListener("click", () => {
        document.documentElement.classList.toggle("dark-theme");
        document.body.classList.toggle("dark-theme");

        const isDark = document.body.classList.contains("dark-theme");
        localStorage.setItem("teamhub-theme", isDark ? "dark" : "light");

        updateIcon();
    });
});
