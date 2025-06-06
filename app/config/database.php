<?php
class Database {
    private static $instance = null;
    private $connection;
    private function __construct() {
        $this->connection = new mysqli('localhost', 'root', '', 'db_taskflow');
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
    public static function getConnection() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
?>