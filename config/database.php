<?php
/**
 * Database Configuration
 * 
 * Update these variables with your actual database credentials.
 */

// Railway automatically provides these environment variables when you link a MySQL database.
// If they don't exist (like on your local PC), it falls back to your local XAMPP credentials!
$host = getenv('MYSQLHOST') ?: 'localhost';
$db   = getenv('MYSQLDATABASE') ?: 'ip_tracking_db';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = getenv('MYSQLPORT') ?: '3306';

// If Railway provides the single MYSQL_URL string, parse it automatically!
if (getenv('MYSQL_URL')) {
    $url = parse_url(getenv('MYSQL_URL'));
    $host = $url['host'];
    $user = $url['user'];
    $pass = isset($url['pass']) ? $url['pass'] : '';
    $port = isset($url['port']) ? $url['port'] : '3306';
    $db   = substr($url['path'], 1); // remove the leading slash
}

define('DB_HOST', $host);
define('DB_NAME', $db);
define('DB_USER', $user);
define('DB_PASS', $pass);
define('DB_PORT', $port);

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
