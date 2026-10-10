<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\PaeseRepository;
use App\Repositories\ViaggioRepository;

class ViaggioController
{
    private ViaggioRepository $viaggi;
    private PaeseRepository $paesi;

    public function __construct()
    {
        $this->viaggi = new ViaggioRepository();
        $this->paesi = new PaeseRepository();
    }

    public function index(Request $request): void
    {
        $filters = [];

        if ($request->query('paese_id') !== null) {
            $filters['paese_id'] = (int) $request->query('paese_id');
        }

        if ($request->query('posti_disponibili') !== null) {
            $filters['posti_disponibili'] = (int) $request->query('posti_disponibili');
        }

        if ($request->query('posti_min') !== null) {
            $filters['posti_min'] = (int) $request->query('posti_min');
        }

        Response::json($this->viaggi->all($filters), 200);
    }

    public function show(Request $request, string $id): void
    {
        $viaggio = $this->viaggi->find((int) $id);

        if (!$viaggio) {
            Response::json(['error' => 'Viaggio non trovato'], 404);
        }

        Response::json($viaggio, 200);
    }

    public function store(Request $request): void
    {
        [$postiDisponibili, $paesiIds, $errore] = $this->validatePayload($request);

        if ($errore) {
            Response::json(['error' => $errore], 422);
        }

        $viaggio = $this->viaggi->create($postiDisponibili, $paesiIds);
        Response::json($viaggio, 201);
    }

    public function update(Request $request, string $id): void
    {
        $esistente = $this->viaggi->find((int) $id);

        if (!$esistente) {
            Response::json(['error' => 'Viaggio non trovato'], 404);
        }

        [$postiDisponibili, $paesiIds, $errore] = $this->validatePayload($request);

        if ($errore) {
            Response::json(['error' => $errore], 422);
        }

        $viaggio = $this->viaggi->update((int) $id, $postiDisponibili, $paesiIds);
        Response::json($viaggio, 200);
    }

    public function destroy(Request $request, string $id): void
    {
        $viaggio = $this->viaggi->find((int) $id);

        if (!$viaggio) {
            Response::json(['error' => 'Viaggio non trovato'], 404);
        }

        $this->viaggi->delete((int) $id);
        Response::json(null, 204);
    }

    /**
     * Valida il payload in ingresso per creazione/aggiornamento di un viaggio.
     * Ritorna [postiDisponibili, paesiIds, erroreONull]
     */
    private function validatePayload(Request $request): array
    {
        $postiDisponibili = $request->input('posti_disponibili');
        $paesiIds = $request->input('paesi', []);

        if ($postiDisponibili === null || !is_numeric($postiDisponibili) || (int) $postiDisponibili < 0) {
            return [null, null, 'Il campo "posti_disponibili" è obbligatorio ed deve essere un numero >= 0'];
        }

        if (!is_array($paesiIds) || empty($paesiIds)) {
            return [null, null, 'Il campo "paesi" è obbligatorio e deve essere un array di id non vuoto'];
        }

        $paesiIds = array_map('intval', $paesiIds);

        if (!$this->paesi->existsAll($paesiIds)) {
            return [null, null, 'Uno o più paesi indicati non esistono'];
        }

        return [(int) $postiDisponibili, $paesiIds, null];
    }
}
