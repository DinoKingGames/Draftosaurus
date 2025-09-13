<?php
declare(strict_types=1);

require_once APP_PATH . '/config/database.php';

class UsuarioRepository {
    public function findByEmail(string $email): ?array {
        $conn = db();
        $sql = "SELECT id, nombre, email, contrasena, rol, estado FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();

        $row = null;
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc() ?: null;
        } else {
            $stmt->bind_result($id, $nombre, $emailOut, $hash, $rol, $estado);
            if ($stmt->fetch()) {
                $row = [
                    'id'        => (int)$id,
                    'nombre'    => $nombre,
                    'email'     => $emailOut,
                    'contrasena'=> $hash,
                    'rol'       => $rol,
                    'estado'    => $estado,
                ];
            }
        }

        $stmt->close();
        return $row;
    }

    public function findByNombre(string $nombre): ?array {
        $conn = db();
        $sql = "SELECT id, nombre, email, contrasena, rol, estado FROM usuarios WHERE nombre = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $nombre);
        $stmt->execute();

        $row = null;
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc() ?: null;
        } else {
            $stmt->bind_result($id, $nombreOut, $email, $hash, $rol, $estado);
            if ($stmt->fetch()) {
                $row = [
                    'id'        => (int)$id,
                    'nombre'    => $nombreOut,
                    'email'     => $email,
                    'contrasena'=> $hash,
                    'rol'       => $rol,
                    'estado'    => $estado,
                ];
            }
        }

        $stmt->close();
        return $row;
    }

    public function create(string $nombre, string $email, string $passwordHash, string $rol = 'user'): int {
        $conn = db();
        $sql = "INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssss', $nombre, $email, $passwordHash, $rol);
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();
        return (int)$newId;
    }
}