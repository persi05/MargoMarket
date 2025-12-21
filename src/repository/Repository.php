<?php

require_once __DIR__ . '/../../Database.php';

class Repository
{
    protected Database $database;
    protected PDO $connection;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->connection = $this->database->connect();
    }

    protected function getConnection(): PDO
    {
        return $this->connection;
    }

    protected function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function fetchOne(string $query, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function execute(string $query, array $params = []): bool
    {
        $stmt = $this->connection->prepare($query);
        return $stmt->execute($params);
    }

    protected function getLastInsertId(): int
    {
        return (int) $this->connection->lastInsertId();
    }

    protected function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    protected function commit(): bool
    {
        return $this->connection->commit();
    }

    protected function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    protected function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}