<?php
class Router {
    private array $pageWhitelist = [
        'inicio',
        'jugar',
        'contacto',
        'proximamente',
        'seguimiento',
        'nosotros',
    ];

    public function handle(): void {
        $controller = $_GET['controller'] ?? null;
        $action     = $_GET['action'] ?? null;

        if ($controller && $action) {
            $this->handleController($controller, $action);
            return;
        }

        $this->handlePage($_GET['page'] ?? 'inicio');
    }

    private function handlePage(string $page): void {
        if (!in_array($page, $this->pageWhitelist, true)) {
            http_response_code(404);
            echo '404 - Página no encontrada';
            return;
        }
        require APP_PATH . '/Client/paginas/' . $page . '.php';
    }

    private function handleController(string $controller, string $action): void {
        $map = [
            'Juego'            => 'JuegoController',
            'JuegoControlador' => 'JuegoController',
        ];
        $class = $map[$controller] ?? null;

        if (!$class) {
            http_response_code(404);
            echo 'Controller no encontrado';
            return;
        }

        $file = APP_PATH . '/Controllers/' . $class . '.php';
        if (!file_exists($file)) {
            http_response_code(404);
            echo 'Archivo de controller no encontrado';
            return;
        }

        require_once $file;

        if (!class_exists($class)) {
            http_response_code(500);
            echo 'Clase de controller no definida';
            return;
        }

        $instance = new $class();
        if (!method_exists($instance, $action)) {
            http_response_code(404);
            echo 'Acción no encontrada';
            return;
        }

        $instance->$action();
    }
}