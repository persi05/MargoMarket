<?php

require_once __DIR__ . '/Repository.php';
require_once __DIR__ . '/../models/Listing.php';

class FavoriteRepository extends Repository
{
    private static ?FavoriteRepository $instance = null;

    private function __construct()
    {
        parent::__construct();
    }

    private function __clone() {}

    public static function getInstance(): FavoriteRepository
    {
        if (self::$instance === null) {
            self::$instance = new FavoriteRepository();
        }
        return self::$instance;
    }

    public function addFavorite(int $userId, int $listingId): bool
    {
        if ($this->isFavorite($userId, $listingId)) {
            return true;
        }

        $query = "
            INSERT INTO listing_favorites (user_id, listing_id)
            VALUES (:user_id, :listing_id)
        ";

        return $this->execute($query, [
            'user_id' => $userId,
            'listing_id' => $listingId
        ]);
    }

    public function removeFavorite(int $userId, int $listingId): bool
    {
        $query = "
            DELETE FROM listing_favorites 
            WHERE user_id = :user_id AND listing_id = :listing_id
        ";

        return $this->execute($query, [
            'user_id' => $userId,
            'listing_id' => $listingId
        ]);
    }

    public function isFavorite(int $userId, int $listingId): bool
    {
        $query = "
            SELECT COUNT(*) as count 
            FROM listing_favorites 
            WHERE user_id = :user_id AND listing_id = :listing_id
        ";

        $result = $this->fetchOne($query, [
            'user_id' => $userId,
            'listing_id' => $listingId
        ]);

        return $result && $result['count'] > 0;
    }

    public function getUserFavorites(int $userId): array
    {
        $query = "
            SELECT *
            FROM user_favorites_view
            WHERE user_id = :user_id
            ORDER BY favorited_at DESC
        ";

        $results = $this->fetchAll($query, ['user_id' => $userId]);
        
        $listings = [];
        foreach ($results as $row) {
            $listing = new Listing(
                $row['item_name'],
                (int) $row['item_type_id'],
                (int) $row['level'],
                (int) $row['rarity_id'],
                (int) $row['price'],
                (int) $row['currency_id'],
                (int) $row['server_id'],
                $row['contact'],
                (int) $row['owner_id'],
                (int) $row['status_id'],
                (int) $row['listing_id'],
                $row['item_type'],
                $row['rarity'],
                $row['currency'],
                $row['server'],
                $row['listing_status'],
                $row['listing_created_at'],
                $row['sold_at'],
                $row['owner_email']
            );
            $listings[] = $listing;
        }

        return $listings;
    }

    public function getUserFavoriteIds(int $userId): array
    {
        $query = "
            SELECT listing_id 
            FROM listing_favorites 
            WHERE user_id = :user_id
        ";

        $results = $this->fetchAll($query, ['user_id' => $userId]);
        
        return array_map(function($row) {
            return (int) $row['listing_id'];
        }, $results);
    }
}