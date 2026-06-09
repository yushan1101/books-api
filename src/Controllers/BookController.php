<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class BookController
{
    private static array $books = [];
    private static bool $loaded = false;

    private static function storeFile(): string {
        $dir = __DIR__ . '/../../var';
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        return $dir . DIRECTORY_SEPARATOR . 'books.json';
    }

    private static function load(): void {
        if (self::$loaded) return;
        $file = self::storeFile();
        if (is_file($file)) {
            $data = json_decode((string)@file_get_contents($file), true);
            if (is_array($data)) { self::$books = $data; self::$loaded = true; return; }
        }
        self::$books = require __DIR__ . '/../Data/books.php';
        self::$loaded = true;
        self::save();
    }

    private static function save(): void {
        @file_put_contents(
            self::storeFile(),
            json_encode(self::$books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    public function index(Request $req, Response $res): Response {
        self::load();
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

    public function show(Request $req, Response $res, array $args): Response {
        self::load();
        $id = (int)($args['id'] ?? 0);
        $book = $this->findById($id);
        return $book
            ? $this->json($res, $book)
            : $this->json($res, ['error' => "Book {$id} not found"], 404);
    }

    public function create(Request $req, Response $res): Response {
        self::load();
        $body = (array)($req->getParsedBody() ?? []);
        $errors = $this->validate($body, requireAll: true);
        if (!empty($errors)) return $this->json($res, ['errors' => $errors], 400);
        $id = (max(array_column(self::$books, 'id') ?: [0])) + 1;
        $book = [
            'id'     => $id,
            'title'  => trim($body['title']),
            'author' => trim($body['author']),
            'year'   => (int)$body['year'],
            'genre'  => trim((string)($body['genre'] ?? 'Uncategorised')),
        ];
        self::$books[] = $book;
        self::save();
        return $this->json($res, ['message' => 'Book created', 'data' => $book], 201)
            ->withHeader('Location', '/api/books/' . $id);
    }

    public function update(Request $req, Response $res, array $args): Response {
        self::load();
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
        self::save();
        return $this->json($res, ['message' => 'Book updated', 'data' => $current]);
    }

    public function delete(Request $req, Response $res, array $args): Response {
        self::load();
        $id = (int)($args['id'] ?? 0);
        $idx = $this->findIndexById($id);
        if ($idx === null) return $this->json($res, ['error' => "Book {$id} not found"], 404);
        $deleted = self::$books[$idx];
        array_splice(self::$books, $idx, 1);
        self::save();
        return $this->json($res, ['message' => 'Book deleted', 'data' => $deleted]);
    }

    public function reset(Request $req, Response $res): Response {
        self::$books = require __DIR__ . '/../Data/books.php';
        self::$loaded = true;
        self::save();
        return $this->json($res, ['message' => 'Data reset to seed', 'count' => count(self::$books)]);
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