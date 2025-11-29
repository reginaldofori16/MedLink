<?php
/**
 * Database Connection Class
 * Handles all database connections for MedLink platform
 */

class DatabaseConnection {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        // Database configuration
        $this->host = 'localhost';
        $this->dbname = 'medlink_db';
        $this->username = 'root';
        $this->password = '';
        
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    /**
     * Get the database connection
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Close the database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>

