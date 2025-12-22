<?php

require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Listing.php';

class ListingRepository extends Repository
{

    public function getActiveListings(int $limit = 50, ?int $lastId = null): array
    {
        $query = "SELECT * FROM active_listings_view ";
        
        if ($lastId !== null) {
            $query .= "WHERE id > :last_id ";
        }

        $query .= "ORDER BY id ASC LIMIT :limit";

        $stmt = $this->getConnection()->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        if ($lastId !== null) {
            $stmt->bindValue(':last_id', $lastId, PDO::PARAM_INT);
        }

        $stmt->execute();
        
        return $this->mapToListings($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function searchListings(
        ?string $searchTerm = null,
        ?int $serverId = null,
        int $minLevel = 0,
        int $maxLevel = 300,
        ?int $itemTypeId = null,
        ?int $rarityId = null,
        ?int $currencyId = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        $query = "
            SELECT * FROM search_listings(
                :search_term,
                :server_id,
                :min_level,
                :max_level,
                :item_type_id,
                :rarity_id,
                :currency_id,
                :limit,
                :offset
            )
        ";

        $results = $this->fetchAll($query, [
            'search_term' => $searchTerm,
            'server_id' => $serverId,
            'min_level' => $minLevel,
            'max_level' => $maxLevel,
            'item_type_id' => $itemTypeId,
            'rarity_id' => $rarityId,
            'currency_id' => $currencyId,
            'limit' => $limit,
            'offset' => $offset
        ]);

        return $this->mapToListings($results);
    }

    public function getListingById(int $id): ?Listing
    {
        $query = "
            SELECT 
                l.id,
                l.user_id,
                l.item_name,
                l.item_type_id,
                it.name as item_type,
                l.level,
                l.rarity_id,
                r.name as rarity,
                l.price,
                l.currency_id,
                c.name as currency,
                l.server_id,
                s.name as server,
                l.contact,
                l.status_id,
                ls.name as status,
                l.image_url,
                l.created_at,
                l.sold_at,
                u.email as user_email
            FROM listings l
            INNER JOIN item_types it ON l.item_type_id = it.id
            INNER JOIN rarities r ON l.rarity_id = r.id
            INNER JOIN currencies c ON l.currency_id = c.id
            INNER JOIN servers s ON l.server_id = s.id
            INNER JOIN listing_statuses ls ON l.status_id = ls.id
            INNER JOIN users u ON l.user_id = u.id
            WHERE l.id = :id
            LIMIT 1
        ";

        $result = $this->fetchOne($query, ['id' => $id]);

        if (!$result) {
            return null;
        }

        return $this->mapToListing($result);
    }

    public function getUserListings(int $userId): array
    {
        $query = "
            SELECT 
                l.id,
                l.user_id,
                l.item_name,
                l.item_type_id,
                it.name as item_type,
                l.level,
                l.rarity_id,
                r.name as rarity,
                l.price,
                l.currency_id,
                c.name as currency,
                l.server_id,
                s.name as server,
                l.contact,
                l.status_id,
                ls.name as status,
                l.image_url,
                l.created_at,
                l.sold_at,
                u.email as user_email
            FROM listings l
            INNER JOIN item_types it ON l.item_type_id = it.id
            INNER JOIN rarities r ON l.rarity_id = r.id
            INNER JOIN currencies c ON l.currency_id = c.id
            INNER JOIN servers s ON l.server_id = s.id
            INNER JOIN listing_statuses ls ON l.status_id = ls.id
            INNER JOIN users u ON l.user_id = u.id
            WHERE l.user_id = :user_id
            ORDER BY l.created_at DESC
        ";

        $results = $this->fetchAll($query, ['user_id' => $userId]);
        return $this->mapToListings($results);
    }

    public function createListing(
        int $userId,
        string $itemName,
        int $itemTypeId,
        int $level,
        int $rarityId,
        int $price,
        int $currencyId,
        int $serverId,
        string $contact,
        ?string $imageUrl = null
    ): ?int {
        $statusQuery = "SELECT id FROM listing_statuses WHERE name = 'active' LIMIT 1";
        $statusResult = $this->fetchOne($statusQuery);
        $statusId = $statusResult ? (int) $statusResult['id'] : 1;

        $query = "
            INSERT INTO listings (
                user_id, item_name, item_type_id, level, rarity_id,
                price, currency_id, server_id, contact, status_id, image_url
            ) VALUES (
                :user_id, :item_name, :item_type_id, :level, :rarity_id,
                :price, :currency_id, :server_id, :contact, :status_id, :image_url
            )
        ";

        $success = $this->execute($query, [
            'user_id' => $userId,
            'item_name' => $itemName,
            'item_type_id' => $itemTypeId,
            'level' => $level,
            'rarity_id' => $rarityId,
            'price' => $price,
            'currency_id' => $currencyId,
            'server_id' => $serverId,
            'contact' => $contact,
            'status_id' => $statusId,
            'image_url' => $imageUrl
        ]);

        return $success ? $this->getLastInsertId() : null;
    }

    public function markAsSold(int $listingId, int $userId): bool
    {
        $query = "SELECT mark_listing_as_sold(:listing_id, :user_id)";
        
        try {
            $result = $this->fetchOne($query, [
                'listing_id' => $listingId,
                'user_id' => $userId
            ]);
            return $result !== null;
        } catch (PDOException $e) {
            error_log("Mark as sold failed: " . $e->getMessage());
            return false;
        }
    }

    public function deleteListing(int $listingId, int $userId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            $query = "DELETE FROM listings WHERE id = :id";
            return $this->execute($query, ['id' => $listingId]);
        } else {
            $query = "DELETE FROM listings WHERE id = :id AND user_id = :user_id";
            return $this->execute($query, [
                'id' => $listingId,
                'user_id' => $userId
            ]);
        }
    }

    public function getServers(): array
    {
        $query = "SELECT id, name FROM servers ORDER BY name";
        return $this->fetchAll($query);
    }

    public function getItemTypes(): array
    {
        $query = "SELECT id, name FROM item_types ORDER BY name";
        return $this->fetchAll($query);
    }

    public function getRarities(): array
    {
        $query = "SELECT id, name FROM rarities ORDER BY id";
        return $this->fetchAll($query);
    }

    public function getCurrencies(): array
    {
        $query = "SELECT id, name FROM currencies ORDER BY id";
        return $this->fetchAll($query);
    }

    public function countActiveListings(): int
    {
        $query = "
            SELECT COUNT(*) as count 
            FROM listings l
            INNER JOIN listing_statuses ls ON l.status_id = ls.id
            WHERE ls.name = 'active'
        ";
        $result = $this->fetchOne($query);
        return $result ? (int) $result['count'] : 0;
    }

    private function mapToListing(array $data): Listing
    {
        return new Listing(
            $data['item_name'],
            (int) $data['item_type_id'],
            (int) $data['level'],
            (int) $data['rarity_id'],
            (int) $data['price'],
            (int) $data['currency_id'],
            (int) $data['server_id'],
            $data['contact'],
            (int) $data['user_id'],
            (int) $data['status_id'],
            isset($data['id']) ? (int) $data['id'] : null,
            $data['item_type'] ?? '',
            $data['rarity'] ?? '',
            $data['currency'] ?? '',
            $data['server'] ?? '',
            $data['status'] ?? 'active',
            $data['image_url'] ?? null,
            $data['created_at'] ?? null,
            $data['sold_at'] ?? null,
            $data['user_email'] ?? null
        );
    }

    private function mapToListings(array $results): array
    {
        $listings = [];
        foreach ($results as $row) {
            $listings[] = $this->mapToListing($row);
        }
        return $listings;
    }

    public function countFilteredListings(?string $searchTerm = null, ?int $serverId = null, int $minLevel = 0, 
    int $maxLevel = 300, ?int $itemTypeId = null, ?int $rarityId = null, ?int $currencyId = null): int {
        $query = "
            SELECT COUNT(*) as count
            FROM listings l
            INNER JOIN listing_statuses ls ON l.status_id = ls.id
            WHERE ls.name = 'active'
                AND (:server_id::INTEGER IS NULL OR l.server_id = :server_id)
                AND (:item_type_id::INTEGER IS NULL OR l.item_type_id = :item_type_id)
                AND (:rarity_id::INTEGER IS NULL OR l.rarity_id = :rarity_id)
                AND (:currency_id::INTEGER IS NULL OR l.currency_id = :currency_id)
                AND l.level BETWEEN :min_level AND :max_level
                AND (:search_term::VARCHAR IS NULL OR l.item_name ILIKE '%' || :search_term || '%')
        ";

        $result = $this->fetchOne($query, [
            'search_term' => $searchTerm,
            'server_id' => $serverId,
            'min_level' => $minLevel,
            'max_level' => $maxLevel,
            'item_type_id' => $itemTypeId,
            'rarity_id' => $rarityId,
            'currency_id' => $currencyId
        ]);

        return $result ? (int) $result['count'] : 0;
    }
}