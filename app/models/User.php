<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table = "users"; 

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE username = :username AND password = :password 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
