<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use PDO;
use Throwable;

class ViaggioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Restituisce tutti i viaggi, applicando eventuali filtri:
     * - paese_id: viaggi che coinvolgono un determinato paese
     * - posti_disponibili: viaggi con esattamente N posti disponibili
     * - posti_min: viaggi con almeno N posti disponibili
     */
    public function all(array $filters = []): array
    {
        $sql = 'SELECT DISTINCT v.* FROM viaggi v';
        $conditions = [];
        $params = [];

        if (!empty($filters['paese_id'])) {
            $sql .= ' INNER JOIN viaggio_paese vp ON vp.viaggio_id = v.id';
            $conditions[] = 'vp.paese_id = ?';
            $params[] = $filters['paese_id'];
        }

        if (isset($filters['posti_disponibili'])) {
            $conditions[] = 'v.posti_disponibili = ?';
            $params[] = $filters['posti_disponibili'];
        }

        if (isset($filters['posti_min'])) {
            $conditions[] = 'v.posti_disponibili >= ?';
            $params[] = $filters['posti_min'];
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY v.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $viaggi = $stmt->fetchAll();

        foreach ($viaggi as &$viaggio) {
            $viaggio['paesi'] = $this->getPaesiByViaggio((int) $viaggio['id']);
        }

        return $viaggi;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM viaggi WHERE id = ?');
        $stmt->execute([$id]);
        $viaggio = $stmt->fetch();

        if (!$viaggio) {
            return null;
        }

        $viaggio['paesi'] = $this->getPaesiByViaggio($id);

        return $viaggio;
    }

    private function getPaesiByViaggio(int $viaggioId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.nome FROM paesi p
             INNER JOIN viaggio_paese vp ON vp.paese_id = p.id
             WHERE vp.viaggio_id = ?
             ORDER BY p.nome ASC'
        );
        $stmt->execute([$viaggioId]);

        return $stmt->fetchAll();
    }

    public function create(int $postiDisponibili, array $paesiIds): array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('INSERT INTO viaggi (posti_disponibili) VALUES (?)');
            $stmt->execute([$postiDisponibili]);
            $viaggioId = (int) $this->db->lastInsertId();

            $this->syncPaesi($viaggioId, $paesiIds);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->find($viaggioId);
    }

    public function update(int $id, int $postiDisponibili, array $paesiIds): ?array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('UPDATE viaggi SET posti_disponibili = ? WHERE id = ?');
            $stmt->execute([$postiDisponibili, $id]);

            $this->syncPaesi($id, $paesiIds);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->find($id);
    }

    /**
     * Sostituisce l'elenco dei paesi collegati a un viaggio
     * (cancella le associazioni esistenti e inserisce le nuove).
     */
    private function syncPaesi(int $viaggioId, array $paesiIds): void
    {
        $stmt = $this->db->prepare('DELETE FROM viaggio_paese WHERE viaggio_id = ?');
        $stmt->execute([$viaggioId]);

        if (empty($paesiIds)) {
            return;
        }

        $stmt = $this->db->prepare('INSERT INTO viaggio_paese (viaggio_id, paese_id) VALUES (?, ?)');

        foreach (array_unique($paesiIds) as $paeseId) {
            $stmt->execute([$viaggioId, $paeseId]);
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM viaggi WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
