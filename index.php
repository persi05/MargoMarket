<?php

require_once 'Routing.php';
require_once 'src/repository/UserRepository.php';

session_start();

$userRepository = UserRepository::getInstance();

# not good but acceptable
if (random_int(1, 100) === 1) { 
    $userRepository->cleanupExpiredSessions();
}

if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
    $user = $userRepository->getUserBySessionToken($_SESSION['session_token']);
    
    if (!$user) {
        session_unset();
        session_destroy();
        session_start();
    }
}

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

if ($path === null || $path === false) {
    $path = '';
}

Routing::run($path);