<?php

require_once APP_PATH . '/Controllers/DinosaurioController.php';
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/Repositories/EstadoPartidaRepository.php';

class JuegoControlador
{
    private const HAND_SIZE = 6;

    private static array $RECINTOS = [
        'El Bosque de la Semejanza',
        'El Prado de la Diferencia',
        'La Pradera del Amor',
        'El Trío Frondoso',
        'El Rey de la Selva',
        'La Isla Solitaria',
        'El Rio',
    ];

    private static array $TOP_RECINTOS = [
        'El Bosque de la Semejanza',
        'El Trío Frondoso',
        'El Rey de la Selva',
        'El Prado de la Diferencia',
    ];
    private static array $BOTTOM_RECINTOS = [
        'La Pradera del Amor',
        'La Isla Solitaria',
    ];
    private static array $LEFT_RECINTOS = [
        'El Bosque de la Semejanza',
        'El Trío Frondoso',
        'La Pradera del Amor',
    ];
    private static array $RIGHT_RECINTOS = [
        'El Rey de la Selva',
        'El Prado de la Diferencia',
        'La Isla Solitaria',
    ];

    private static array $DICE_FACES = [
        'Bosque',
        'Llanura',
        'Cafetería',
        'Baños',
        'Recinto vacío',
        'Zona libre de T‑Rex'
    ];

    private static function jsonResponse(bool $success, int $code, string $message, $data = null): void {
        header('Content-Type: application/json; charset=utf-8');
        $resp = ['success' => $success, 'code' => $code, 'message' => $message];
        if (!is_null($data)) $resp['data'] = $data;
        echo json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private static function publicState(array $g): array {
        $dice = $g['dice'] ?? [
            'active' => ['face'=>null,'applies_to'=>null,'roller'=>null,'turn'=>null],
            'queued' => ['face'=>null,'applies_to'=>null,'roller'=>null,'turn'=>null],
            'last_roll_turn' => null
        ];

        $active = $dice['active'] ?? null;
        $allowed = null;
        if (!empty($active['applies_to']) && !empty($active['face'])) {
            $allowed = self::computeAllowedRecintos($g, (int)$active['applies_to'], (string)$active['face']);
        }

        $turnRolled = (isset($dice['last_roll_turn']) && isset($g['placed']) && $dice['last_roll_turn'] === $g['placed']);

        return [
            'hands' => [
                1 => array_map([self::class, 'pubDino'], $g['hands'][1]),
                2 => array_map([self::class, 'pubDino'], $g['hands'][2]),
            ],
            'boards' => $g['boards'],
            'overall_round' => $g['overall_round'] ?? 1,
            'cycle' => $g['cycle'] ?? 1,
            'current_player' => $g['current_player'] ?? 1,
            'placed' => $g['placed'] ?? 0,
            'finished' => $g['finished'] ?? false,
            'sack_remaining' => count($g['sack'] ?? []),
            'placed_count' => $g['placed_count'] ?? [1=>0,2=>0],
            'dice' => [
                'face' => $active['face'] ?? null,
                'roller' => $active['roller'] ?? null,
                'applies_to' => $active['applies_to'] ?? null,
                'allowed_recintos' => $allowed,
                'turn_rolled' => $turnRolled,
            ],
        ];
    }

    private static function pubDino($d): array {
        if (is_object($d)) {
            return [
                'id' => property_exists($d, 'id') ? $d->id : null,
                'tipo' => property_exists($d, 'tipo') ? $d->tipo : null,
                'imagen' => property_exists($d, 'imagen') ? $d->imagen : null,
            ];
        } elseif (is_array($d)) {
            return [
                'id' => $d['id'] ?? null,
                'tipo' => $d['tipo'] ?? null,
                'imagen' => $d['imagen'] ?? null,
            ];
        }
        return ['id' => null, 'tipo' => null, 'imagen' => null];
    }

    private static function buildSack48(): array {
        $dc = new DinosaurioController();
        $sack = $dc->asignacion();
        if (!is_array($sack) || count($sack) !== 48) {
            throw new RuntimeException('No se pudo construir el saco de 48 dinosaurios.');
        }
        shuffle($sack);
        return $sack;
    }

    private static function draw(array &$sack, int $n): array {
        $out = [];
        $n = min($n, count($sack));
        for ($i = 0; $i < $n; $i++) $out[] = array_pop($sack);
        return $out;
    }

    private static function findDinoIndexInHand(array $hand, $dinoId): int {
        foreach ($hand as $i => $d) {
            $id = is_object($d) ? ($d->id ?? null) : (is_array($d) ? ($d['id'] ?? null) : null);
            if ((string)$id === (string)$dinoId) return $i;
        }
        return -1;
    }

    private static function createGame(): array {
        $sack = self::buildSack48();

        $hands = [1=>[],2=>[]];
        for ($p=1; $p<=2; $p++) {
            $hands[$p] = self::draw($sack, self::HAND_SIZE);
        }

        $boards = [1=>[],2=>[]];
        foreach ([1,2] as $p) {
            foreach (self::$RECINTOS as $r) $boards[$p][$r] = [];
        }

        return [
            'sack' => $sack,
            'hands' => $hands,
            'boards' => $boards,
            'overall_round' => 1,
            'cycle' => 1,
            'current_player' => 1,
            'placed' => 0,
            'finished' => false,
            'placed_count' => [1=>0, 2=>0],
            'players' => [1=>'Jugador 1', 2=>'Jugador 2'],
            'dice' => [
                'active' => ['face'=>null,'applies_to'=>null,'roller'=>null,'turn'=>null],
                'queued' => ['face'=>null,'applies_to'=>null,'roller'=>null,'turn'=>null],
                'last_roll_turn' => null,
            ],
        ];
    }

    private static function storage(): GameResume {
        static $storage = null;
        if ($storage === null) $storage = new GameResume(db());
        return $storage;
    }

    private static function currentUserId(): int {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $cands = [
            $_REQUEST['user_id'] ?? null,
            $_SESSION['usuario_id'] ?? null,
            $_SESSION['usuario']['id'] ?? null,
            $_SESSION['user']['id'] ?? null,
        ];
        foreach ($cands as $v) {
            $id = (int)$v;
            if ($id > 0) return $id;
        }
        return 0;
    }

    public static function handleRequest(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $action = $_REQUEST['action'] ?? null;
        if (!$action) self::jsonResponse(false, 400, 'Acción no especificada. Uso: action=init|get_hand|state|place|winner|load|roll');

        switch ($action) {
            case 'init': {
                $_SESSION['game'] = self::createGame();

                $gameId = null;
                $persistWarning = null;
                $user1 = self::currentUserId();
                $opponent = isset($_REQUEST['opponent_id']) ? (int)$_REQUEST['opponent_id'] : null;

                if ($user1 > 0) {
                    try {
                        $gameId = self::storage()->createPartida($user1, $opponent, $_SESSION['game']);
                    } catch (Throwable $e) {
                        $persistWarning = 'No se pudo persistir la partida: ' . $e->getMessage();
                    }
                }

                $data = [
                    'game_id' => $gameId,
                    'game' => self::publicState($_SESSION['game']),
                ];
                if ($persistWarning) $data['persist_warning'] = $persistWarning;

                self::jsonResponse(true, 0, 'Juego inicializado', $data);
                break;
            }

            case 'load': {
                $gameId = isset($_REQUEST['game_id']) ? (int)$_REQUEST['game_id'] : 0;
                $userId = self::currentUserId();
                if ($gameId <= 0 || $userId <= 0) self::jsonResponse(false, 400, 'Faltan game_id o user_id');

                $state = self::storage()->loadStateForUser($gameId, $userId);
                if (!$state) self::jsonResponse(false, 404, 'Partida no encontrada o no perteneces a ella');

                $_SESSION['game'] = $state;
                self::jsonResponse(true, 0, 'OK', [
                    'game_id' => $gameId,
                    'game' => self::publicState($_SESSION['game']),
                ]);
                break;
            }

            case 'get_hand': {
                if (!isset($_SESSION['game'])) self::jsonResponse(false, 409, 'No hay partida cargada (usa init o load)');
                $g = &$_SESSION['game'];
                $p = isset($_REQUEST['player']) ? (int)$_REQUEST['player'] : 1;
                if (!in_array($p, [1,2], true)) self::jsonResponse(false, 400, 'Parámetro player inválido (use 1 o 2)');

                $calc = self::calculateWinner($g);
                $scores = [
                    1 => $calc['players'][1]['total'] ?? 0,
                    2 => $calc['players'][2]['total'] ?? 0,
                ];

                self::jsonResponse(true, 0, 'OK', [
                    'player' => $p,
                    'hand' => array_map([self::class, 'pubDino'], $g['hands'][$p]),
                    'sack_remaining' => count($g['sack']),
                    'placed_count' => $g['placed_count'],
                    'scores' => $scores,
                    'game' => self::publicState($g),
                ]);
                break;
            }

            case 'state': {
                if (!isset($_SESSION['game'])) self::jsonResponse(false, 409, 'No hay partida cargada (usa init o load)');
                $g = &$_SESSION['game'];
                self::jsonResponse(true, 0, 'OK', ['game' => self::publicState($g)]);
                break;
            }

            case 'place': {
                if (!isset($_SESSION['game'])) self::jsonResponse(false, 409, 'No hay partida cargada (usa init o load)');
                $g = &$_SESSION['game'];

                $player = isset($_REQUEST['player']) ? (int)$_REQUEST['player'] : 0;
                $dinoId = $_REQUEST['dino_id'] ?? null;
                $speciesLegacy = $_REQUEST['species'] ?? null;
                $recinto = $_REQUEST['recinto'] ?? null;

                $turnBefore = $g['current_player'] ?? $player;

                $turnRolled = (isset($g['dice']['last_roll_turn']) && isset($g['placed']) && $g['dice']['last_roll_turn'] === $g['placed']);
                if (!$turnRolled) {
                    self::jsonResponse(false, 1012, 'Primero tirá el dado para este turno.', [
                        'game' => self::publicState($g)
                    ]);
                }

                $active = $g['dice']['active'] ?? null;
                if (!empty($active['applies_to']) && (int)$active['applies_to'] === $player && !empty($active['face'])) {
                    $allowed = self::computeAllowedRecintos($g, $player, (string)$active['face']);
                    if (!empty($allowed) && $recinto !== null) {
                        if (!in_array($recinto, $allowed, true)) {
                            self::jsonResponse(false, 1011, 'Movimiento no permitido por dado: ' . $active['face'], [
                                'game' => self::publicState($g)
                            ]);
                        }
                    }
                }

                $placeData = self::handlePlace($g, $player, $dinoId, $speciesLegacy, $recinto);

                $g['dice']['active'] = $g['dice']['queued'] ?? ['face'=>null,'applies_to'=>null,'roller'=>null,'turn'=>null];
                $g['dice']['queued'] = ['face'=>null,'applies_to'=>null,'roller'=>null,'turn'=>null];

                $calc = self::calculateWinner($g);
                $placeData['scores'] = [
                    1 => $calc['players'][1]['total'] ?? 0,
                    2 => $calc['players'][2]['total'] ?? 0,
                ];

                $userId = self::currentUserId();
                $gameId = isset($_REQUEST['game_id']) ? (int)$_REQUEST['game_id'] : 0;
                if ($gameId > 0) {
                    try {
                        self::storage()->saveState($gameId, $g);
                        if ($userId > 0 && $recinto !== null) {
                            $jpId = self::storage()->getJugadorPartidaId($gameId, $userId);
                            if ($jpId) {
                                $speciesPlaced = end($g['boards'][$player][$recinto]) ?: null;
                                $ronda = (int)($g['overall_round'] ?? 1);
                                self::storage()->recordPlacement($jpId, $recinto, (string)$speciesPlaced, $ronda, (int)$turnBefore);
                            }
                        }
                    } catch (Throwable $e) {
                        $placeData['persist_warning'] = 'No se pudo guardar el estado: ' . $e->getMessage();
                    }
                }

                $placeData['game_id'] = $gameId ?: null;
                $placeData['game'] = self::publicState($g);

                self::jsonResponse(true, 0, 'Dinosaurio colocado', $placeData);
                break;
            }

            case 'roll': {
                if (!isset($_SESSION['game'])) self::jsonResponse(false, 409, 'No hay partida cargada (usa init o load)');
                $g = &$_SESSION['game'];
                if (!empty($g['finished'])) self::jsonResponse(false, 409, 'La partida ya ha finalizado');

                $current = (int)($g['current_player'] ?? 1);

                $turnRolled = (isset($g['dice']['last_roll_turn']) && isset($g['placed']) && $g['dice']['last_roll_turn'] === $g['placed']);
                if ($turnRolled) {
                    self::jsonResponse(false, 409, 'Ya tiraste el dado para este turno.', [
                        'game' => self::publicState($g)
                    ]);
                }

                $face = self::rollDie();
                $g['dice']['queued'] = [
                    'face' => $face,
                    'roller' => $current,
                    'applies_to' => (3 - $current),
                    'turn' => $g['placed'] ?? 0,
                ];
                $g['dice']['last_roll_turn'] = $g['placed'] ?? 0;

                $gameId = isset($_REQUEST['game_id']) ? (int)$_REQUEST['game_id'] : 0;
                if ($gameId > 0) {
                    try { self::storage()->saveState($gameId, $g); }
                    catch (Throwable $e) {
                        self::jsonResponse(false, 500, 'No se pudo guardar el estado del dado: ' . $e->getMessage(), [
                            'game' => self::publicState($g)
                        ]);
                    }
                }

                self::jsonResponse(true, 0, 'Dado tirado', [
                    'rolled_face' => $face,
                    'game' => self::publicState($g),
                ]);
                break;
            }

            case 'winner': {
                if (!isset($_SESSION['game'])) self::jsonResponse(false, 409, 'No hay partida cargada (usa init o load)');
                $g = &$_SESSION['game'];
                $res = self::calculateWinner($g);
                self::jsonResponse(true, 0, 'Resultado final', $res);
                break;
            }

            default:
                self::jsonResponse(false, 400, 'Acción desconocida. Uso: action=init|get_hand|state|place|winner|load|roll');
        }
    }

    private static function handlePlace(array &$g, int $player, $dinoId, $speciesLegacy, $recinto): array {
        if (!in_array($player, [1,2], true)) self::jsonResponse(false, 1003, 'Jugador inválido');
        if (!empty($g['finished'])) self::jsonResponse(false, 1002, 'La partida ya ha finalizado');
        if ($player !== ($g['current_player'] ?? 1)) self::jsonResponse(false, 1004, 'No es tu turno');
        if ($recinto !== null && !in_array($recinto, self::$RECINTOS, true)) self::jsonResponse(false, 1007, 'Recinto inválido');

        $idx = -1;
        if ($dinoId !== null) {
            $idx = self::findDinoIndexInHand($g['hands'][$player], $dinoId);
        } elseif ($speciesLegacy !== null) {
            foreach ($g['hands'][$player] as $i => $d) {
                $tipo = is_object($d) ? ($d->tipo ?? null) : (is_array($d) ? ($d['tipo'] ?? null) : null);
                if ($tipo === $speciesLegacy) { $idx = $i; break; }
            }
        } else {
            self::jsonResponse(false, 1005, 'Falta dino_id (o species legacy)');
        }

        if ($idx < 0) self::jsonResponse(false, 1008, 'El dinosaurio elegido no está en tu mano');

        $placedDino = array_splice($g['hands'][$player], $idx, 1)[0];
        $placedTipo = is_object($placedDino) ? ($placedDino->tipo ?? null) : (is_array($placedDino) ? ($placedDino['tipo'] ?? null) : null);

        if ($recinto !== null) {
            $can = self::canPlaceInRecinto($recinto, (string)$placedTipo, $g['boards'][$player]);
            if ($can !== true) self::jsonResponse(false, 1010, is_string($can) ? $can : 'Movimiento no permitido en este recinto');
            $g['boards'][$player][$recinto][] = $placedTipo;
        }

        $g['placed_count'][$player] = ($g['placed_count'][$player] ?? 0) + 1;
        $g['placed'] = ($g['placed'] ?? 0) + 1;

        foreach ($g['hands'][$player] as $d) { $g['sack'][] = $d; }
        $g['hands'][$player] = self::draw($g['sack'], self::HAND_SIZE);

        $g['current_player'] = (3 - $player);

        $g['finished'] = (($g['placed_count'][1] ?? 0) >= self::HAND_SIZE && ($g['placed_count'][2] ?? 0) >= self::HAND_SIZE);

        return [
            'player' => $player,
            'placed_dino' => self::pubDino($placedDino),
            'new_hand' => array_map([self::class, 'pubDino'], $g['hands'][$player]),
            'placed_count' => $g['placed_count'],
            'sack_remaining' => count($g['sack']),
            'finished' => $g['finished'],
        ];
    }

    private static function canPlaceInRecinto(string $recinto, string $species, array $board) {
        $current = $board[$recinto] ?? [];

        switch ($recinto) {
            case 'El Bosque de la Semejanza':
                if (empty($current)) return true;
                foreach ($current as $d) if ($d !== $species) return 'El Bosque de la Semejanza solo admite dinos de la misma especie que los ya alojados';
                return true;
            case 'El Prado de la Diferencia':
                foreach ($current as $d) if ($d === $species) return 'El Prado de la Diferencia no admite repeticiones de especie';
                return true;
            case 'La Pradera del Amor':
                return true;
            case 'El Trío Frondoso':
                if (count($current) >= 3) return 'El Trío Frondoso ya está lleno (máx 3)';
                return true;
            case 'El Rey de la Selva':
                if (count($current) >= 1) return 'El Rey de la Selva solo puede albergar 1 dinosaurio';
                return true;
            case 'La Isla Solitaria':
                if (count($current) >= 1) return 'La Isla Solitaria solo puede albergar 1 dinosaurio';
                return true;
            case 'El Rio':
                return true;
            default:
                return 'Recinto desconocido';
        }
    }

    private static function calculateWinner(array $g): array {
        $results = [1=>['total'=>0,'by_recinct'=>[]], 2=>['total'=>0,'by_recinct'=>[]]];

        $bosqueSemejanza = [0,2,4,8,12,18,24];
        $pradoDiferencia = [0,1,3,6,10,15,21];

        foreach ([1,2] as $p) {
            $total = 0;
            foreach (self::$RECINTOS as $r) {
                $dinos = $g['boards'][$p][$r];
                $score = 0;

                switch ($r) {
                    case 'El Bosque de la Semejanza':
                        $n = count($dinos);
                        $score = $bosqueSemejanza[$n] ?? 0;
                        break;

                    case 'El Prado de la Diferencia':
                        $n = count($dinos);
                        $score = $pradoDiferencia[$n] ?? 0;
                        break;

                    case 'La Pradera del Amor':
                        $counts = [];
                        foreach ($dinos as $d) $counts[$d] = ($counts[$d] ?? 0) + 1;
                        foreach ($counts as $c) $score += intdiv($c, 2) * 3;
                        break;

                    case 'El Trío Frondoso':
                        $score = (count($dinos) === 3) ? 7 : 0;
                        break;

                    case 'El Rey de la Selva':
                        if (!empty($dinos)) {
                            $species = $dinos[0];
                            $myCount  = self::countSpeciesInBoard($g['boards'][$p], $species);
                            $oppCount = self::countSpeciesInBoard($g['boards'][3 - $p], $species);
                            if ($myCount > $oppCount) $score = 7;
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

                $hasTRex = in_array('T-Rex', $dinos, true);
                if ($hasTRex) $score += 1;

                $results[$p]['by_recinct'][$r] = $score;
                $total += $score;
            }
            $results[$p]['total'] = $total;
            $results[$p]['player'] = $g['players'][$p] ?? ('Jugador ' . $p);
        }

        $winner = null;
        if ($results[1]['total'] > $results[2]['total']) $winner = $results[1];
        elseif ($results[2]['total'] > $results[1]) $winner = $results[2];

        return [
            'players' => $results,
            'winner' => $winner,
            'draw' => $winner === null
        ];
    }

    private static function countSpeciesInBoard(array $board, string $species): int {
        $c = 0;
        foreach ($board as $dinos) foreach ($dinos as $d) if ($d === $species) $c++;
        return $c;
    }

    private static function rollDie(): string {
        $faces = self::$DICE_FACES;
        return $faces[random_int(0, count($faces)-1)];
    }

    private static function computeAllowedRecintos(array $g, int $appliesTo, ?string $face): array {
        if (!$face) return [];
        $allowed = [];

        foreach (self::$RECINTOS as $r) {
            if (in_array($face, ['Bosque','Llanura','Cafetería','Baños'], true) && $r === 'El Rio') {
                continue;
            }
            switch ($face) {
                case 'Bosque':
                    if (in_array($r, self::$TOP_RECINTOS, true)) $allowed[] = $r;
                    break;
                case 'Llanura':
                    if (in_array($r, self::$BOTTOM_RECINTOS, true)) $allowed[] = $r;
                    break;
                case 'Cafetería':
                    if (in_array($r, self::$LEFT_RECINTOS, true)) $allowed[] = $r;
                    break;
                case 'Baños':
                    if (in_array($r, self::$RIGHT_RECINTOS, true)) $allowed[] = $r;
                    break;
                case 'Recinto vacío':
                    if (empty($g['boards'][$appliesTo][$r])) $allowed[] = $r;
                    break;
                case 'Zona libre de T‑Rex':
                    $hasTrex = in_array('T-Rex', $g['boards'][$appliesTo][$r] ?? [], true);
                    if (!$hasTrex) $allowed[] = $r;
                    break;
            }
        }
        return $allowed;
    }
}