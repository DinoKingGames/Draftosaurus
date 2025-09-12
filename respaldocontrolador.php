<?php
/**
 * Controlador del juego:
 * - Saco global de 48 dinos (6 tipos x 8) desde DinosaurioController::asignacion()
 * - init: reparte 6 a cada jugador (se quitan del saco).
 * - place: jugador coloca 1, devuelve los 5 restantes al saco, baraja y reparte 5 nuevos (hasta 12 colocados por jugador).
 * - get_hand: devuelve la mano actual sin repartir extra.
 */

require_once __DIR__ . '/DinosaurioController.php';

class JuegoControlador {
    private static $RECINTOS = [
        "El Bosque de la Semejanza",
        "El Prado de la Diferencia",
        "La Pradera del Amor",
        "El Trío Frondoso",
        "El Rey de la Selva",
        "La Isla Solitaria",
        "El Rio"
    ];

    private static function jsonResponse($success, $code, $message, $data = null) {
        header('Content-Type: application/json; charset=utf-8');
        $resp = [
            'success' => (bool)$success,
            'code' => (int)$code,
            'message' => (string)$message,
        ];
        if (!is_null($data)) $resp['data'] = $data;
        echo json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private static function pubDino($d) {
        if (is_object($d)) {
            return ['id' => $d->id ?? null, 'tipo' => $d->tipo ?? null, 'imagen' => $d->imagen ?? null];
        }
        if (is_array($d)) {
            return ['id' => $d['id'] ?? null, 'tipo' => $d['tipo'] ?? null, 'imagen' => $d['imagen'] ?? null];
        }
        return ['id' => null, 'tipo' => null, 'imagen' => null];
    }

    private static function publicState($g) {
        return [
            'hands_count' => [1 => count($g['hands'][1]), 2 => count($g['hands'][2])],
            'hands' => [
                1 => array_map([self::class, 'pubDino'], $g['hands'][1]),
                2 => array_map([self::class, 'pubDino'], $g['hands'][2]),
            ],
            'placed_count' => $g['placed_count'],
            'sack_remaining' => count($g['sack']),
            'finished' => $g['finished'],
            'players' => $g['players'],
        ];
    }

    private static function buildSack48() {
        $dc = new DinosaurioController();
        $sack = $dc->asignacion(); // 48
        if (!is_array($sack) || count($sack) !== 48) {
            throw new RuntimeException('No se pudo construir el saco de 48 dinosaurios.');
        }
        shuffle($sack);
        return $sack;
    }

    private static function createGame() {
        $sack = self::buildSack48();

        $hands = [1=>[],2=>[]];
        for ($p=1; $p<=2; $p++) {
            for ($k=0; $k<6; $k++) $hands[$p][] = array_pop($sack);
        }

        $boards = [1=>[],2=>[]];
        foreach ([1,2] as $p) foreach (self::$RECINTOS as $r) $boards[$p][$r] = [];

        return [
            'sack' => $sack,
            'hands' => $hands,
            'boards' => $boards,
            'placed_count' => [1=>0,2=>0],
            'finished' => false,
            'players' => [1 => 'Jugador 1', 2 => 'Jugador 2'],
        ];
    }

    private static function draw(&$sack, $n) {
        $out = [];
        $n = min($n, count($sack));
        for ($i=0; $i<$n; $i++) $out[] = array_pop($sack);
        return $out;
    }

    private static function findDinoIndexInHand($hand, $dinoId) {
        foreach ($hand as $i => $d) {
            $id = is_object($d) ? ($d->id ?? null) : (is_array($d) ? ($d['id'] ?? null) : null);
            if ((string)$id === (string)$dinoId) return $i;
        }
        return -1;
    }

    public static function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['game'])) $_SESSION['game'] = self::createGame();
        $g = &$_SESSION['game'];

        $action = $_REQUEST['action'] ?? null;

        switch ($action) {
            case 'init': {
                $_SESSION['game'] = self::createGame();
                $g = &$_SESSION['game'];
                self::jsonResponse(true, 0, 'OK', ['game' => self::publicState($g)]);
            }

            case 'get_hand': {
                $p = isset($_REQUEST['player']) ? (int)$_REQUEST['player'] : 1;
                if ($p !== 1 && $p !== 2) self::jsonResponse(false, 400, 'Parámetro player inválido (use 1 o 2)');
                self::jsonResponse(true, 0, 'OK', [
                    'player' => $p,
                    'hand' => array_map([self::class, 'pubDino'], $g['hands'][$p]),
                    'placed_count' => $g['placed_count'],
                    'sack_remaining' => count($g['sack']),
                    'finished' => $g['finished'],
                ]);
            }

            case 'place': {
                $p = isset($_REQUEST['player']) ? (int)$_REQUEST['player'] : 1;
                $dinoId = $_REQUEST['dino_id'] ?? null;
                if ($p !== 1 && $p !== 2) self::jsonResponse(false, 400, 'Parámetro player inválido (use 1 o 2)');
                if (!$dinoId) self::jsonResponse(false, 400, 'Falta dino_id');
                if ($g['finished']) self::jsonResponse(false, 409, 'La partida ya finalizó');

                $idx = self::findDinoIndexInHand($g['hands'][$p], $dinoId);
                if ($idx < 0) self::jsonResponse(false, 404, 'El dino no está en la mano del jugador');

                $placedDino = array_splice($g['hands'][$p], $idx, 1)[0];
                $g['placed_count'][$p] = ($g['placed_count'][$p] ?? 0) + 1;

                foreach ($g['hands'][$p] as $d) $g['sack'][] = $d;
                $g['hands'][$p] = [];

                if (count($g['sack']) > 1) shuffle($g['sack']);

                if (($g['placed_count'][$p] ?? 0) < 12) {
                    $g['hands'][$p] = self::draw($g['sack'], 6);
                }

                if (($g['placed_count'][1] ?? 0) >= 12 && ($g['placed_count'][2] ?? 0) >= 12) {
                    $g['finished'] = true;
                }

                self::jsonResponse(true, 0, 'OK', [
                    'player' => $p,
                    'placed_dino' => self::pubDino($placedDino),
                    'new_hand' => array_map([self::class, 'pubDino'], $g['hands'][$p]),
                    'placed_count' => $g['placed_count'],
                    'sack_remaining' => count($g['sack']),
                    'finished' => $g['finished'],
                ]);
            }

            case 'state':
                self::jsonResponse(true, 0, 'OK', ['game' => self::publicState($g)]);

            default:
                self::jsonResponse(false, 400, 'Acción no soportada');
        }
    }
}