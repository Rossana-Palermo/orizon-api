<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

header('Content-Type: application/json; charset=utf-8');

$router = new Router();
require __DIR__ . '/../src/routes.php';

$request = new Request();

try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    Response::json([
        'error' => 'Errore interno del server',
        'message' => $e->getMessage(),
    ], 500);
}
