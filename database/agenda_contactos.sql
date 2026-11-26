-- Mi agenda version 2.0 · MySQL 5.7+ / MariaDB 10.4+
-- Estudiante: Katherine Ximena Morocho Guanuche · EVA: kxmorocho
-- Desarrollo Web DSOF_3042 · Actividad APEB1
-- Seleccionar primero la base de datos asignada por el hosting.
-- No contiene credenciales ni datos personales de personas reales.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_correo (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id TINYINT UNSIGNED NOT NULL,
    nombre VARCHAR(40) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (id, nombre) VALUES
    (1,'Familia'), (2,'Amigos'), (3,'Trabajo'), (4,'Universidad'), (5,'Otro')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

CREATE TABLE IF NOT EXISTS contactos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT UNSIGNED NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    id_categoria TINYINT UNSIGNED NOT NULL,
    favorito TINYINT UNSIGNED NOT NULL DEFAULT 0,
    notas TEXT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contactos_usuario_nombre (id_usuario,nombre),
    KEY idx_contactos_usuario_ciudad (id_usuario,ciudad),
    KEY idx_contactos_usuario_favorito (id_usuario,favorito),
    KEY idx_contactos_usuario_fecha (id_usuario,fecha_creacion),
    KEY idx_contactos_categoria (id_categoria),
    CONSTRAINT fk_contactos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_contactos_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
