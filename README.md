# [Hellō](https://www.hello.coop/) Identity Provider (IdP) Client in PHP

| Release | Tests  | Coverage |
| :----:  | :----: | :------: | 
| [![Latest Version](https://img.shields.io/github/release/UnnikrishnanBhargavakurup/hellocoop.svg?style=flat-square)](https://github.com/UnnikrishnanBhargavakurup/hellocoop/releases) | [![Test Status](https://github.com/UnnikrishnanBhargavakurup/hellocoop/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/UnnikrishnanBhargavakurup/hellocoop/actions) | [![Coverage](https://codecov.io/gh/UnnikrishnanBhargavakurup/hellocoop/graph/badge.svg?token=GFPX13L1TM)](https://codecov.io/gh/UnnikrishnanBhargavakurup/hellocoop) |

Hellō enables your users to register and log in using their choice of popular social logins, email, or phone—without tracking them. Hellō is a cloud identity wallet with a mission to empower users to control their digital identities.

## Key Features
- **Social Login**: Support for popular providers like Google, Facebook, etc.
- **Email and Phone Authentication**: Verified email or phone login.
- **User Privacy First**: Focused on giving users control without tracking.
- **Quick Integration**: Set up authentication in minutes.

### Developer-Friendly
The easiest and quickest way to onboard and authenticate your users with popular social providers, verified email, and phone. Hellō can be integrated into your application in minutes, not hours.

## Getting Started

The following example demonstrates how to integrate the Hellō client into your PHP application.

### Installation
Install the library using Composer:
```bash
composer require unnikrishnanbhargavakurup/hellocoop
```

### Usage

```php
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
    SECRET                                  // Secret key for encryption and decryption of data
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
```

## Code Quality: Pre-Commit Hook for Linting

To maintain code quality, this project uses a **pre-commit hook** for automatic linting before each commit.

### Setup Instructions

1. **Copy the pre-commit hook to your Git hooks directory**:
   ```bash
   cp pre-commit .git/hooks/
   ```

2. **Make the hook executable** (if it’s not already):
   ```bash
   chmod +x .git/hooks/pre-commit
   ```

This ensures code quality and consistency across the project.

## Contributing
We welcome contributions! Please open an issue or submit a pull request with your changes. Make sure your contributions adhere to the code style guidelines enforced by the pre-commit hook.

## License

This project is licensed under the MIT License. See the [LICENSE](./LICENSE) file for details.
