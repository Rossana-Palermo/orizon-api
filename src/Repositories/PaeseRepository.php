<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;

class PaeseRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM paesi ORDER BY nome ASC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM paesi WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findByNome(string $nome): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM paesi WHERE nome = ?');
        $stmt->execute([$nome]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function create(string $nome): array
    {
        $stmt = $this->db->prepare('INSERT INTO paesi (nome) VALUES (?)');
        $stmt->execute([$nome]);

        return $this->find((int) $this->db->lastInsertId());
    }

    public function update(int $id, string $nome): ?array
    {
        $stmt = $this->db->prepare('UPDATE paesi SET nome = ? WHERE id = ?');
        $stmt->execute([$nome, $id]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM paesi WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica che tutti gli id passati corrispondano a paesi esistenti.
     * Usato quando si crea/aggiorna un viaggio.
     */
    public function existsAll(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        $ids = array_unique($ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM paesi WHERE id IN ({$placeholders})");
        $stmt->execute(array_values($ids));

        return (int) $stmt->fetchColumn() === count($ids);
    }
}
