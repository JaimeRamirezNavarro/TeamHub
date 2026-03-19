<p align="center">
  <img src="public/assets/img/logo.png" alt="TeamHub Logo" width="150" />
</p>

<h1 align="center">TeamHub</h1>

<p align="center">
  <strong>Gestión de proyectos y colaboración de equipos de alto rendimiento.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Docker-Enabled-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/Apache-2.4-D22128?style=for-the-badge&logo=apache&logoColor=white" alt="Apache" />
</p>

---

## 🌟 Descripción General

**TeamHub** es una plataforma moderna diseñada para optimizar la gestión de proyectos y la colaboración dentro de equipos de trabajo. Con una interfaz oscura y profesional, permite un control total sobre las tareas, estados de proyecto y la disponibilidad de los usuarios en tiempo real.

![TeamHub Mockup](public/assets/img/mockup.png)

## ✨ Características Principales

-   🚀 **Gestión de Proyectos Intuitiva**: Visualización clara de proyectos con descripciones detalladas y sistema de estados (*En Progreso, Completado, Pausado, Cancelado*).
-   👥 **Presencia en Tiempo Real**: Visualización dinámica de usuarios conectados, ausentes o desconectados con actualización automática.
-   🔐 **Roles y Permisos**:
    -   **Admin**: Control total del sistema y configuración.
    -   **Jefe de Proyecto**: Gestión estratégica de equipos y estados.
    -   **Trabajador**: Visualización y colaboración enfocada.
-   🎨 **Interfaz Premium**: Diseño *Dark Mode* moderno, responsivo y optimizado para una experiencia de usuario fluida.

## 🛠️ Stack Tecnológico

-   **Backend**: PHP 8.2 (Estructura organizada en `/app`).
-   **Base de Datos**: MySQL 8.0.
-   **Infraestructura**: Docker & Docker Compose.
-   **Servidor**: Apache con `mod_rewrite`.
-   **Frontend**: Vanilla JS (AJAX), CSS Moderno.

## 📂 Estructura del Proyecto

```bash
TeamHub/
├── app/            # Lógica de negocio (Controllers, Models, Views, Services)
├── config/         # Archivos de configuración y constantes
├── endpoints/      # APIs internas para funcionalidades en tiempo real
├── modelo/         # Consultas de base de datos legadas/específicas
├── public/         # Punto de entrada web y recursos estáticos (CSS, JS, Img)
├── tests/          # Suite de pruebas unitarias y funcionales
└── docker-compose.yml # Configuración de contenedores
```

## 🚀 Instalación Rápida

Sigue estos pasos para tener **TeamHub** funcionando en menos de 5 minutos:

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/JaimeRamirezNavarro/TeamHub.git
    cd TeamHub
    ```

2.  **Configurar el entorno**:
    Copia `config/db_config.example.php` a `config/db_config.php` y ajusta tus credenciales si no usas los valores por defecto de Docker.

3.  **Iniciar con Docker**:
    ```bash
    docker-compose up -d
    ```

4.  **Acceder a la plataforma**:
    -   **Web**: [http://localhost:8080](http://localhost:8080)
    -   **Base de Datos**: Puerto `3306` (Root Pass: `root`)
    -   **phpMyAdmin**: [http://localhost:8081](http://localhost:8081)

---

<p align="center">
  Hecho con ❤️ para una gestión de equipos más eficiente.
</p>
