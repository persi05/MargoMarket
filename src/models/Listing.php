<?php


class Listing
{
    private ?int $id;
    private int $userId;
    private string $itemName;
    private int $itemTypeId;
    private string $itemTypeName;
    private int $level;
    private int $rarityId;
    private string $rarityName;
    private int $price;
    private int $currencyId;
    private string $currencyName;
    private int $serverId;
    private string $serverName;
    private string $contact;
    private int $statusId;
    private string $statusName;
    private ?string $imageUrl;
    private ?string $createdAt;
    private ?string $soldAt;
    private ?string $userEmail;

    public function __construct(
        string $itemName,
        int $itemTypeId,
        int $level,
        int $rarityId,
        int $price,
        int $currencyId,
        int $serverId,
        string $contact,
        int $userId,
        int $statusId = 1,
        ?int $id = null,
        string $itemTypeName = '',
        string $rarityName = '',
        string $currencyName = '',
        string $serverName = '',
        string $statusName = 'active',
        ?string $imageUrl = null,
        ?string $createdAt = null,
        ?string $soldAt = null,
        ?string $userEmail = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->itemName = $itemName;
        $this->itemTypeId = $itemTypeId;
        $this->itemTypeName = $itemTypeName;
        $this->level = $level;
        $this->rarityId = $rarityId;
        $this->rarityName = $rarityName;
        $this->price = $price;
        $this->currencyId = $currencyId;
        $this->currencyName = $currencyName;
        $this->serverId = $serverId;
        $this->serverName = $serverName;
        $this->contact = $contact;
        $this->statusId = $statusId;
        $this->statusName = $statusName;
        $this->imageUrl = $imageUrl;
        $this->createdAt = $createdAt;
        $this->soldAt = $soldAt;
        $this->userEmail = $userEmail;
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getItemName(): string { return $this->itemName; }
    public function getItemTypeId(): int { return $this->itemTypeId; }
    public function getItemTypeName(): string { return $this->itemTypeName; }
    public function getLevel(): int { return $this->level; }
    public function getRarityId(): int { return $this->rarityId; }
    public function getRarityName(): string { return $this->rarityName; }
    public function getPrice(): int { return $this->price; }
    public function getCurrencyId(): int { return $this->currencyId; }
    public function getCurrencyName(): string { return $this->currencyName; }
    public function getServerId(): int { return $this->serverId; }
    public function getServerName(): string { return $this->serverName; }
    public function getContact(): string { return $this->contact; }
    public function getStatusId(): int { return $this->statusId; }
    public function getStatusName(): string { return $this->statusName; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getSoldAt(): ?string { return $this->soldAt; }
    public function getUserEmail(): ?string { return $this->userEmail; }

    public function setId(int $id): void { $this->id = $id; }
    public function setStatusId(int $statusId): void { $this->statusId = $statusId; }
    public function setStatusName(string $statusName): void { $this->statusName = $statusName; }
    public function setSoldAt(?string $soldAt): void { $this->soldAt = $soldAt; }


    public function isActive(): bool
    {
        return $this->statusName === 'active';
    }

    public function isSold(): bool
    {
        return $this->statusName === 'sold';
    }

    public function getFormattedPrice(): string
    {
        if ($this->currencyName === 'w grze') {
            return number_format($this->price, 0, ',', ' ').' złota';
        }
        return number_format($this->price, 0, ',', ' ').' PLN';
    }

    public function getShortPrice(): string
    {
        $price = $this->price;
        $suffix = '';

        if ($price >= 1000000) {
            $price = $price / 1000000;
            $suffix = 'm';
        } elseif ($price >= 1000) {
            $price = $price / 1000;
            $suffix = 'k';
        }

        $formatted = number_format($price, 1, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');
        
        return $formatted.$suffix.($this->currencyName === 'pln' ? ' PLN' : '');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'user_email' => $this->userEmail,
            'item_name' => $this->itemName,
            'item_type' => $this->itemTypeName,
            'level' => $this->level,
            'rarity' => $this->rarityName,
            'price' => $this->price,
            'currency' => $this->currencyName,
            'server' => $this->serverName,
            'contact' => $this->contact,
            'status' => $this->statusName,
            'image_url' => $this->imageUrl,
            'created_at' => $this->createdAt,
            'sold_at' => $this->soldAt,
        ];
    }
}