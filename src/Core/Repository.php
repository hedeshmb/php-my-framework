<?php

namespace App\Core;

use App\Controllers\UserController;

class Repository
{
    private ?\PDO $pdo = null;

    public function __construct()
    {
        $host = 'db';  // Use the service name as the hostname
        $dbname = 'app_db';
        $username = 'app_user';
        $password = 'app_password';
        $port = 3306;  // Default MySQL port inside the container

        try {
            $this->pdo = new \PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->build();
        } catch (\PDOException $e) {
            var_export("Connection failed: " . $e->getMessage());
            exit;
        }
    }

    public function build()
    {
        $sql_create_user_table = "CREATE TABLE IF NOT EXISTS users (
                                    id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    username VARCHAR(512)  NOT NULL UNIQUE,
                                    password VARCHAR(512) NOT NULL,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                )";
        $this->pdo->query($sql_create_user_table);

        $sql_create_domain_table = "CREATE TABLE IF NOT EXISTS domains (
                                    id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    url VARCHAR(512) NOT NULL,
                                    description VARCHAR(512) NOT NULL,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                )";
        $this->pdo->query($sql_create_domain_table);

        $sql_create_link_table = "CREATE TABLE IF NOT EXISTS links (
                                    id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    domain_id INT(12) NOT NULL,
                                    title VARCHAR(512),
                                    source_link VARCHAR(512) NOT null,
                                    short_link VARCHAR(512) NOT null,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                )";
        $this->pdo->query($sql_create_link_table);

        $this->storeUser();
    }

    private function storeUser()
    {
        try {
            $sql = "INSERT INTO users (username, password) VALUES ('test', md5('test123'))";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
        } catch (\PDOException $e) {

        }
    }

    public function selectUser($username, $password)
    {
        $password = md5($password);
        try {
            $sql = "SELECT * FROM users WHERE username = :username AND password = :password";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
        }
    }

    public function storeDomain(string $url, string $description)
    {
        $sql = "INSERT INTO domains (url, description) VALUES (:url, :description)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        $lastInsertId = $this->pdo->lastInsertId();
        $this->pdo = null;

        return $lastInsertId;
    }

    public function storeLink(string $title, string $source_link, int $domain_id)
    {
        try {
            $this->pdo->beginTransaction();

            $short_link = $this->generateShortLink();

            $sql = "INSERT INTO links (title, source_link, short_link, domain_id) VALUES (:title, :source_link, :short_link, :domain_id)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':source_link', $source_link);
            $stmt->bindParam(':short_link', $short_link);
            $stmt->bindParam(':domain_id', $domain_id);
            $stmt->execute();

            $lastInsertId = $this->pdo->lastInsertId();
            $this->pdo->commit();
            $this->pdo = null;

            return $lastInsertId;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function updateLink(string $title, string $source_link, int $domain_id, int $id)
    {
        if (empty($this->selectLink($id))) {
            return false;
        }

        $short_link = $this->generateShortLink();

        $sql = "UPDATE links SET title = :title, source_link = :source_link , short_link = :short_link, domain_id = :domain_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':source_link', $source_link);
        $stmt->bindParam(':short_link', $short_link);
        $stmt->bindParam(':domain_id', $domain_id);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $this->pdo = null;
        return true;
    }

    public function deleteLink(int $id)
    {
        $sql = "DELETE FROM links WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $this->pdo = null;
    }

    public function selectLink(int $id = null)
    {
        if ($id == null) {
            $sql = "SELECT * FROM links";
            $stmt = $this->pdo->prepare($sql);
        } else {
            $sql = "SELECT * FROM links WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    private function generateShortLink()
    {
        $length = 8;
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}