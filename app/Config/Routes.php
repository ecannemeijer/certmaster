<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Redirect root to login
$routes->get('/', 'Auth::login');

// Authentication routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::authenticate');
$routes->get('logout', 'Auth::logout');
$routes->get('check-session', 'Auth::checkSession');
$routes->get('api/version', 'Auth::getVersion');

// Protected routes (require authentication)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('logs', 'Dashboard::logs');

    // Server management
    $routes->get('servers', 'Servers::index');
    $routes->get('servers/create', 'Servers::create');
    $routes->post('servers/store', 'Servers::store');
    $routes->get('servers/edit/(:num)', 'Servers::edit/$1');
    $routes->post('servers/update/(:num)', 'Servers::update/$1');
    $routes->get('servers/delete/(:num)', 'Servers::delete/$1');
    $routes->post('servers/generate-ssh-key/(:num)', 'Servers::generateSshKey/$1');

    // Certificate management
    $routes->get('certificates/upload/(:num)', 'Certificates::upload/$1');
    $routes->post('certificates/store/(:num)', 'Certificates::store/$1');
    $routes->get('certificates/info/(:num)', 'Certificates::info/$1');
    $routes->post('certificates/deploy/(:num)', 'Certificates::deploy/$1');

    // User management
    $routes->get('users', 'Users::index');
    $routes->get('users/create', 'Users::create');
    $routes->post('users/store', 'Users::store');
    $routes->get('users/edit/(:num)', 'Users::edit/$1');
    $routes->post('users/update/(:num)', 'Users::update/$1');
    $routes->get('users/delete/(:num)', 'Users::delete/$1');
    $routes->get('users/changePassword/(:num)', 'Users::changePassword/$1');
    $routes->post('users/updatePassword/(:num)', 'Users::updatePassword/$1');
});
