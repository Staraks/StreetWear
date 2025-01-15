<?php

// Класс для подключения к базе данных
class DBConnection {
    
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;

    public function __construct(string $host, string $username, string $password, string $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->connection = null;
    }

    public function connect() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            if ($this->connection->connect_error) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ошибка подключения: ' . $this->connection->connect_error]);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Исключение: ' . $e->getMessage()]);
            exit;
        }
    }

    public function getConnection(): mysqli {
        return $this->connection;
    }

    public function disconnect() {
        try {
            if ($this->connection) {
                $this->connection->close();
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
