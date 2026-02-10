# 🚀 TeamHub

**TeamHub** es una plataforma moderna y robusta para la gestión de proyectos y colaboración de equipos. Diseñada para ofrecer una experiencia de usuario fluida con una interfaz oscura y profesional, permite gestionar roles, estados de proyectos y disponibilidad de usuarios en tiempo real.

## ✨ Características Principales

-   **Gestión de Proyectos**:
    -   Visualización clara de proyectos con descripciones detalladas.
    -   Sistema de estados de proyecto: *En Progreso, Completado, Pausado, Cancelado*.
    -   Permisos diferenciados: Solo los **Jefes de Proyecto** pueden cambiar el estado.

-   **Roles y Permisos Avanzados**:
    -   **Trabajador**: Puede ver proyectos, tareas y miembros.
    -   **Jefe de Proyecto (Admin Local)**: Gestiona el estado del proyecto.
    -   **Ghost Admin (Super Usuario)**: Un rol especial oculto que tiene control total sobre todos los proyectos sin aparecer en las listas de miembros.

-   **Gestión de Disponibilidad**:
    -   Indicadores de estado de usuario en tiempo real: *🏢 Oficina, 🏠 Teletrabajo, 📅 Reunión, ⚠️ Desconectado*.
    -   Selector rápido de estado en la barra lateral.

-   **Interfaz Moderna**:
    -   Diseño *Dark Mode* profesional.
    -   Layout responsivo con Sidebar y Panel Central.
    -   Feedback visual mediante etiquetas de colores y notificaciones.

## 🛠️ Tecnologías

-   **Backend**: PHP 8.2 (Vanilla, Estructura MVC simplificada).
-   **Base de Datos**: MySQL 8.0.
-   **Servidor Web**: Apache.
-   **Infraestructura**: Docker & Docker Compose.
-   **Frontend**: HTML5, CSS3 (Grid/Flexbox), PHP Templating.

## 📦 Instalación y Despliegue

Este proyecto está contenerizado con Docker para facilitar su despliegue en cualquier entorno.

### Prerrequisitos
-   Docker y Docker Compose instalados.

### Pasos
1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/JaimeRamirezNavarro/TeamHub.git
    cd TeamHub
    ```

2.  **Iniciar los contenedores**:
    ```bash
    docker compose up -d
    ```
    Esto levantará el servidor web en el puerto `8080` y la base de datos.

3.  **Inicializar la Base de Datos**:
    Ejecuta el script de migración incluido para crear las tablas y datos de prueba:
    ```bash
    docker compose exec web php modelo/init_db.php
    ```

4.  **Acceder a la aplicación**:
    Abre tu navegador en: [http://localhost:8080](http://localhost:8080)

## 🔑 Credenciales de Prueba

El sistema viene con datos pre-cargados para probar los diferentes roles:

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| **Super Admin (Ghost)** | `admin@teamhub.com` | `1234` |
| **Jefe de Proyecto** | `sergio@teamhub.com` | `1234` |
| **Trabajador** | `david@teamhub.com` | `1234` |

*(Nota: Todos los usuarios de prueba tienen la contraseña `1234` por defecto)*

## 📂 Estructura del Proyecto

El código está organizado estrictamente en 3 capas:
-   `modelo/`: Lógica de negocio, migraciones, seeds y esquema SQL.
-   `motor/`: Conexión a base de datos y núcleo.
-   `ui/`: Vistas y controladores de interfaz.

---
Desarrollado con ❤️ por [Tu Nombre/Equipo]
