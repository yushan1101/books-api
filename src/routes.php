<?php
use App\Controllers\BookController;
use App\Database;
use App\Repositories\BookRepository;
use Slim\App;

return function (App $app): void {

    // Handle OPTIONS preflight for ALL routes
    $app->options('/{routes:.+}', function (Request $req, Response $res) {
        return $res;
    });

    $controller = new BookController(new BookRepository(Database::get()));

    $app->group('/api', function ($g) use ($controller) {
        $g->get   ('/books',      [$controller, 'index']);
        $g->get   ('/books/{id}', [$controller, 'show']);
        $g->post  ('/books',      [$controller, 'create']);
        $g->put   ('/books/{id}', [$controller, 'update']);
        $g->delete('/books/{id}', [$controller, 'delete']);
    });
};