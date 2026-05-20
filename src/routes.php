<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use App\Controllers\BookController;

return function (App $app): void {
    $app->get('/', function (Request $r, Response $s) {
        $s->getBody()->write(json_encode([
            'name' => 'Books REST API',
            'version' => '1.0.0',
        ]));
        return $s->withHeader('Content-Type', 'application/json');
    });

    $app->group('/api', function ($g) {
        $g->get('/books', [BookController::class, 'index']);
        $g->get('/books/{id}', [BookController::class, 'show']);
        $g->post('/books', [BookController::class, 'create']);
        $g->put('/books/{id}', [BookController::class, 'update']);
        $g->delete('/books/{id}', [BookController::class, 'delete']);
    });
};
