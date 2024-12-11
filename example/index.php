<?php

require_once __DIR__ . '/../vendor/autoload.php';

use HelloCoop\Config\HelloConfig;
use HelloCoop\HelloClient;

// Step 1: Define all required configurations
define('API_ROUTE', '/api/hellocoop'); // Define the API route
// App ID from https://console.hello.coop/
define('APP_ID', 'app_43tf7X1qHvsCVZIuPQtzQE8J_KQq');
// Add your domain name here (e.g., ngrok domain or deployed domain)
define('HOST', 'b46e-223-205-76-153.ngrok-free.app');
// Create a 32-byte hex secret key using the command: openssl rand -hex 32
define('SECRET', '1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef');

// Step 2: Create an instance of the HelloConfig class
$config = new HelloConfig(
    API_ROUTE,                              // $apiRoute: The base API route
    API_ROUTE . '?op=auth',                 // $authApiRoute: The route for authentication
    API_ROUTE . '?op=login',                // $loginApiRoute: The route for login
    API_ROUTE . '?op=logout',               // $logoutApiRoute: The route for logout
    false,                                  // CSRF protection (false to restrict cross-origin requests)
    APP_ID,                                 // Application ID
    'https://' . HOST . API_ROUTE,          // Full API URL
    HOST,                                   // Hostname
    SECRET                                  // Secret key for signing requests
);

// Step 3: Create an instance of HelloClient
$helloClient = new HelloClient($config);

$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri); // Extract the path from the request URI, ignoring query parameters
$requestPath = $parsedUrl['path'] ?? '';

// Step 4: Route Hellō API requests
if ($requestPath === API_ROUTE) {
    $helloClient->route(); // Handle the routing of the API request
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
        '/api/hellocoop?op=login&target_uri=/profile&scope=openid+profile+nickname&provider_hint=github+gitlab';
      
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
