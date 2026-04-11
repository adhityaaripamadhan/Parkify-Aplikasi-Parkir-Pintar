<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table = "users"; 

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // fungsi login
    public function login($username, $password) {
        // query untuk mencari user berdasarkan username dan password 
        $query = "SELECT * FROM {$this->table} 
                  WHERE username = :username AND password = :password 
                  LIMIT 1";

        // prepare statment untuk mencegah sql injection
        $stmt = $this->conn->prepare($query);

        // binding parameter username dan password ke query
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);

        $stmt->execute();

        // mengambil hasil data user (jika ditemukan)
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
