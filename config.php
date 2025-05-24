<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'anketa'; 
    private $username = 'root';
    private $password = '';
    private $port = 3307; 

    public $conn;

    public function connect() {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port
        );

        if ($this->conn->connect_error) {
            die("Ошибка подключения: " . $this->conn->connect_error);
        }

        return $this->conn;
    }
}

$db = new Database();
$conn = $db->connect();
?>
