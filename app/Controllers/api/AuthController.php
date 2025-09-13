<?php
declare(strict_types=1);

require_once APP_PATH . '/Repositories/UsuarioRepository.php';

class AuthController {
    public static function register(array $input): void {
        $nombre   = trim($input['nombre'] ?? '');
        $email    = trim($input['email'] ?? '');
        $password = (string)($input['password'] ?? '');
        $confirm  = (string)($input['password_confirm'] ?? '');

        $errors = [];
        if ($nombre === '') $errors[] = 'El nombre es obligatorio.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
        if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($password !== $confirm) $errors[] = 'Las contraseñas no coinciden.';

        $repo = new UsuarioRepository();
        if (!$errors && ($repo->findByEmail($email) || $repo->findByNombre($nombre))) {
            $errors[] = 'Ya existe un usuario con ese email o nombre.';
        }

        if ($errors) {
            self::json(['ok' => false, 'errors' => $errors], 422);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userId = $repo->create($nombre, $email, $hash, 'user');

        $_SESSION['user'] = ['id' => $userId, 'nombre' => $nombre, 'email' => $email, 'rol' => 'user'];

        self::json(['ok' => true, 'user' => $_SESSION['user']], 201);
    }

    public static function login(array $input): void {
        $emailOrUser = trim($input['email'] ?? ''); // puedes permitir email o nombre
        $password = (string)($input['password'] ?? '');

        $errors = [];
        if ($emailOrUser === '') $errors[] = 'Email o usuario es obligatorio.';
        if ($password === '') $errors[] = 'La contraseña es obligatoria.';
        if ($errors) {
            self::json(['ok' => false, 'errors' => $errors], 422);
        }

        $repo = new UsuarioRepository();
        $user = filter_var($emailOrUser, FILTER_VALIDATE_EMAIL)
              ? $repo->findByEmail($emailOrUser)
              : $repo->findByNombre($emailOrUser);

        if (!$user) {
            self::json(['ok' => false, 'errors' => ['Usuario no encontrado.']], 401);
        }

        if ($user['estado'] !== 'activo') {
            self::json(['ok' => false, 'errors' => ['Cuenta no activa.']], 403);
        }

        if (!password_verify($password, $user['contrasena'])) {
            self::json(['ok' => false, 'errors' => ['Credenciales inválidas.']], 401);
        }

        $_SESSION['user'] = [
            'id'     => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
            'rol'    => $user['rol'],
        ];

        self::json(['ok' => true, 'user' => $_SESSION['user']]);
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true);
        }
        session_destroy();
        self::json(['ok' => true]);
    }

    public static function session(): void {
        $user = $_SESSION['user'] ?? null;
        self::json(['authenticated' => (bool)$user, 'user' => $user]);
    }

    private static function json(array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}