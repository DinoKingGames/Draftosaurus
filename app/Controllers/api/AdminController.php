<?php
declare(strict_types=1);

require_once APP_PATH . '/config/database.php';

class AdminController
{
        public static function listUsers(): void
    {
        $search = trim((string)($_GET['search'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($_GET['page_size'] ?? 50)));
        $offset = ($page - 1) * $pageSize;

        $conn = db();

        if ($search !== '') {
            $like = '%' . $search . '%';
            $countSt = $conn->prepare("SELECT COUNT(*) AS c FROM usuarios WHERE nombre LIKE ? OR email LIKE ?");
            $countSt->bind_param('ss', $like, $like);
            $countSt->execute();
            $res = $countSt->get_result()->fetch_assoc();
            $total = (int)($res['c'] ?? 0);
            $countSt->close();

            $st = $conn->prepare("SELECT id, nombre, email, rol, estado, fecha_creacion
                                  FROM usuarios
                                  WHERE nombre LIKE ? OR email LIKE ?
                                  ORDER BY id DESC
                                  LIMIT ? OFFSET ?");
            $st->bind_param('ssii', $like, $like, $pageSize, $offset);
        } else {
            $totalRes = $conn->query("SELECT COUNT(*) AS c FROM usuarios");
            $totalRow = $totalRes->fetch_assoc();
            $total = (int)($totalRow['c'] ?? 0);

            $st = $conn->prepare("SELECT id, nombre, email, rol, estado, fecha_creacion
                                  FROM usuarios
                                  ORDER BY id DESC
                                  LIMIT ? OFFSET ?");
            $st->bind_param('ii', $pageSize, $offset);
        }

        $st->execute();
        $rows = [];
        $res = $st->get_result();
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $st->close();

        json_response(['data' => $rows, 'total' => $total, 'page' => $page, 'page_size' => $pageSize]);
    }
    public static function getUser(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            json_response(['error' => 'Parámetro id requerido'], 400);
        }

        $conn = db();
        $st = $conn->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id = ? LIMIT 1");
        $st->bind_param('i', $id);
        $st->execute();
        $res = $st->get_result();
        $row = $res->fetch_assoc();
        $st->close();

        if (!$row) {
            json_response(['error' => 'Usuario no encontrado'], 404);
        }

        $avatar = self::initialsFromName($row['nombre'] ?? '');

        json_response([
            'id'    => (int)$row['id'],
            'name'  => $row['nombre'],
            'email' => $row['email'],
            'role'  => $row['rol'],
            'avatar'=> $avatar,
        ]);
    }
    public static function deleteUser(array $body): void
    {
        $id = (int)($body['id'] ?? 0);
        if ($id <= 0) json_response(['error' => 'ID inválido'], 400);

        $conn = db();
        $st = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $affected = $st->affected_rows;
        $st->close();

        if ($affected <= 0) json_response(['error' => 'Usuario no encontrado'], 404);
        json_response(['message' => 'Usuario eliminado']);
    }

    public static function getRoles(): void
    {
        $roles = [
            [ 'id' => 'superadmin', 'name' => 'Superadmin',   'permissions' => ['all'] ],
            [ 'id' => 'admin',      'name' => 'Administrador','permissions' => ['read', 'write', 'admin'] ],
            [ 'id' => 'user',       'name' => 'Usuario',      'permissions' => ['read'] ],
        ];
        json_response($roles);
    }

    public static function getRoleHistory(): void
    {
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            json_response(['error' => 'Parámetro user_id requerido'], 400);
        }

        $conn = db();
        $st = $conn->prepare("SELECT rol_anterior AS previous_role, rol_nuevo AS new_role, changed_at 
                              FROM historial_roles
                              WHERE usuario_id = ?
                              ORDER BY changed_at DESC");
        $st->bind_param('i', $userId);
        $st->execute();
        $res = $st->get_result();
        $history = [];
        while ($row = $res->fetch_assoc()) $history[] = $row;
        $st->close();

        json_response($history);
    }
    public static function updateRole(array $body): void
    {
        $userId = isset($body['user_id']) ? (int)$body['user_id'] : 0;
        $newRole = isset($body['new_role']) ? trim((string)$body['new_role']) : '';

        if ($userId <= 0 || $newRole === '') {
            json_response(['error' => 'Datos incompletos'], 400);
        }

        $valid = ['user','admin','superadmin'];
        if (!in_array($newRole, $valid, true)) {
            json_response(['error' => 'Rol no válido'], 400);
        }

        $conn = db();
        $conn->begin_transaction();
        try {
            $st = $conn->prepare("SELECT rol FROM usuarios WHERE id = ? LIMIT 1");
            $st->bind_param('i', $userId);
            $st->execute();
            $res = $st->get_result();
            $user = $res->fetch_assoc();
            $st->close();

            if (!$user) {
                $conn->rollback();
                json_response(['error' => 'Usuario no encontrado'], 404);
            }

            $previousRole = $user['rol'];
            if ($previousRole === $newRole) {
                $conn->rollback();
                json_response(['error' => 'El usuario ya tiene este rol asignado'], 400);
            }

            $st = $conn->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
            $st->bind_param('si', $newRole, $userId);
            $st->execute();
            $st->close();

            $st = $conn->prepare("INSERT INTO historial_roles (usuario_id, rol_anterior, rol_nuevo) VALUES (?, ?, ?)");
            $st->bind_param('iss', $userId, $previousRole, $newRole);
            $st->execute();
            $st->close();

            $conn->commit();
            json_response(['message' => 'Rol actualizado correctamente']);
        } catch (\Throwable $e) {
            $conn->rollback();
            json_response(['error' => 'Error al actualizar el rol'], 500);
        }
    }

    private static function initialsFromName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $ini = '';
        foreach ($parts as $p) {
            if ($p !== '') $ini .= mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8');
            if (mb_strlen($ini, 'UTF-8') >= 2) break;
        }
        return $ini !== '' ? $ini : 'U';
    }
}