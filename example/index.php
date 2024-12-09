<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HelloCoop\Config\HelloConfig;
use HelloCoop\HelloClient;


define('API_ROUTE', '/api/hellocoop');
define('HOST', 'f3b0-223-205-76-153.ngrok-free.app'); // add your domain name here

// Step 1: Create instances of hello config class
$config = new HelloConfig(
    API_ROUTE,                            // $apiRoute
    API_ROUTE . '?op=auth',               // $authApiRoute
    API_ROUTE . '?op=login',             // $loginApiRoute
    API_ROUTE . '?op=logout',           // $logoutApiRoute
    false,
    'app_43tf7X1qHvsCVZIuPQtzQE8J_KQq',
    'https://' . HOST . API_ROUTE,
    HOST,
    '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
);

// Step 2: Create an instance of HelloClient
$helloClient = new HelloClient($config);


$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri); // Extract the path and ignore query parameters
$requestPath = $parsedUrl['path'] ?? '';

if ($requestPath === API_ROUTE) {
    $helloClient->route();
}

// print json_encode($helloClient->getAuth());

?>
<html>
<head>
  <link href="https://cdn.hello.coop/css/hello-btn.css" rel="stylesheet"/>
</head>
<body>
  <!-- ... -->  
  <div class="hello-container">
    <button class="hello-btn" onclick="login(event)">
      ō&nbsp;&nbsp;&nbsp;Continue with Hellō
    </button>
  </div>
  <script>
    function login(event) {
      var LOGIN_PATH = 'https://f3b0-223-205-76-153.ngrok-free.app/api/hellocoop?op=login&target_uri=/profile&scope=profile+nickname&provider_hint=github+gitlab';
      event.target.classList.add('hello-btn-loader'); // Show spinner
      event.target.disabled = true;                  // Disable button
      window.location.href = LOGIN_PATH;             // Redirect to login endpoint
    }
  </script>
</body>
</html>


