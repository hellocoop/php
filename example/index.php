<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HelloCoop\Config\HelloConfig;
use HelloCoop\HelloClient;


define('API_ROUTE', '/api/hellocoop');
define('HOST', '12fe-223-205-76-153.ngrok-free.app'); // add your domain name here

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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.hello.coop/css/hello-btn.css" rel="stylesheet">
  <title>Hellō identity provider(IdP) client in PHP - Demo</title>
  <style>
    /* Center the button and content */
    body {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background-color: #f9f9f9; /* Optional background color */
    }
    pre {
      margin-bottom: 20px;
      padding: 10px;
      background-color: #efefef;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      color: #333;
      max-width: 90%;
      overflow-x: auto;
    }
    .hello-container {
      text-align: center;
    }
    .hello-btn {
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <pre>
<?php
// Assuming $helloClient is already instantiated and configured
echo json_encode($helloClient->getAuth(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
  </pre>
  <div class="hello-container">
    <button class="hello-btn" onclick="login(event)">
      ō&nbsp;&nbsp;&nbsp;Continue with Hellō
    </button>

<?php
// Get authentication status
$auth = $helloClient->getAuth();
if ($auth['isLoggedIn']) : ?>
    <button class="hello-btn" onclick="logout(event)">
      ō&nbsp;&nbsp;&nbsp;Logout
    </button>
<?php endif; ?>
  </div>
  <script>
    function login(event) {
      const LOGIN_PATH = 'https://' + 
        '<?php echo htmlspecialchars(HOST, ENT_QUOTES, "UTF-8"); ?>' +
        '/api/hellocoop?op=login&target_uri=/profile&scope=profile+nickname&provider_hint=github+gitlab';
      
      event.target.classList.add('hello-btn-loader'); // Show spinner
      event.target.disabled = true;                  // Disable button
      window.location.href = LOGIN_PATH;             // Redirect to login endpoint
    }

    function logout(event) {
      const LOGOUT_PATH = 'https://' + 
        '<?php echo htmlspecialchars(HOST, ENT_QUOTES, "UTF-8"); ?>' +
        '/api/hellocoop?op=logout';
      
      event.target.classList.add('hello-btn-loader'); // Show spinner
      event.target.disabled = true;                  // Disable button
      window.location.href = LOGOUT_PATH;            // Redirect to logout endpoint
    }
  </script>
</body>
</html>
