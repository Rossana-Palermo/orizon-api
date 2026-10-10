<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\PaeseRepository;

class PaeseController
{
    private PaeseRepository $paesi;

    public function __construct()
    {
        $this->paesi = new PaeseRepository();
    }

    public function index(Request $request): void
    {
        Response::json($this->paesi->all(), 200);
    }

    public function show(Request $request, string $id): void
    {
        $paese = $this->paesi->find((int) $id);

        if (!$paese) {
            Response::json(['error' => 'Paese non trovato'], 404);
        }

        Response::json($paese, 200);
    }

    public function store(Request $request): void
    {
        $nome = trim((string) $request->input('nome', ''));

        if ($nome === '') {
            Response::json(['error' => 'Il campo "nome" è obbligatorio'], 422);
        }

        if ($this->paesi->findByNome($nome)) {
            Response::json(['error' => 'Esiste già un paese con questo nome'], 409);
        }

        $paese = $this->paesi->create($nome);
        Response::json($paese, 201);
    }

    public function update(Request $request, string $id): void
    {
        $paese = $this->paesi->find((int) $id);

        if (!$paese) {
            Response::json(['error' => 'Paese non trovato'], 404);
        }

        $nome = trim((string) $request->input('nome', ''));

        if ($nome === '') {
            Response::json(['error' => 'Il campo "nome" è obbligatorio'], 422);
        }

        $duplicato = $this->paesi->findByNome($nome);

        if ($duplicato && (int) $duplicato['id'] !== (int) $id) {
            Response::json(['error' => 'Esiste già un paese con questo nome'], 409);
        }

        $aggiornato = $this->paesi->update((int) $id, $nome);
        Response::json($aggiornato, 200);
    }

    public function destroy(Request $request, string $id): void
    {
        $paese = $this->paesi->find((int) $id);

        if (!$paese) {
            Response::json(['error' => 'Paese non trovato'], 404);
        }

        $this->paesi->delete((int) $id);
        Response::json(null, 204);
    }
}
