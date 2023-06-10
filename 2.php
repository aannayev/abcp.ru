<?php

namespace Gateway;

use PDO;
use PDOException;

class User
{
    private $pdo;

    public function __construct()
    {
        $dsn = 'mysql:dbname=db;host=127.0.0.1';
        $user = 'dbuser';
        $password = 'dbpass';

        try {
            $this->pdo = new PDO($dsn, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getUsers(int $ageFrom): array
    {
        $stmt = $this->pdo->prepare("SELECT id, name, lastName, `from`, age, settings FROM Users WHERE age > :ageFrom LIMIT :limit");
        $stmt->bindValue(':ageFrom', $ageFrom, PDO::PARAM_INT);
        $stmt->bindValue(':limit', \Manager\User::limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
        foreach ($rows as $row) {
            $settings = json_decode($row['settings'], true);
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'lastName' => $row['lastName'],
                'from' => $row['from'],
                'age' => $row['age'],
                'key' => isset($settings['key']) ? $settings['key'] : null,
            ];
        }

        return $users;
    }

    public function getUserByName(string $name): array
    {
        $stmt = $this->pdo->prepare("SELECT id, name, lastName, `from`, age FROM Users WHERE name = :name");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: [];
    }

    public function addUser(string $name, string $lastName, int $age): string
    {
        $stmt = $this->pdo->prepare("INSERT INTO Users (name, lastName, age) VALUES (:name, :lastName, :age)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':lastName', $lastName, PDO::PARAM_STR);
        $stmt->bindValue(':age', $age, PDO::PARAM_INT);
        $stmt->execute();

        return $this->pdo->lastInsertId();
    }
}

