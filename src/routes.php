<?php

declare(strict_types=1);

use App\Controllers\PaeseController;
use App\Controllers\ViaggioController;
use App\Core\Request;
use App\Core\Response;

/** @var \App\Core\Router $router */

// Rotta di benvenuto, utile per verificare che l'API sia online
$router->get('/', function (Request $request) {
    Response::json([
        'message' => 'Orizon API',
        'endpoints' => [
            'GET    /api/paesi',
            'GET    /api/paesi/{id}',
            'POST   /api/paesi',
            'PUT    /api/paesi/{id}',
            'DELETE /api/paesi/{id}',
            'GET    /api/viaggi',
            'GET    /api/viaggi/{id}',
            'POST   /api/viaggi',
            'PUT    /api/viaggi/{id}',
            'DELETE /api/viaggi/{id}',
        ],
    ], 200);
});

$paeseController = new PaeseController();
$viaggioController = new ViaggioController();

// ---- Risorsa: Paesi ----
$router->get('/api/paesi', [$paeseController, 'index']);
$router->get('/api/paesi/{id}', [$paeseController, 'show']);
$router->post('/api/paesi', [$paeseController, 'store']);
$router->put('/api/paesi/{id}', [$paeseController, 'update']);
$router->delete('/api/paesi/{id}', [$paeseController, 'destroy']);

// ---- Risorsa: Viaggi ----
// GET /api/viaggi supporta i filtri via query string:
//   ?paese_id=2            -> viaggi che coinvolgono il paese con id 2
//   ?posti_disponibili=4   -> viaggi con esattamente 4 posti disponibili
//   ?posti_min=1           -> viaggi con almeno 1 posto disponibile
$router->get('/api/viaggi', [$viaggioController, 'index']);
$router->get('/api/viaggi/{id}', [$viaggioController, 'show']);
$router->post('/api/viaggi', [$viaggioController, 'store']);
$router->put('/api/viaggi/{id}', [$viaggioController, 'update']);
$router->delete('/api/viaggi/{id}', [$viaggioController, 'destroy']);
