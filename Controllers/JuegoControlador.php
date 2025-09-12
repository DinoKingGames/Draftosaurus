<?php
require_once __DIR__ . '/DinosaurioController.php';

class JuegoControlador {
    // Tamaño fijo de la mano/bandeja
    private const HAND_SIZE = 6;

    // Configuración de recintos (nombres exactos usados en las validaciones)
    private static $RECINTOS = [
        "El Bosque de la Semejanza",
        "El Prado de la Diferencia",
        "La Pradera del Amor",
        "El Trío Frondoso",
        "El Rey de la Selva",
        "La Isla Solitaria",
        "El Rio"
    ];

    // Respuesta JSON estandarizada
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

    // Inicia sesión y crea el juego si no existe (compatibilidad con endpoint legacy)
    public static function initEndpoint() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['game'])) {
            $_SESSION['game'] = self::createGame();
            self::jsonResponse(true, 0, 'Juego creado', ['game' => self::publicState($_SESSION['game'])]);
        }
        self::jsonResponse(true, 0, 'Juego ya inicializado', ['game' => self::publicState($_SESSION['game'])]);
    }

    // Convierte un dinosaurio (objeto/array) a forma pública segura
    private static function pubDino($d) {
        if (is_object($d)) {
            return [
                'id' => property_exists($d, 'id') ? $d->id : null,
                'tipo' => property_exists($d, 'tipo') ? $d->tipo : null,
                'imagen' => property_exists($d, 'imagen') ? $d->imagen : null,
            ];
        }
        if (is_array($d)) {
            return [
                'id' => $d['id'] ?? null,
                'tipo' => $d['tipo'] ?? null,
                'imagen' => $d['imagen'] ?? null,
            ];
        }
        return ['id' => null, 'tipo' => null, 'imagen' => null];
    }

    // Devuelve estado público (oculta detalles sensibles si es necesario)
    private static function publicState($g) {
        // Para transparencia, exponemos manos en formato sanitizado
        return [
            'species' => $g['species'],
            'hands_count' => [1 => count($g['hands'][1]), 2 => count($g['hands'][2])],
            'hands' => [
                1 => array_map([self::class, 'pubDino'], $g['hands'][1]),
                2 => array_map([self::class, 'pubDino'], $g['hands'][2]),
            ],
            'boards' => $g['boards'],
            'overall_round' => $g['overall_round'],
            'cycle' => $g['cycle'],
            'current_player' => $g['current_player'],
            'placed' => $g['placed'],
            'finished' => $g['finished'],
            'sack_remaining' => count($g['sack']),
            'placed_count' => $g['placed_count'],
        ];
    }

    // Construye el saco de 48 dinosaurios con id/tipo/imagen
    private static function buildSack48() {
        $dc = new DinosaurioController();
        $sack = $dc->asignacion(); // devuelve 48
        if (!is_array($sack) || count($sack) !== 48) {
            throw new RuntimeException('No se pudo construir el saco de 48 dinosaurios.');
        }
        shuffle($sack);
        return $sack;
    }

    // Extrae n elementos del saco (al final del array)
    private static function draw(&$sack, $n) {
        $out = [];
        $n = min($n, count($sack));
        for ($i=0; $i<$n; $i++) $out[] = array_pop($sack);
        return $out;
    }

    // Busca el índice de un dino por id dentro de una mano
    private static function findDinoIndexInHand($hand, $dinoId) {
        foreach ($hand as $i => $d) {
            $id = is_object($d) ? ($d->id ?? null) : (is_array($d) ? ($d['id'] ?? null) : null);
            if ((string)$id === (string)$dinoId) return $i;
        }
        return -1;
    }

    // Crea el juego: 48 dinos, repartir 6 a cada jugador
    private static function createGame() {
        // Especies visibles (colores) para UI pública
        $species = ["Azul","Cyan","Naranja","Rojo","Rosado","Verde"];

        $sack = self::buildSack48();

        // Repartir 6 a cada jugador
        $hands = [1=>[],2=>[]];
        for ($p=1;$p<=2;$p++) {
            $hands[$p] = self::draw($sack, self::HAND_SIZE);
        }

        // Crear tableros vacíos con 7 recintos
        $boards = [1=>[],2=>[]];
        foreach ([1,2] as $p) {
            foreach (self::$RECINTOS as $r) $boards[$p][$r] = [];
        }

        return [
            'species' => $species,
            'sack' => $sack,
            'hands' => $hands,
            'boards' => $boards,
            'overall_round' => 1,
            'cycle' => 1,
            'placed' => [1=>false,2=>false],
            'current_player' => 1,
            'finished' => false,
            'players' => [1 => 'Jugador 1', 2 => 'Jugador 2'],
            // Nuevo: conteo de colocados por jugador para detectar fin de partida (12 c/u)
            'placed_count' => [1=>0, 2=>0],
        ];
    }

    // Endpoint público que atiende acciones simples
    public static function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['game'])) {
            $_SESSION['game'] = self::createGame();
        }
        $g = &$_SESSION['game'];

        $action = $_REQUEST['action'] ?? null;

        switch ($action) {
            case 'init':
                // Reinicia la partida y reparte 6 a cada jugador
                $_SESSION['game'] = self::createGame();
                $g = &$_SESSION['game'];
                self::jsonResponse(true, 0, 'Juego inicializado', ['game' => self::publicState($g)]);
                break;

            case 'get_hand': {
                $p = isset($_REQUEST['player']) ? (int)$_REQUEST['player'] : 1;
                if (!in_array($p, [1,2], true)) self::jsonResponse(false, 400, 'Parámetro player inválido (use 1 o 2)');
                self::jsonResponse(true, 0, 'OK', [
                    'player' => $p,
                    'hand' => array_map([self::class, 'pubDino'], $g['hands'][$p]),
                    'sack_remaining' => count($g['sack']),
                    'placed_count' => $g['placed_count'],
                    'finished' => $g['finished'],
                ]);
                break;
            }

            case 'state':
                self::jsonResponse(true, 0, 'Estado actual', ['game' => self::publicState($g)]);
                break;

            case 'place':
                // Espera JSON en body o params
                $input = json_decode(file_get_contents('php://input'), true);
                if (!is_array($input)) $input = $_REQUEST;
                $player  = isset($input['player']) ? intval($input['player']) : null;
                $recinto = $input['recinto'] ?? null;

                // Soporte para forma nueva (dino_id) y forma legacy (species)
                $dinoId  = $input['dino_id'] ?? null;
                $species = $input['species'] ?? null;

                self::handlePlace($g, $player, $dinoId, $species, $recinto);
                break;

            case 'winner':
                if (!$g['finished']) self::jsonResponse(false, 1001, 'La partida no ha finalizado aún');
                $res = self::calculateWinner($g);
                self::jsonResponse(true, 0, 'Resultado final', $res);
                break;

            default:
                self::jsonResponse(false, 400, 'Acción no especificada o desconocida. Uso: action=init|get_hand|state|place|winner');
        }
    }

    // Manejo de la acción de colocar (regla: tras colocar 1, devolver resto y repartir a 6)
    private static function handlePlace(&$g, $player, $dinoId, $speciesLegacy, $recinto) {
        // Validaciones básicas
        if ($g['finished']) self::jsonResponse(false, 1002, 'La partida ya ha finalizado');
        if (!in_array($player, [1,2], true)) self::jsonResponse(false, 1003, 'Jugador inválido');

        // Si deseas forzar turnos alternados, mantén esta validación:
        if ($player !== $g['current_player']) self::jsonResponse(false, 1004, 'No es tu turno');

        // Validación de recinto si viene informado (puede omitirse por ahora)
        if ($recinto !== null && !in_array($recinto, self::$RECINTOS, true)) {
            self::jsonResponse(false, 1007, 'Recinto inválido');
        }

        // Buscar el dino en la mano del jugador
        $idx = -1;
        $placedDino = null;

        if ($dinoId !== null) {
            $idx = self::findDinoIndexInHand($g['hands'][$player], $dinoId);
        } elseif ($speciesLegacy !== null) {
            // Compatibilidad: buscar por tipo (color) en la mano actual
            foreach ($g['hands'][$player] as $i => $d) {
                $tipo = is_object($d) ? ($d->tipo ?? null) : (is_array($d) ? ($d['tipo'] ?? null) : null);
                if ($tipo === $speciesLegacy) { $idx = $i; break; }
            }
        } else {
            self::jsonResponse(false, 1005, 'Falta dino_id (o species legacy)');
        }

        if ($idx < 0) self::jsonResponse(false, 1008, 'El dinosaurio elegido no está en tu mano');

        // Extraer el dino colocado
        $placedDino = array_splice($g['hands'][$player], $idx, 1)[0];
        $placedTipo = is_object($placedDino) ? ($placedDino->tipo ?? null) : (is_array($placedDino) ? ($placedDino['tipo'] ?? null) : null);

        // Registrar en tablero si se informó recinto
        if ($recinto !== null) {
            $g['boards'][$player][$recinto][] = $placedTipo;
        }

        // Marcar colocación
        $g['placed_count'][$player] = ($g['placed_count'][$player] ?? 0) + 1;

        // Devolver el resto de la mano al saco
        foreach ($g['hands'][$player] as $d) $g['sack'][] = $d;
        // Vaciar mano
        $g['hands'][$player] = [];

        // Barajar saco
        if (count($g['sack']) > 1) shuffle($g['sack']);

        // Repartir hasta tener 6 nuevamente si aún no llegó a 12 colocaciones
        if (($g['placed_count'][$player] ?? 0) < 12) {
            $g['hands'][$player] = self::draw($g['sack'], self::HAND_SIZE);
        }

        // ¿Fin de partida?
        if (($g['placed_count'][1] ?? 0) >= 12 && ($g['placed_count'][2] ?? 0) >= 12) {
            $g['finished'] = true;
        }

        // Cambiar turno
        if (!$g['finished']) {
            $g['current_player'] = 3 - $player;
        }

        $_SESSION['game'] = $g;

        self::jsonResponse(true, 0, 'Dinosaurio colocado', [
            'player' => $player,
            'placed_dino' => self::pubDino($placedDino),
            'new_hand' => array_map([self::class, 'pubDino'], $g['hands'][$player]),
            'placed_count' => $g['placed_count'],
            'sack_remaining' => count($g['sack']),
            'finished' => $g['finished'],
            'game' => self::publicState($g),
        ]);
    }

    // Validaciones de reglas por recinto. Devuelve true si puede colocar, o string con razón si no.
    // Nota: Usará el 'tipo' como "especie" lógica (coincide con colores actuales). Se conserva para uso futuro.
    private static function canPlaceInRecinto($recinto, $species, $board) {
        $current = $board[$recinto];
        switch ($recinto) {
            case 'El Bosque de la Semejanza':
                // Solo puede albergar dinosaurios de la misma especie
                if (empty($current)) return true;
                foreach ($current as $d) if ($d !== $species) return 'El Bosque de la Semejanza solo admite dinos de la misma especie que los ya alojados';
                return true;

            case 'El Prado de la Diferencia':
                // Solo especies distintas
                foreach ($current as $d) if ($d === $species) return 'El Prado de la Diferencia no admite repeticiones de especie';
                return true;

            case 'La Pradera del Amor':
                // Acepta todas las especies, no hay restricción
                return true;

            case 'El Trío Frondoso':
                // Hasta 3 dinosaurios
                if (count($current) >= 3) return 'El Trío Frondoso ya está lleno (máx 3)';
                return true;

            case 'El Rey de la Selva':
                // Solo 1 dinosaurio
                if (count($current) >= 1) return 'El Rey de la Selva solo puede albergar 1 dinosaurio';
                return true;

            case 'La Isla Solitaria':
                if (count($current) >= 1) return 'La Isla Solitaria solo puede albergar 1 dinosaurio';
                return true;

            case 'El Rio':
                // Sin restricciones
                return true;

            default:
                return 'Recinto desconocido';
        }
    }

    // Calcula el ganador y devuelve detalle por jugador y por recinto
    private static function calculateWinner($g) {
        $results = [1=>['total'=>0,'by_recinct'=>[]], 2=>['total'=>0,'by_recinct'=>[]]];

        foreach ([1,2] as $p) {
            $total = 0;
            foreach (self::$RECINTOS as $r) {
                $dinos = $g['boards'][$p][$r];
                $score = 0;

                switch ($r) {
                    case 'El Bosque de la Semejanza':
                        // cada dino vale 1, solo debió permitirse misma especie
                        $score = count($dinos) * 1;
                        break;
                    case 'El Prado de la Diferencia':
                        $score = count($dinos) * 1;
                        break;
                    case 'La Pradera del Amor':
                        // 5 puntos por cada pareja de la misma especie
                        $counts = [];
                        foreach ($dinos as $d) $counts[$d] = ($counts[$d] ?? 0) + 1;
                        foreach ($counts as $c) $score += intdiv($c, 2) * 5;
                        break;
                    case 'El Trío Frondoso':
                        if (count($dinos) === 3) $score = 7;
                        break;
                    case 'El Rey de la Selva':
                        if (count($dinos) === 1) {
                            $species = $dinos[0];
                            // contar especies del rival en todo su tablero
                            $oppCount = self::countSpeciesInBoard($g['boards'][3 - $p], $species);
                            if ($oppCount < 1) $score = 7; // rival tiene menos dinos de esa especie
                        }
                        break;
                    case 'La Isla Solitaria':
                        if (count($dinos) === 1) {
                            $species = $dinos[0];
                            $myCount = self::countSpeciesInBoard($g['boards'][$p], $species);
                            if ($myCount === 1) $score = 7;
                        }
                        break;
                    case 'El Rio':
                        $score = count($dinos) * 1;
                        break;
                }

                // Bono opcional: si el recinto tiene al menos 1 T-Rex, suma +1 extra
                $hasTRex = in_array('T-Rex', $dinos, true);
                if ($hasTRex) $score += 1;

                $results[$p]['by_recinct'][$r] = $score;
                $total += $score;
            }
            $results[$p]['total'] = $total;
            $results[$p]['player'] = $g['players'][$p] ?? ('Jugador ' . $p);
        }

        // Determinar ganador (si empate, devolver ambos)
        if ($results[1]['total'] > $results[2]['total']) {
            $winner = $results[1];
        } elseif ($results[2]['total'] > $results[1]['total']) {
            $winner = $results[2];
        } else {
            $winner = null; // empate
        }

        return [
            'players' => $results,
            'winner' => $winner,
            'draw' => $winner === null
        ];
    }

    private static function countSpeciesInBoard($board, $species) {
        $c = 0;
        foreach ($board as $r => $dinos) {
            foreach ($dinos as $d) if ($d === $species) $c++;
        }
        return $c;
    }
}

if (php_sapi_name() !== 'cli') {
    JuegoControlador::handleRequest();
}
?>