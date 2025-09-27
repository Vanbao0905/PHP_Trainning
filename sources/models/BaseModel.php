<?php
require_once 'configs/database.php';

abstract class BaseModel {
    // PDO connection
    protected static $_connection;

    public function __construct() {
        if (!isset(self::$_connection)) {
            try {
                self::$_connection = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Query raw SQL (not recommended unless safe)
     */
    protected function query($sql, $params = []) {
        $stmt = self::$_connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Select statement
     */
    protected function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Delete statement
     */
    protected function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Update statement
     */
    protected function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Insert statement
     */
    protected function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return self::$_connection->lastInsertId();
    }
}
