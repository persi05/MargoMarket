<?php

require_once "config.php";

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private string $username;
    private string $password;
    private string $host;
    private string $database;

    private function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connect(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        try {
            $this->connection = new PDO(
                "pgsql:host={$this->host};port=5432;dbname={$this->database}",
                $this->username,
                $this->password,
                [
                    "sslmode" => "prefer",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );

            return $this->connection;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Database connection failed");
        }
    }
}
