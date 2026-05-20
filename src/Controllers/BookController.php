<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class BookController
{
    private static array $books = [];

    private static function bootstrap(): void
    {
        if (self::$books === []) {
            self::$books = require __DIR__ . '/../Data/books.php';
        }
    }

    /** GET /api/books */
    public function index(Request $req, Response $res): Response
    {
        self::bootstrap();
        $params = $req->getQueryParams();
        $items = self::$books;

        if (!empty($params['q'])) {
            $q = mb_strtolower((string)$params['q']);
            $items = array_values(array_filter($items, fn($b) =>
                str_contains(mb_strtolower($b['title']), $q) ||
                str_contains(mb_strtolower($b['author']), $q)
            ));
        }

        if (!empty($params['limit'])) {
            $items = array_slice($items, 0, max(1, (int)$params['limit']));
        }

        return $this->json($res, ['count' => count($items), 'data' => $items]);
    }

    /** GET /api/books/{id} */
    public function show(Request $req, Response $res, array $args): Response
    {
        self::bootstrap();
        $id = (int)($args['id'] ?? 0);
        $book = $this->findById($id);
        return $book
            ? $this->json($res, $book)
            : $this->json($res, ['error' => "Book {$id} not found"], 404);
    }

    /** POST /api/books */
    public function create(Request $req, Response $res): Response
    {
        self::bootstrap();
        $body = (array)($req->getParsedBody() ?? []);
        $errors = $this->validate($body, requireAll: true);

        if (!empty($errors)) {
            return $this->json($res, ['errors' => $errors], 400);
        }

        $id = (max(array_column(self::$books, 'id') ?: [0])) + 1;
        $book = [
            'id'     => $id,
            'title'  => trim($body['title']),
            'author' => trim($body['author']),
            'year'   => (int)$body['year'],
            'genre'  => trim((string)($body['genre'] ?? 'Uncategorised')),
        ];

        self::$books[] = $book;
        return $this->json($res, ['message' => 'Book created', 'data' => $book], 201)
            ->withHeader('Location', '/api/books/' . $id);
    }

    /** PUT /api/books/{id} */
    public function update(Request $req, Response $res, array $args): Response
    {
        self::bootstrap();
        $id = (int)($args['id'] ?? 0);
        $idx = $this->findIndexById($id);

        if ($idx === null) return $this->json($res, ['error' => "Book {$id} not found"], 404);

        $body = (array)($req->getParsedBody() ?? []);
        $errors = $this->validate($body, requireAll: false);
        if (!empty($errors)) return $this->json($res, ['errors' => $errors], 400);

        $current = self::$books[$idx];
        foreach (['title', 'author', 'genre'] as $k) {
            if (array_key_exists($k, $body)) $current[$k] = trim((string)$body[$k]);
        }
        if (array_key_exists('year', $body)) $current['year'] = (int)$body['year'];

        self::$books[$idx] = $current;
        return $this->json($res, ['message' => 'Book updated', 'data' => $current]);
    }

    /** DELETE /api/books/{id} */
    public function delete(Request $req, Response $res, array $args): Response
    {
        self::bootstrap();
        $id = (int)($args['id'] ?? 0);
        $idx = $this->findIndexById($id);

        if ($idx === null) return $this->json($res, ['error' => "Book {$id} not found"], 404);

        $deleted = self::$books[$idx];
        array_splice(self::$books, $idx, 1);
        return $this->json($res, ['message' => 'Book deleted', 'data' => $deleted]);
    }

    private function findById(int $id): ?array {
        foreach (self::$books as $b) if ($b['id'] === $id) return $b;
        return null;
    }

    private function findIndexById(int $id): ?int {
        foreach (self::$books as $i => $b) if ($b['id'] === $id) return $i;
        return null;
    }

    private function validate(array $body, bool $requireAll): array {
        $errors = [];
        $rules = [
            'title'  => fn($v) => is_string($v) && trim($v) !== '',
            'author' => fn($v) => is_string($v) && trim($v) !== '',
            'year'   => fn($v) => is_numeric($v) && (int)$v >= 1000 && (int)$v <= (int)date('Y'),
        ];
        foreach ($rules as $f => $check) {
            if ($requireAll && !array_key_exists($f, $body)) { $errors[$f] = "$f is required"; continue; }
            if (array_key_exists($f, $body) && !$check($body[$f])) $errors[$f] = "$f is invalid";
        }
        return $errors;
    }

    private function json(Response $res, mixed $data, int $status = 200): Response {
        $res->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $res->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }
}