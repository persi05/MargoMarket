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
        $searchTerm = !empty($_GET['search']) ? $_GET['search'] : null;
        $serverId = !empty($_GET['server']) ? (int)$_GET['server'] : null;
        $minLevel = !empty($_GET['min_level']) ? (int)$_GET['min_level'] : 0;
        $maxLevel = !empty($_GET['max_level']) ? (int)$_GET['max_level'] : 300;
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

        $itemName = trim($_POST['item_name'] ?? '');
        $itemTypeId = (int)($_POST['item_type_id'] ?? 0);
        $level = (int)($_POST['level'] ?? 0);
        $rarityId = (int)($_POST['rarity_id'] ?? 0);
        $price = (int)($_POST['price'] ?? 0);
        $currencyId = (int)($_POST['currency_id'] ?? 0);
        $serverId = (int)($_POST['server_id'] ?? 0);
        $contact = trim($_POST['contact'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

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

        $success = $this->listingRepository->deleteListing($listingId, $userId);

        if ($success) {
            $this->redirect('/my-listings?success=deleted');
        } else {
            $this->redirect('/my-listings?error=failed');
        }
    }
}