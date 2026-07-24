<?php
/**
 * Database Configuration
 * 
 * Update these variables with your actual database credentials.
 */

// Railway automatically provides these environment variables when you link a MySQL database.
// If they don't exist (like on your local PC), it falls back to your local XAMPP credentials!
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'ip_tracking_db');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

// Some Railway databases run on a specific port, so let's capture that just in case
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . DB_PORT . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Ensure data is returned as UTF-8
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo json_encode([
                "status" => "error", 
                "message" => "Database connection error: " . $exception->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
?>
