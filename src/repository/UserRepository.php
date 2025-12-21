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

    public function createSession(int $userId, string $token, int $expiresInSeconds = 3600): bool
    {
        try {
            $this->beginTransaction();

            $deleteQuery = "DELETE FROM user_sessions WHERE user_id = :user_id";
            $this->execute($deleteQuery, ['user_id' => $userId]);

            $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);

            $insertQuery = "
                INSERT INTO user_sessions (user_id, session_token, expires_at)
                VALUES (:user_id, :token, :expires_at)
            ";

            $this->execute($insertQuery, [
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt
            ]);

            $this->commit();
            return true;

        } catch (Exception $e) {
            if ($this->inTransaction()) {
                $this->rollback();
            }
            return false;
        }
    }

    public function getUserBySessionToken(string $token): ?User
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
            INNER JOIN user_sessions s ON u.id = s.user_id
            WHERE s.session_token = :token
              AND s.expires_at > NOW()
            LIMIT 1
        ";

        $result = $this->fetchOne($query, ['token' => $token]);

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

    public function deleteSession(int $userId): bool
    {
        $query = "DELETE FROM user_sessions WHERE user_id = :user_id";
        return $this->execute($query, ['user_id' => $userId]);
    }

    public function deleteSessionByToken(string $token): bool
    {
        $query = "DELETE FROM user_sessions WHERE session_token = :token";
        return $this->execute($query, ['token' => $token]);
    }

    public function cleanupExpiredSessions(): int
    {
        $query = "DELETE FROM user_sessions WHERE expires_at < NOW()";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function extendSession(string $token, int $expiresInSeconds = 3600): bool
    {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);
        
        $query = "
            UPDATE user_sessions 
            SET expires_at = :expires_at 
            WHERE session_token = :token
        ";

        return $this->execute($query, [
            'expires_at' => $expiresAt,
            'token' => $token
        ]);
    }
}