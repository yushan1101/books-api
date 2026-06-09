<?php
namespace App\Repositories;

use PDO;

final class BookRepository
{
    public function __construct(private PDO $pdo) {}

    public function all(string $q = '', int $limit = 0): array {
        $sql = 'SELECT * FROM books';
        $args = [];
        if ($q !== '') {
            $sql .= ' WHERE title LIKE :qtitle OR author LIKE :qauthor';
            $args[':qtitle'] = '%' . $q . '%';
            $args[':qauthor'] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY id ASC';
        if ($limit > 0) $sql .= ' LIMIT ' . max(1, $limit);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->pdo->prepare('SELECT * FROM books WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $b): int {
        $sql = 'INSERT INTO books (title, author, year, genre)
                VALUES (:title, :author, :year, :genre)';
        $this->pdo->prepare($sql)->execute([
            ':title'  => trim($b['title']),
            ':author' => trim($b['author']),
            ':year'   => (int)$b['year'],
            ':genre'  => trim($b['genre'] ?? 'Uncategorised'),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $b): int {
        $sets = []; $args = [':id' => $id];
        foreach (['title','author','genre'] as $f) {
            if (array_key_exists($f, $b)) {
                $sets[] = "$f=:$f";
                $args[":$f"] = trim($b[$f]);
            }
        }
        if (array_key_exists('year', $b)) {
            $sets[] = 'year=:year';
            $args[':year'] = (int)$b['year'];
        }
        if (!$sets) return 0;
        $sql = 'UPDATE books SET ' . implode(',', $sets) . ' WHERE id=:id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        return $stmt->rowCount();
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() === 1;
    }
}