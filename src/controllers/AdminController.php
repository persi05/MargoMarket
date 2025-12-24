<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/ListingRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class AdminController extends AppController
{
    private ListingRepository $listingRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->listingRepository = new ListingRepository();
        $this->userRepository = new UserRepository();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $searchTerm = !empty($_GET['search']) ? $_GET['search'] : null;
        $serverId = !empty($_GET['server']) ? (int)$_GET['server'] : null;
        
        $status = !empty($_GET['status']) ? $_GET['status'] : null;

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $listings = $this->listingRepository->getAllListingsAdmin(
            $searchTerm,
            $serverId,
            $status,
            $limit,
            $offset
        );

        $servers = $this->listingRepository->getServers();
        
        $totalListings = $this->listingRepository->countAllListings($searchTerm, $serverId, $status);
        
        $totalPages = ceil($totalListings / $limit);
        $totalUsers = $this->userRepository->countUsers();

        $this->render('admin/index', [
            'listings' => $listings,
            'servers' => $servers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalListings' => $totalListings,
            'totalUsers' => $totalUsers,
            'filters' => [
                'search' => $searchTerm,
                'server' => $serverId,
                'status' => $status
            ]
        ]);
    }

    public function deleteListingAdmin(): void
    {
        $this->requireAdmin();

        if (!$this->isPost()) {
            $this->redirect('/admin');
            return;
        }

        $listingId = (int)($_POST['listing_id'] ?? 0);

        if ($listingId <= 0) {
            $this->redirect('/admin?error=invalid');
            return;
        }

        $success = $this->listingRepository->deleteListing($listingId, 0, true);

        if ($success) {
            $this->redirect('/admin?success=deleted');
        } else {
            $this->redirect('/admin?error=failed');
        }
    }

public function users(): void
{
    $this->requireAdmin();

    $usersData = $this->userRepository->getAllUsers();
    
    $users = [];
    foreach ($usersData as $user) {
        $stats = $this->userRepository->getUserStats($user->getId());
        $users[] = [
            'user' => $user,
            'stats' => $stats
        ];
    }

    $this->render('admin/users', [
        'users' => $users
    ]);
}
}