CREATE DATABASE dinoking_database;
USE dinoking_database;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) UNIQUE NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('user', 'admin', 'superadmin') NOT NULL DEFAULT 'user',
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estado ENUM('activo', 'inactivo', 'bloqueado') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS partida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_state JSON,
    estado ENUM('esperando_jugador', 'en_curso', 'finalizada') DEFAULT 'esperando_jugador',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jugador_partida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partida_id INT NOT NULL,
    usuario_id INT NOT NULL,
    posicion INT NOT NULL,

    CONSTRAINT fk_jp_partida FOREIGN KEY (partida_id) REFERENCES partida(id) ON DELETE CASCADE,
    CONSTRAINT fk_jp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,

    UNIQUE KEY unique_player_game (partida_id, usuario_id),
    UNIQUE KEY unique_partida_posicion (partida_id, posicion),

    INDEX idx_jp_partida (partida_id),
    INDEX idx_jp_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS colocacion_dinosaurios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jugador_partida_id INT NOT NULL, 
    campo VARCHAR(50) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    ronda INT NOT NULL,
    turno INT NOT NULL,
    colocado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jugador_partida_id) REFERENCES jugador_partida(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS puntuacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jugador_partida_id INT NOT NULL,
    puntos_totales INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jugador_partida_id) REFERENCES jugador_partida(id) ON DELETE CASCADE
);

INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES ('admin', 'dinoking.gamestudios@gmail.com', '$2y$10$IH7isEsgqKUgVG.R03qy4uoyOc1EQkQVgOLbw5StkWjk.lzbyP.5K', 'superadmin');
INSERT INTO usuarios(nombre, email, contrasena, rol) VALUES ('Gabriel', 'gabriel.gs@gmail.com', '$2y$10$6LvFG1WGNuCu0GvfrM8Siu95XL6yq2FoXMmE5Qs1cqB.6j56vAQRe', 'admin'); 
INSERT INTO usuarios(nombre, email, contrasena, rol) VALUES ('Fico', 'ficocapo3@gmail.com', '$2y$10$GdXws2Uj.sGUSKdVHHpJ.erL55EMMK6JLxuNi3jIb7eCB3o9Ux9Ii', 'admin');
INSERT INTO usuarios(nombre, email, contrasena, rol) VALUES('Matias', 'magonzalez0297@gmail.com', '$2y$10$wK0OWuQfyMP.qErweB5C3uFwWtZMlNFcDjDTPdMDl2BJ3iWg83jXG', 'admin');