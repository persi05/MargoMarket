<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/ListingController.php';
require_once 'src/controllers/AdminController.php';

class Routing
{
    private static ?Routing $instance = null;
    
    private static array $routes = [
        'login' => [
            'controller' => 'SecurityController',
            'action' => 'login'
        ],
        'register' => [
            'controller' => 'SecurityController',
            'action' => 'register'
        ],
        'logout' => [
            'controller' => 'SecurityController',
            'action' => 'logout'
        ],
        
        '' => [
            'controller' => 'ListingController',
            'action' => 'index'
        ],
        'listings' => [
            'controller' => 'ListingController',
            'action' => 'index'
        ],
        'create' => [
            'controller' => 'ListingController',
            'action' => 'create'
        ],
        'my-listings' => [
            'controller' => 'ListingController',
            'action' => 'myListings'
        ],
        'mark-as-sold' => [
            'controller' => 'ListingController',
            'action' => 'markAsSold'
        ],
        'delete-listing' => [
            'controller' => 'ListingController',
            'action' => 'delete'
        ],
        'search' => [
            'controller' => 'ListingController',
            'action' => 'search'
        ],
        'favorites' => [
            'controller' => 'ListingController',
            'action' => 'favorites'
        ],
        'favorite-add' => [
            'controller' => 'ListingController',
            'action' => 'addToFavorites'
        ],
        'favorite-remove' => [
            'controller' => 'ListingController',
            'action' => 'removeFromFavorites'
        ],
        'favorite-toggle' => [
            'controller' => 'ListingController',
            'action' => 'toggleFavorite'
        ],
        
        'admin' => [
            'controller' => 'AdminController',
            'action' => 'index'
        ],
        'admin-delete' => [
            'controller' => 'AdminController',
            'action' => 'deleteListingAdmin'
        ],
        'admin-users' => [
            'controller' => 'AdminController',
            'action' => 'users'
        ],
        'admin-delete-user' => [
            'controller' => 'AdminController',
            'action' => 'deleteUser'
        ]
    ];

    private function __construct() {}
    
    private function __clone() {}

    public static function getInstance(): Routing
    {
        if (self::$instance === null) {
            self::$instance = new Routing();
        }
        return self::$instance;
    }

    public static function run(string $path): void
    {
        $path = trim($path, '/');
        $id = null;
        $routeKey = $path;

        if (preg_match('/^([\w\-]+)\/(\d+)$/', $path, $matches)) {
            $routeKey = $matches[1];
            $id = (int)$matches[2];
        }

        if (!array_key_exists($routeKey, self::$routes)) {
            http_response_code(404);
            include 'public/views/404.html';
            return;
        }

        $controllerObj = self::$routes[$routeKey]['controller'];
        $action = self::$routes[$routeKey]['action'];

        $controller = new $controllerObj();
        $controller->$action($id);
    }
}