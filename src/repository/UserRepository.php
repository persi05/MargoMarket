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

}