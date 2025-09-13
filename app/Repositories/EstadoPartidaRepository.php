<?php

// Repositorio de persistencia (mysqli)
// Tablas usadas: partida, jugador_partida, colocacion_dinosaurios

class GameResume {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function createPartida(int $user1Id, ?int $user2Id, array $state): int {
        $this->db->begin_transaction();
        try {
            $json = json_encode($state, JSON_UNESCAPED_UNICODE);

            $st = $this->db->prepare("INSERT INTO partida (game_state, estado) VALUES (?, 'en_curso')");
            if (!$st) throw new RuntimeException($this->db->error);
            $st->bind_param('s', $json);
            $st->execute();
            if ($st->errno) throw new RuntimeException($st->error);
            $partidaId = $this->db->insert_id;
            $st->close();

            $this->addJugador($partidaId, $user1Id, 1);
            if ($user2Id !== null && $user2Id > 0) $this->addJugador($partidaId, $user2Id, 2);

            $this->db->commit();
            return $partidaId;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function addJugador(int $partidaId, int $usuarioId, int $posicion): void {
        $st = $this->db->prepare("INSERT INTO jugador_partida (partida_id, usuario_id, posicion) VALUES (?, ?, ?)");
        if (!$st) throw new RuntimeException($this->db->error);
        $st->bind_param('iii', $partidaId, $usuarioId, $posicion);
        $st->execute();
        if ($st->errno) throw new RuntimeException($st->error);
        $st->close();
    }

    public function saveState(int $partidaId, array $state): void {
        $json = json_encode($state, JSON_UNESCAPED_UNICODE);
        $st = $this->db->prepare("UPDATE partida SET game_state = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if (!$st) throw new RuntimeException($this->db->error);
        $st->bind_param('si', $json, $partidaId);
        $st->execute();
        if ($st->errno) throw new RuntimeException($st->error);
        $st->close();
    }

    // Solo permite cargar si el usuario participa de esa partida
    public function loadStateForUser(int $partidaId, int $userId): ?array {
        $sql = "SELECT p.game_state
                FROM partida p
                JOIN jugador_partida jp ON jp.partida_id = p.id
                WHERE p.id = ? AND jp.usuario_id = ?
                LIMIT 1";
        $st = $this->db->prepare($sql);
        if (!$st) throw new RuntimeException($this->db->error);
        $st->bind_param('ii', $partidaId, $userId);
        $st->execute();
        $res = $st->get_result();
        $row = $res->fetch_assoc();
        $st->close();
        if (!$row) return null;
        $state = json_decode($row['game_state'], true);
        return is_array($state) ? $state : null;
    }

    public function getJugadorPartidaId(int $partidaId, int $usuarioId): ?int {
        $st = $this->db->prepare("SELECT id FROM jugador_partida WHERE partida_id = ? AND usuario_id = ? LIMIT 1");
        if (!$st) throw new RuntimeException($this->db->error);
        $st->bind_param('ii', $partidaId, $usuarioId);
        $st->execute();
        $res = $st->get_result();
        $row = $res->fetch_assoc();
        $st->close();
        return $row ? (int)$row['id'] : null;
    }

    public function recordPlacement(int $jugadorPartidaId, string $campo, string $especie, int $ronda, int $turno): void {
        $st = $this->db->prepare("INSERT INTO colocacion_dinosaurios (jugador_partida_id, campo, especie, ronda, turno) VALUES (?, ?, ?, ?, ?)");
        if (!$st) throw new RuntimeException($this->db->error);
        $st->bind_param('issii', $jugadorPartidaId, $campo, $especie, $ronda, $turno);
        $st->execute();
        if ($st->errno) throw new RuntimeException($st->error);
        $st->close();
    }
}