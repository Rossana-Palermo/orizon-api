# Orizon API

API JSON RESTful per Orizon, agenzia di viaggi sostenibili. Permette di gestire
paesi e viaggi "last minute", così da poterli sponsorizzare in base a
disponibilità e destinazioni.

## Modello dati

- **Paese**: `id`, `nome`
- **Viaggio**: `id`, `posti_disponibili`
- **viaggio_paese** (tabella ponte): collega `viaggio_id` e `paese_id`

Relazione: un Viaggio può coinvolgere **più** Paesi, e un Paese può essere
coinvolto in **più** Viaggi → relazione **molti-a-molti**, gestita tramite la
tabella ponte `viaggio_paese`.

```
paesi (1) ----- (N) viaggio_paese (N) ----- (1) viaggi
```

## Stack

- PHP >= 8.0 (nessun framework, router + DI "a mano")
- MySQL
- PDO con prepared statement (protezione da SQL injection)
- Composer per autoload PSR-4 (`App\` → `src/`) e per `vlucas/phpdotenv`

## Setup

1. Clona il repository ed entra nella cartella del progetto.
2. Installa le dipendenze:
   ```
   composer install
   ```
3. Copia `.env.example` in `.env` e inserisci le tue credenziali MySQL:
   ```
   cp .env.example .env
   ```
4. Crea il database importando `migrations.sql`:
   ```
   mysql -u root -p < migrations.sql
   ```
   (oppure importalo da phpMyAdmin)
5. Avvia il server:
   - Con XAMPP: metti il progetto in `htdocs/` e visita `http://localhost/orizon-api/public/`
   - Oppure con il server built-in di PHP:
     ```
     php -S localhost:8000 -t public
     ```

## Endpoint

### Paesi

| Metodo | URL                | Descrizione            | Body (JSON)        |
|--------|--------------------|-------------------------|---------------------|
| GET    | `/api/paesi`       | Elenco paesi            | -                   |
| GET    | `/api/paesi/{id}`  | Dettaglio paese         | -                   |
| POST   | `/api/paesi`       | Crea paese              | `{ "nome": "Vietnam" }` |
| PUT    | `/api/paesi/{id}`  | Aggiorna paese          | `{ "nome": "Vietnam" }` |
| DELETE | `/api/paesi/{id}`  | Elimina paese           | -                   |

### Viaggi

| Metodo | URL                 | Descrizione              | Body (JSON) |
|--------|---------------------|---------------------------|-------------|
| GET    | `/api/viaggi`       | Elenco viaggi (con filtri) | -          |
| GET    | `/api/viaggi/{id}`  | Dettaglio viaggio          | -          |
| POST   | `/api/viaggi`       | Crea viaggio                | `{ "posti_disponibili": 4, "paesi": [1, 2] }` |
| PUT    | `/api/viaggi/{id}`  | Aggiorna viaggio            | `{ "posti_disponibili": 2, "paesi": [1] }` |
| DELETE | `/api/viaggi/{id}`  | Elimina viaggio             | -          |

**Filtri su `GET /api/viaggi`** (query string, combinabili):
- `?paese_id=2` → solo i viaggi che coinvolgono il paese con id 2
- `?posti_disponibili=4` → solo i viaggi con esattamente 4 posti disponibili
- `?posti_min=1` → solo i viaggi con almeno 1 posto disponibile (utile per le
  offerte last minute: niente viaggi già esauriti)

Esempio: `GET /api/viaggi?paese_id=1&posti_min=1`

## Status code usati

- `200 OK` — richiesta GET/PUT riuscita
- `201 Created` — risorsa creata con POST
- `204 No Content` — DELETE riuscita
- `404 Not Found` — risorsa/rotta non trovata
- `405 Method Not Allowed` — rotta esistente ma metodo HTTP non previsto
- `409 Conflict` — nome paese già esistente
- `422 Unprocessable Entity` — dati mancanti o non validi
- `500 Internal Server Error` — errore imprevisto lato server

## Esempi di test (curl)

```bash
# Crea un paese
curl -X POST http://localhost:8000/api/paesi \
  -H "Content-Type: application/json" \
  -d '{"nome":"Vietnam"}'

# Elenco paesi
curl http://localhost:8000/api/paesi

# Crea un viaggio che coinvolge i paesi con id 1 e 2
curl -X POST http://localhost:8000/api/viaggi \
  -H "Content-Type: application/json" \
  -d '{"posti_disponibili":4,"paesi":[1,2]}'

# Viaggi last minute: almeno 1 posto disponibile
curl "http://localhost:8000/api/viaggi?posti_min=1"

# Aggiorna un viaggio
curl -X PUT http://localhost:8000/api/viaggi/1 \
  -H "Content-Type: application/json" \
  -d '{"posti_disponibili":1,"paesi":[1]}'

# Elimina un paese
curl -X DELETE http://localhost:8000/api/paesi/3
```

## Struttura del progetto

```
orizon-api/
├── composer.json
├── migrations.sql
├── .env.example
├── public/
│   ├── index.php          <- front controller
│   └── .htaccess
└── src/
    ├── routes.php          <- definizione delle rotte
    ├── Config/
    │   └── Database.php    <- connessione PDO
    ├── Core/
    │   ├── Router.php
    │   ├── Request.php
    │   └── Response.php
    ├── Controllers/
    │   ├── PaeseController.php
    │   └── ViaggioController.php
    └── Repositories/
        ├── PaeseRepository.php
        └── ViaggioRepository.php
```
