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
    status ENUM('Oficina', 'Teletrabajo', 'Ausente') DEFAULT 'Oficina',
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Equipos (Proyectos)
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

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
);

-- Tabla de Tareas (Opcional, para completar el esquema)
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pendiente', 'en_progreso', 'completada') DEFAULT 'pendiente',
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Seed Data (Datos iniciales)

-- Usuario Admin (Password: 1234)
-- Nota: En producción, usar password_hash en PHP. Aquí se inserta directo para probar con la lógica actual de consultas.php
INSERT INTO users (username, email, password, status, role) VALUES 
('Admin', 'admin@teamhub.com', '$2y$10$YourHashedPasswordHereOrPlainTextIfLogicAllows', 'Oficina', 'admin'),
('Ana', 'ana@teamhub.com', '$2y$10$YourHashedPasswordHere', 'Teletrabajo', 'user'),
('Carlos', 'carlos@teamhub.com', '$2y$10$YourHashedPasswordHere', 'Oficina', 'user'),
('Pedro', 'pedro@teamhub.com', '$2y$10$YourHashedPasswordHere', 'Ausente', 'user'),
('Maria', 'maria@teamhub.com', '$2y$10$YourHashedPasswordHere', 'Teletrabajo', 'user'),
('Lucia', 'lucia@teamhub.com', '$2y$10$YourHashedPasswordHere', 'Oficina', 'user');

-- Equipos
INSERT INTO teams (name, description, created_by) VALUES 
('Proyecto Alpha', 'Desarrollo de API REST', 1),
('Marketing Q1', 'Campaña publicitaria', 1),
('Infraestructura', 'Mantenimiento de servidores', 1);

-- Miembros
-- Admin (id 1) NO se inserta para que sea Ghost Admin (no visible pero con permisos)
INSERT INTO team_members (user_id, team_id, role) VALUES 
(2, 1, 'admin'),
(3, 1, 'member'),
(4, 1, 'member'),
(5, 2, 'admin'),
(6, 2, 'member'),
(2, 2, 'member'),
(3, 3, 'admin');
