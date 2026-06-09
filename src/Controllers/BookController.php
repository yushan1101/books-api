<?php
namespace App\Controllers;

use App\Repositories\BookRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class BookController
{
    public function __construct(private BookRepository $books) {}

    public function index(Request $r, Response $s): Response {
        $p = $r->getQueryParams();
        $rows = $this->books->all((string)($p['q'] ?? ''), (int)($p['limit'] ?? 0));
        return $this->json($s, ['count' => count($rows), 'data' => $rows]);
    }

    public function show(Request $r, Response $s, array $a): Response {
        $book = $this->books->find((int)$a['id']);
        return $book
            ? $this->json($s, $book)
            : $this->json($s, ['error' => 'not found'], 404);
    }

    public function create(Request $r, Response $s): Response {
        $body = (array)$r->getParsedBody();
        $errors = $this->validate($body, true);
        if ($errors) return $this->json($s, ['errors' => $errors], 400);
        $id = $this->books->create($body);
        return $this->json($s, ['message' => 'Book created', 'data' => $this->books->find($id)], 201)
            ->withHeader('Location', '/api/books/' . $id);
    }

    public function update(Request $r, Response $s, array $a): Response {
        $id = (int)$a['id'];
        $book = $this->books->find($id);
        if (!$book) return $this->json($s, ['error' => 'not found'], 404);
        $body = (array)$r->getParsedBody();
        $errors = $this->validate($body, false);
        if ($errors) return $this->json($s, ['errors' => $errors], 400);
        $this->books->update($id, $body);
        return $this->json($s, ['message' => 'Book updated', 'data' => $this->books->find($id)]);
    }

    public function delete(Request $r, Response $s, array $a): Response {
        $id = (int)$a['id'];
        $book = $this->books->find($id);
        if (!$book) return $this->json($s, ['error' => 'not found'], 404);
        $this->books->delete($id);
        return $this->json($s, ['message' => 'Book deleted', 'data' => $book]);
    }

    private function validate(array $b, bool $requireAll): array {
        $errors = [];
        $rules = [
            'title'  => fn($v) => is_string($v) && trim($v) !== '',
            'author' => fn($v) => is_string($v) && trim($v) !== '',
            'year'   => fn($v) => is_numeric($v) && (int)$v >= 1000 && (int)$v <= (int)date('Y'),
        ];
        foreach ($rules as $f => $check) {
            if ($requireAll && !array_key_exists($f, $b)) { $errors[$f] = "$f is required"; continue; }
            if (array_key_exists($f, $b) && !$check($b[$f])) $errors[$f] = "$f is invalid";
        }
        return $errors;
    }

    private function json(Response $r, mixed $data, int $code = 200): Response {
        $r->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $r->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($code);
    }
}