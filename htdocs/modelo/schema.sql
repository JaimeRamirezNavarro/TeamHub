-- Eliminar tablas si existen para empezar de cero (opcional)
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;

-- Tabla de Usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('Oficina', 'Teletrabajo', 'Ausente', 'Reunión', 'Desconectado', 'En Gather') DEFAULT 'Oficina',
    last_activity DATETIME DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    remember_token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_last_activity (last_activity)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de Equipos (Proyectos) con soporte para Gather
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('En Progreso', 'Completado', 'Pausado', 'Cancelado') DEFAULT 'En Progreso',
    gather_space_id VARCHAR(255) DEFAULT NULL,
    gather_space_url VARCHAR(500) DEFAULT NULL,
    gather_enabled BOOLEAN DEFAULT FALSE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de Miembros de Equipo
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    team_id INT NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE(user_id, team_id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tabla de Tareas (Opcional, para completar el esquema)
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pendiente', 'en_progreso', 'completada') DEFAULT 'pendiente',
    user_id INT,
    team_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seed Data (Datos iniciales)

-- Usuario Admin Ghost (Password: 1234)
-- Este usuario tiene permisos globales y no aparece en las listas de miembros
INSERT INTO users (username, email, password, role, status) VALUES 
('Admin', 'admin@teamhub.com', '$2y$10$placeholder', 'admin', 'Oficina');

-- Usuarios de prueba
INSERT INTO users (username, email, password, status) VALUES 
('Sergio', 'sergio@teamhub.com', '$2y$10$placeholder', 'Oficina'),
('David', 'david@teamhub.com', '$2y$10$placeholder', 'Teletrabajo'),
('Laura', 'laura@teamhub.com', '$2y$10$placeholder', 'Oficina'),
('Elena', 'elena@teamhub.com', '$2y$10$placeholder', 'Reunión');

-- Equipos / Proyectos
INSERT INTO teams (name, description, status, created_by) VALUES 
('Proyecto Alpha', 'Desarrollo de API REST para integración con sistemas externos', 'En Progreso', 1),
('Marketing Q1', 'Campaña publicitaria digital para el primer trimestre', 'En Progreso', 1),
('Infraestructura Cloud', 'Migración a arquitectura cloud y optimización de servidores', 'En Progreso', 1),
('Diseño UI/UX', 'Renovación completa de la identidad visual de la marca', 'Pausado', 1);

-- Miembros de los equipos (Admin Ghost NO se incluye aquí)
INSERT INTO team_members (user_id, team_id, role) VALUES 
-- Proyecto Alpha
(2, 1, 'admin'),  -- Sergio es jefe
(3, 1, 'member'), -- David es miembro
(4, 1, 'member'), -- Laura es miembro

-- Marketing Q1
(3, 2, 'admin'),  -- David es jefe
(5, 2, 'member'), -- Elena es miembro

-- Infraestructura
(2, 3, 'member'), -- Sergio es miembro
(4, 3, 'admin'),  -- Laura es jefe

-- Diseño UI/UX
(5, 4, 'admin'),  -- Elena es jefe
(3, 4, 'member'); -- David es miembro