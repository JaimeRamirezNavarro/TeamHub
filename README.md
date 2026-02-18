# TeamHub

**TeamHub** es una plataforma moderna para la gestión de proyectos y colaboración de equipos. Ofrece una experiencia fluida con una interfaz oscura y profesional, permitiendo gestionar roles, estados de proyectos y disponibilidad de usuarios en tiempo real.

## Características Principales

-   **Gestión de Proyectos**:
    -   Visualización clara de proyectos con descripciones detalladas.
    -   Sistema de estados: *En Progreso, Completado, Pausado, Cancelado*.
    -   Control de acceso basado en roles.

-   **Usuarios Online**:
    -   Sistema de presencia en tiempo real integrado.
    -   Visualización de quién está conectado, ausente o desconectado.
    -   Actualización automática de actividad.

-   **Roles y Permisos**:
    -   **Trabajador**: Visualización y colaboración.
    -   **Jefe de Proyecto**: Gestión de estados y equipos.
    -   **Admin**: Control total del sistema.

-   **Interfaz Moderna**:
    -   Diseño *Dark Mode*.
    -   Layout responsivo.
    -   Notificaciones y feedback visual.

## Estructura del Proyecto

El código está organizado para facilitar su mantenimiento:

-   `htdocs/`
    -   `modelo/`: Lógica de negocio y consultas a BD.
    -   `motor/`: Conexión a base de datos (DB).
    -   `ui/`: Interfaz de usuario (Páginas PHP, HTML).
        -   `components/`: Fragmentos reutilizables (Widgets).
        -   `assets/`: Recursos estáticos.
    -   `endpoints/`: APIs internas para funcionalidades AJAX (Heartbeat, Status).
    -   `config/`: Archivos de configuración.

## Instalación

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/JaimeRamirezNavarro/TeamHub.git
    ```

2.  **Iniciar con Docker**:
    ```bash
    docker compose up -d
    ```

3.  **Acceder**:
    -   Web: [http://localhost:8080](http://localhost:8080)
    -   Base de Datos: Puerto 3306

---
Desarrollado para una gestión eficiente de equipos.
