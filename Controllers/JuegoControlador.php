<?php
/**
 * Nota: En un principio agrego 24 dinos, se separan 6 y 6 para cada uno y sobrarian 12 restantes. La lógica de intercambio de manos ocurre al final de cada ciclo (cuando ambos colocan). Después de la primera ronda
 * se remezclan los 12 restantes y se reparten 6 para cada jugador (segunda ronda). Al terminar la segunda ronda se calcula el ganador.
 */

class JuegoControlador {
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

    // Inicia sesión y crea el juego si no existe
    public static function initEndpoint() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['game'])) {
            $_SESSION['game'] = self::createGame();
            self::jsonResponse(true, 0, 'Juego creado', ['game' => self::publicState($_SESSION['game'])]);
        }
        self::jsonResponse(true, 0, 'Juego ya inicializado', ['game' => self::publicState($_SESSION['game'])]);
    }

    // Devuelve estado público (oculta detalles sensibles si es necesario)
    private static function publicState($g) {
        return [
            'species' => $g['species'],
            'hands_count' => [1 => count($g['hands'][1]), 2 => count($g['hands'][2])],
            'hands' => $g['hands'], // se incluye por transparencia; eliminar si se necesita ocultar
            'boards' => $g['boards'],
            'overall_round' => $g['overall_round'],
            'cycle' => $g['cycle'],
            'current_player' => $g['current_player'],
            'placed' => $g['placed'],
            'finished' => $g['finished']
        ];
    }

    // Crea el juego: 6 especies (incluye T-Rex), 24 dinos (4 de cada), repartir 6 a cada jugador
    private static function createGame() {
        $species = ["T-Rex","Triceratops","Velociraptor","Brachiosaurio","Estegosaurio","Anquilosaurio"];

        // 4 de cada especie -> 24
        $sack = [];
        foreach ($species as $s) {
            for ($i=0;$i<4;$i++) $sack[] = $s;
        }
        shuffle($sack);

        // Repartir 6 a cada jugador
        $hands = [1=>[],2=>[]];
        for ($p=1;$p<=2;$p++) {
            for ($k=0;$k<6;$k++) {
                $hands[$p][] = array_pop($sack);
            }
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
            'players' => [1 => 'Jugador 1', 2 => 'Jugador 2']
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
                self::jsonResponse(true, 0, 'Juego en sesión', ['game' => self::publicState($g)]);
                break;
            case 'state':
                self::jsonResponse(true, 0, 'Estado actual', ['game' => self::publicState($g)]);
                break;
            case 'place':
                // Espera JSON en body o params
                $input = json_decode(file_get_contents('php://input'), true);
                if (!is_array($input)) $input = $_REQUEST;
                $player = isset($input['player']) ? intval($input['player']) : null;
                $species = $input['species'] ?? null;
                $recinto = $input['recinto'] ?? null;
                self::handlePlace($g, $player, $species, $recinto);
                break;
            case 'winner':
                if (!$g['finished']) self::jsonResponse(false, 1001, 'La partida no ha finalizado aún');
                $res = self::calculateWinner($g);
                self::jsonResponse(true, 0, 'Resultado final', $res);
                break;
            default:
                self::jsonResponse(false, 400, 'Acción no especificada o desconocida. Uso: action=init|state|place|winner');
        }
    }

    // Manejo de la acción de colocar
    private static function handlePlace(&$g, $player, $species, $recinto) {
        // Validaciones básicas
        if ($g['finished']) self::jsonResponse(false, 1002, 'La partida ya ha finalizado');
        if (!in_array($player, [1,2], true)) self::jsonResponse(false, 1003, 'Jugador inválido');
        if ($player !== $g['current_player']) self::jsonResponse(false, 1004, 'No es tu turno');
        if (!is_string($species) || trim($species) === '') self::jsonResponse(false, 1005, 'Especie no informada');
        if (!in_array($species, $g['species'], true)) self::jsonResponse(false, 1006, 'Especie desconocida');
        if (!is_string($recinto) || !in_array($recinto, self::$RECINTOS, true)) self::jsonResponse(false, 1007, 'Recinto inválido');

        // Verificar que la especie esté en la mano del jugador
        $idx = array_search($species, $g['hands'][$player], true);
        if ($idx === false) self::jsonResponse(false, 1008, 'No tienes esa especie en la mano');

        // Verificar reglas de colocación en el recinto
        $board = $g['boards'][$player];
        $opponent = $g['boards'][3 - $player];
        $canPlace = self::canPlaceInRecinto($recinto, $species, $board);
        if ($canPlace !== true) self::jsonResponse(false, 1009, 'Colocación no permitida: ' . $canPlace);

        // Realizar la colocación
        $g['boards'][$player][$recinto][] = $species;
        // Eliminar de la mano (elimina solo una instancia)
        array_splice($g['hands'][$player], $idx, 1);

        // Marcar que este jugador ya colocó en este ciclo
        $g['placed'][$player] = true;

        // Cambiar turno al otro jugador si la partida no finaliza inmediatamente
        if (!$g['placed'][3 - $player]) {
            $g['current_player'] = 3 - $player;
            $_SESSION['game'] = $g;
            self::jsonResponse(true, 0, 'Dinosaurio colocado, turno del rival', ['game' => self::publicState($g)]);
        }

        // Si ambos colocaron en este ciclo -> finalizar ciclo
        if ($g['placed'][1] && $g['placed'][2]) {
            // Incrementar ciclo
            $g['cycle'] += 1;

            if ($g['cycle'] <= 6) {
                // Intercambiar las manos restantes entre jugadores
                $tmp = $g['hands'][1];
                $g['hands'][1] = $g['hands'][2];
                $g['hands'][2] = $tmp;
                // Reset placed flags y dejar que empiece siempre el Jugador 1 en el nuevo ciclo
                $g['placed'] = [1=>false,2=>false];
                $g['current_player'] = 1;
                $_SESSION['game'] = $g;
                self::jsonResponse(true, 0, 'Ciclo completado — manos intercambiadas para el siguiente ciclo', ['game' => self::publicState($g)]);
            } else {
                // Fin de la ronda actual (si overall_round == 1 -> preparar segunda ronda)
                if ($g['overall_round'] === 1) {
                    // Re-embarajar el saco de los 12 restantes y repartir 6 a cada jugador
                    shuffle($g['sack']);
                    $g['hands'] = [1=>[],2=>[]];
                    for ($p=1;$p<=2;$p++) {
                        for ($k=0;$k<6;$k++) {
                            $g['hands'][$p][] = array_pop($g['sack']);
                        }
                    }
                    $g['overall_round'] = 2;
                    $g['cycle'] = 1;
                    $g['placed'] = [1=>false,2=>false];
                    $g['current_player'] = 1;
                    $_SESSION['game'] = $g;
                    self::jsonResponse(true, 0, 'Fin de la primera ronda. Segunda ronda comenzada tras repartir del saco', ['game' => self::publicState($g)]);
                } else {
                    // Final de la segunda ronda -> finalizar partida
                    $g['finished'] = true;
                    $_SESSION['game'] = $g;
                    $result = self::calculateWinner($g);
                    self::jsonResponse(true, 0, 'Partida finalizada', $result);
                }
            }
        }

        // Estado guardado
        $_SESSION['game'] = $g;
        self::jsonResponse(true, 0, 'Dinosaurio colocado', ['game' => self::publicState($g)]);
    }

    // Validaciones de reglas por recinto. Devuelve true si puede colocar, o string con razón si no.
    private static function canPlaceInRecinto($recinto, $species, $board) {
        $current = $board[$recinto];
        switch ($recinto) {
            case 'El Bosque de la Semejanza':
                // Solo puede albergar dinosaurios de la misma especie
                if (empty($current)) return true;
                // todos los existentes deben ser de la misma especie
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

                // Bono: si el recinto tiene al menos 1 T-Rex, suma +1 extra
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

// Si este archivo se ejecuta directamente, manejar la petición
if (php_sapi_name() !== 'cli') {
    JuegoControlador::handleRequest();
}

?>
