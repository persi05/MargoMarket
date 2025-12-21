<?php

class User
{
    private ?int $id;
    private string $email;
    private string $password;
    private int $roleId;
    private string $roleName;
    private ?string $createdAt;

    public function __construct(
        string $email,
        string $password = '',
        int $roleId = 1,
        string $roleName = 'user',
        ?int $id = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getRoleName(): string
    {
        return $this->roleName;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRoleId(int $roleId): void
    {
        $this->roleId = $roleId;
    }

    public function setRoleName(string $roleName): void
    {
        $this->roleName = $roleName;
    }

    public function isAdmin(): bool
    {
        return $this->roleName === 'admin';
    }

    public function isUser(): bool
    {
        return $this->roleName === 'user';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role_id' => $this->roleId,
            'role_name' => $this->roleName,
            'created_at' => $this->createdAt,
        ];
    }
}