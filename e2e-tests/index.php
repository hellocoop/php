<?php

require_once __DIR__ . '/../tests/bootstrap.php';

use HelloCoop\Config\HelloConfigBuilder;
use HelloCoop\HelloClient;


define('API_ROUTE', '/api/hellocoop');

$builder = new HelloConfigBuilder();
$config = $builder
    ->setApiRoute('/api/hellocoop')
    ->setAuthApiRoute('/api/hellocoop?op=auth')
    ->setLoginApiRoute('/api/hellocoop?op=login')
    ->setLogoutApiRoute('/api/hellocoop?op=logout')
    ->setSameSiteStrict(false)
    ->setClientId('000000-0000-0000-0000-000000000000')
    ->setRedirectURI('http://localhost:8000/api/hellocoop')
    ->setSecret('66c71f55568f7b0c3b30cb6a8df9975b5125000caa775240b2e76eb96c43715e')
    ->setHelloWallet('http://localhost:3333')
    ->setScope(['openid', 'profile', 'email'])
    ->build();

// Create an instance of HelloClient
$helloClient = new HelloClient($config);

$requestUri = $_SERVER['REQUEST_URI'];
// Extract the path from the request URI, ignoring query parameters
$parsedUrl = parse_url($requestUri);
$requestPath = $parsedUrl['path'] ?? '';

// Route Hellō API requests
if ($requestPath === API_ROUTE) {
    // Handle the routing of the API request
    print $helloClient->route();
} else {
    header('Content-Type: application/json');
    print json_encode($helloClient->getAuth());
}
