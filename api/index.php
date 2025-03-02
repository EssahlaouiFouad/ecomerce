<?php
require_once '../config/db.php';

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url($request_uri, PHP_URL_PATH);

// Route the request to the appropriate file
switch ($base_path) {
    case '/submit_order.php':
        require_once '../submit_order.php';
        break;
    case '/order_success.php':
        require_once '../order_success.php';
        break;
    case '/admin/dashboard.php':
        require_once '../admin/dashboard.php';
        break;
    case '/admin/settings.php':
        require_once '../admin/settings.php';
        break;
    case '/admin/login.php':
        require_once '../admin/login.php';
        break;
    case '/admin/logout.php':
        require_once '../admin/logout.php';
        break;
    default:
        // Serve the main index page
        readfile('../index.htm');
        break;
}