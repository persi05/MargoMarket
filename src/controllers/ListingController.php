<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ListingRepository.php';

class ListingController extends AppController
{
    private ListingRepository $listingRepository;

    public function __construct()
    {
        $this->listingRepository = new ListingRepository();
    }

    public function index(): void
    {
        $searchTerm = null;
        if (!empty($_GET['search'])) {
            $searchTerm = substr(trim($_GET['search']), 0, 50);
        }

        $serverId = !empty($_GET['server']) ? (int)$_GET['server'] : null;

        $minLevel = 0;
        if (isset($_GET['min_level']) && $_GET['min_level'] !== '') {
            $val = (int)$_GET['min_level'];
            $minLevel = max(0, min(300, $val));
        }

        $maxLevel = 300;
        if (isset($_GET['max_level']) && $_GET['max_level'] !== '') {
            $val = (int)$_GET['max_level'];
            $maxLevel = max(0, min(300, $val));
        }

        $itemTypeId = !empty($_GET['item_type']) ? (int)$_GET['item_type'] : null;
        $rarityId = !empty($_GET['rarity']) ? (int)$_GET['rarity'] : null;
        $currencyId = !empty($_GET['currency']) ? (int)$_GET['currency'] : null;
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $listings = $this->listingRepository->searchListings(
            $searchTerm,
            $serverId,
            $minLevel,
            $maxLevel,
            $itemTypeId,
            $rarityId,
            $currencyId,
            $limit,
            $offset
        );

        $servers = $this->listingRepository->getServers();
        $itemTypes = $this->listingRepository->getItemTypes();
        $rarities = $this->listingRepository->getRarities();
        $currencies = $this->listingRepository->getCurrencies();
        
        $totalListings = $this->listingRepository->countFilteredListings(
            $searchTerm,
            $serverId,
            $minLevel,
            $maxLevel,
            $itemTypeId,
            $rarityId,
            $currencyId
        );
        
        $totalPages = ceil($totalListings / $limit);

        $this->render('listings/index', [
            'listings' => $listings,
            'servers' => $servers,
            'itemTypes' => $itemTypes,
            'rarities' => $rarities,
            'currencies' => $currencies,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'filters' => [
                'search' => $searchTerm,
                'server' => $serverId,
                'min_level' => $minLevel,
                'max_level' => $maxLevel,
                'item_type' => $itemTypeId,
                'rarity' => $rarityId,
                'currency' => $currencyId
            ]
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $servers = $this->listingRepository->getServers();
            $itemTypes = $this->listingRepository->getItemTypes();
            $rarities = $this->listingRepository->getRarities();
            $currencies = $this->listingRepository->getCurrencies();

            $this->render('listings/create', [
                'servers' => $servers,
                'itemTypes' => $itemTypes,
                'rarities' => $rarities,
                'currencies' => $currencies
            ]);
            return;
        }

        $itemName = substr(trim($_POST['item_name'] ?? ''), 0, 60); 
        $contact = substr(trim($_POST['contact'] ?? ''), 0, 50);
        $imageUrl = substr(trim($_POST['image_url'] ?? ''), 0, 255);

        $itemTypeId = (int)($_POST['item_type_id'] ?? 0);
        $level = (int)($_POST['level'] ?? 0);
        $rarityId = (int)($_POST['rarity_id'] ?? 0);
        $currencyId = (int)($_POST['currency_id'] ?? 0);
        $serverId = (int)($_POST['server_id'] ?? 0);
        
        $price = (int)($_POST['price'] ?? 0);

        $errors = [];

        if (strlen($itemName) < 3) {
            $errors[] = 'Nazwa przedmiotu musi mieć minimum 3 znaki';
        }

        if ($level < 1 || $level > 300) {
            $errors[] = 'Poziom musi być między 1 a 300';
        }

        if ($price <= 0) {
            $errors[] = 'Cena musi być większa od 0';
        }

        if ($price > 2000000000) {
            $errors[] = 'Cena musi być mniejsza niż 2000000000';
        }

        if (empty($contact)) {
            $errors[] = 'Podaj dane kontaktowe';
        }

        if ($itemTypeId <= 0 || $rarityId <= 0 || $currencyId <= 0 || $serverId <= 0) {
            $errors[] = 'Wypełnij wszystkie wymagane pola';
        }

        if (!empty($errors)) {
            $servers = $this->listingRepository->getServers();
            $itemTypes = $this->listingRepository->getItemTypes();
            $rarities = $this->listingRepository->getRarities();
            $currencies = $this->listingRepository->getCurrencies();

            $this->render('listings/create', [
                'messages' => implode(', ', $errors),
                'servers' => $servers,
                'itemTypes' => $itemTypes,
                'rarities' => $rarities,
                'currencies' => $currencies,
                'formData' => $_POST
            ]);
            return;
        }

        $listingId = $this->listingRepository->createListing(
            $this->getCurrentUser(),
            $itemName,
            $itemTypeId,
            $level,
            $rarityId,
            $price,
            $currencyId,
            $serverId,
            $contact,
            !empty($imageUrl) ? $imageUrl : null
        );

        if ($listingId) {
            $this->redirect('/my-listings?success=created');
        } else {
            $servers = $this->listingRepository->getServers();
            $itemTypes = $this->listingRepository->getItemTypes();
            $rarities = $this->listingRepository->getRarities();
            $currencies = $this->listingRepository->getCurrencies();

            $this->render('listings/create', [
                'messages' => 'Błąd podczas tworzenia ogłoszenia',
                'servers' => $servers,
                'itemTypes' => $itemTypes,
                'rarities' => $rarities,
                'currencies' => $currencies,
                'formData' => $_POST
            ]);
        }
    }

    public function myListings(): void
    {
        $this->requireAuth();

        $userId = $this->getCurrentUser();
        $listings = $this->listingRepository->getUserListings($userId);
        
        $success = $_GET['success'] ?? null;
        $successMessage = null;
        
        if ($success === 'created') {
            $successMessage = 'Ogłoszenie zostało utworzone!';
        } elseif ($success === 'sold') {
            $successMessage = 'Ogłoszenie oznaczone jako sprzedane!';
        } elseif ($success === 'deleted') {
            $successMessage = 'Ogłoszenie zostało usunięte!';
        }

        $this->render('listings/my-listings', [
            'listings' => $listings,
            'success' => $successMessage
        ]);
    }

    public function markAsSold(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('/my-listings');
            return;
        }

        $listingId = (int)($_POST['listing_id'] ?? 0);
        $userId = $this->getCurrentUser();

        if ($listingId <= 0) {
            $this->redirect('/my-listings?error=invalid');
            return;
        }

        $success = $this->listingRepository->markAsSold($listingId, $userId);

        if ($success) {
            $this->redirect('/my-listings?success=sold');
        } else {
            $this->redirect('/my-listings?error=failed');
        }
    }

public function delete(): void
    {
        $this->requireAuth();

        if (!$this->isPost()) {
            $this->redirect('/my-listings');
            return;
        }

        $listingId = (int)($_POST['listing_id'] ?? 0);
        $userId = $this->getCurrentUser();

        if ($listingId <= 0) {
            $this->redirect('/my-listings?error=invalid');
            return;
        }

        $listing = $this->listingRepository->getListingById($listingId);

        if (!$listing || $listing->getUserId() !== $userId) {
            $this->redirect('/my-listings?error=invalid');
            return;
        }

        if ($listing->isSold()) {
            $this->redirect('/my-listings?error=cannot_delete_sold'); 
            return;
        }

        $success = $this->listingRepository->deleteListing($listingId, $userId);

        if ($success) {
            $this->redirect('/my-listings?success=deleted');
        } else {
            $this->redirect('/my-listings?error=failed');
        }
    }
}