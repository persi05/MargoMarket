<?php

require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository extends Repository
{

    public function getUserByEmail(string $email): ?User
    {
        $query = "
            SELECT 
                u.id,
                u.email,
                u.password,
                u.role_id,
                r.name as role_name,
                u.created_at
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email
            LIMIT 1
        ";

        $result = $this->fetchOne($query, ['email' => $email]);

        if (!$result) {
            return null;
        }

        return new User(
            $result['email'],
            $result['password'],
            (int) $result['role_id'],
            $result['role_name'],
            (int) $result['id'],
            $result['created_at']
        );
    }

    public function getUserById(int $id): ?User
    {
        $query = "
            SELECT 
                u.id,
                u.email,
                u.password,
                u.role_id,
                r.name as role_name,
                u.created_at
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
            LIMIT 1
        ";

        $result = $this->fetchOne($query, ['id' => $id]);

        if (!$result) {
            return null;
        }

        return new User(
            $result['email'],
            $result['password'],
            (int) $result['role_id'],
            $result['role_name'],
            (int) $result['id'],
            $result['created_at']
        );
    }

    public function createUser(string $email, string $password): ?int
    {
        if ($this->getUserByEmail($email) !== null) {
            return null;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $roleQuery = "SELECT id FROM roles WHERE name = 'user' LIMIT 1";
        $roleResult = $this->fetchOne($roleQuery);
        $roleId = $roleResult ? (int) $roleResult['id'] : 1;

        $query = "
            INSERT INTO users (email, password, role_id)
            VALUES (:email, :password, :role_id)
        ";

        $success = $this->execute($query, [
            'email' => $email,
            'password' => $hashedPassword,
            'role_id' => $roleId
        ]);

        return $success ? $this->getLastInsertId() : null;
    }

    public function verifyPassword(User $user, string $password): bool
    {
        return password_verify($password, $user->getPassword());
    }

        public function emailExists(string $email): bool
    {
        $query = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $result = $this->fetchOne($query, ['email' => $email]);
        return $result && $result['count'] > 0;
    }

    public function getAllUsers(): array
    {
        $query = "
            SELECT 
                u.id,
                u.email,
                u.role_id,
                r.name as role_name,
                u.created_at,
                COUNT(l.id) as listings_count
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            LEFT JOIN listings l ON u.id = l.user_id
            GROUP BY u.id, u.email, u.role_id, r.name, u.created_at
            ORDER BY u.created_at DESC
        ";

        $results = $this->fetchAll($query);
        $users = [];

        foreach ($results as $row) {
            $user = new User(
                $row['email'],
                '',
                (int) $row['role_id'],
                $row['role_name'],
                (int) $row['id'],
                $row['created_at']
            );
            $users[] = $user;
        }

        return $users;
    }

}