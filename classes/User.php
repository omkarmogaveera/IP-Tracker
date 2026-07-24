<?php
/**
 * User Class
 * Handles user authentication and management
 */
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Check if email or username already exists
     */
    public function userExists() {
        $query = "SELECT id, username, email, password_hash 
                  FROM " . $this->table_name . " 
                  WHERE email = ? OR username = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->username = htmlspecialchars(strip_tags($this->username));

        $stmt->bindParam(1, $this->email);
        $stmt->bindParam(2, $this->username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->password_hash = $row['password_hash'];
            return true;
        }
        return false;
    }

    /**
     * Create new user
     */
    public function signup() {
        if ($this->userExists()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET username = :username, email = :email, password_hash = :password_hash";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = password_hash($this->password_hash, PASSWORD_BCRYPT);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $this->password_hash);

        try {
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
        } catch (PDOException $e) {
            error_log("User Signup Error: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Login user
     */
    public function login($username_or_email, $password) {
        $query = "SELECT id, username, email, password_hash 
                  FROM " . $this->table_name . " 
                  WHERE email = ? OR username = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $username_or_email = htmlspecialchars(strip_tags($username_or_email));
        $stmt->bindParam(1, $username_or_email);
        $stmt->bindParam(2, $username_or_email);
        try {
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $row['password_hash'])) {
                    $this->id = $row['id'];
                    $this->username = $row['username'];
                    $this->email = $row['email'];
                    return true;
                }
            }
        } catch (PDOException $e) {
            error_log("User Login Error: " . $e->getMessage());
        }
        return false;
    }
}
?>
