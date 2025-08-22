<?php

require_once __DIR__ . '/../tests/bootstrap.php';

use HelloCoop\Config\HelloConfigBuilder;
use HelloCoop\HelloClient;

define('API_ROUTE', '/api/hellocoop');

$builder = new HelloConfigBuilder();
$config = $builder
    ->setApiRoute(API_ROUTE)
    ->setAuthApiRoute(API_ROUTE . '?op=auth')
    ->setLoginApiRoute(API_ROUTE . '?op=login')
    ->setLogoutApiRoute(API_ROUTE . '?op=logout')
    ->setSameSiteStrict(false)
    ->setClientId('000000-0000-0000-0000-000000000000')
    ->setRedirectURI('http://localhost:8000/api/hellocoop')
    ->setSecret('66c71f55568f7b0c3b30cb6a8df9975b5125000caa775240b2e76eb96c43715e')
    ->setHelloWallet('http://localhost:3333')
    ->setScope(['openid', 'profile', 'email'])
    ->build();

$helloClient = new HelloClient($config);

// Current request path (ignore query string)
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl   = parse_url($requestUri);
$requestPath = $parsedUrl['path'] ?? '/';

// 1) Direct Hellō API route → just route it
if ($requestPath === API_ROUTE) {
    print $helloClient->route();
    exit;
}

// 2) If a GET like /post-test?op=login&login_hint=... arrives,
// convert it into a POST and let HelloClient handle it.
// Handle GET /post-test?... by converting selected query params to POST
if ($requestPath === '/post-test' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $allowed = ['op', 'login_hint', 'domain_hint', 'iss', 'command_token'];

    $payload = [];
    foreach ($allowed as $key) {
        if (isset($_GET[$key])) {
            $payload[$key] = $_GET[$key];
        }
    }

    if (!empty($payload)) {
        $_POST = $payload;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = $_SERVER['CONTENT_TYPE'] ?? 'application/x-www-form-urlencoded';

        print $helloClient->route();
        exit;
    }
}

// 3) Fallback: return current auth status
header('Content-Type: application/json');
print json_encode($helloClient->getAuth());
