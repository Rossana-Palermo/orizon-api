<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    /**
     * Invia una risposta JSON con lo status code HTTP corretto
     * e termina l'esecuzione dello script (comportamento tipico
     * di un'API REST: una sola risposta per richiesta).
     */
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        if ($data !== null) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        exit;
    }
}
