-- SOLO para una base anterior con tablas usuarios y contactos ya existentes.
-- Realizar respaldo. Revisar que los tipos de las claves coincidan antes de ejecutar.
-- Este script se ejecuta UNA vez; no ejecutarlo sobre una instalacion de v2.
SET NAMES utf8mb4;
CREATE TABLE categorias (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO categorias (id,nombre) VALUES (1,'Familia'),(2,'Amigos'),(3,'Trabajo'),(4,'Universidad'),(5,'Otro');
ALTER TABLE contactos
    ADD COLUMN id_categoria TINYINT UNSIGNED NOT NULL DEFAULT 5,
    ADD COLUMN favorito TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN notas TEXT NULL,
    ADD COLUMN fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ADD KEY idx_contactos_usuario_nombre (id_usuario,nombre),
    ADD KEY idx_contactos_usuario_ciudad (id_usuario,ciudad),
    ADD KEY idx_contactos_usuario_favorito (id_usuario,favorito),
    ADD KEY idx_contactos_usuario_fecha (id_usuario,fecha_creacion),
    ADD KEY idx_contactos_categoria (id_categoria),
    ADD CONSTRAINT fk_contactos_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE RESTRICT;
-- Los contactos anteriores quedan en Otro. La fecha asignada indica la migracion,
-- pues la tabla original no incluia una fecha de creacion historica.
