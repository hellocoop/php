# [Hellō](https://www.hello.coop/) Identity Provider (IdP) PHP Client Demo

This project demonstrates integrating the Hellō Identity Provider (IdP) in PHP using the `HelloCoop` PHP library.

## Features
- Authenticate users via the Hellō IdP.
- Simple integration with minimal configuration.
- Support for login and logout flows.

---

## Prerequisites
1. **Create an Application on Hellō**  
   - Visit [Hellō Console](https://console.hello.coop/).
   - Create a new application and obtain the **App ID**.
   
2. **Generate a Secret Key**  
   Run the following command in your terminal to generate a 32-byte hex key:  
   ```bash
   openssl rand -hex 32
   ```

3. **Set up Ngrok (Optional)**  
   If you want HTTPS redirects during local development, install and run [Ngrok](https://ngrok.com/).

---

## Configuration

### Update Script Constants
In the script:
1. Replace the placeholder `HOST` with your domain (use Ngrok if running locally):
   ```php
   define('HOST', 'your-ngrok-domain.ngrok-free.app');
   ```
2. Replace `APP_ID` with your Hellō App ID:
   ```php
   define('APP_ID', 'your-app-id-here');
   ```
3. Replace `SECRET` with your generated secret key:
   ```php
   define('SECRET', 'your-secret-key-here');
   ```

---

## Running the Demo

1. **Start PHP's Built-In Server**  
   Navigate to the example folder:
   ```bash
   php -S localhost:8080
   ```

2. **Start Ngrok (Optional)**  
   In a separate terminal, start Ngrok for port 8080:
   ```bash
   ngrok http 8080
   ```
   Note down the generated HTTPS URL and update the `HOST` constant in the script.

3. **Access the Demo**  
   Open the browser and navigate to your server's address (e.g., `https://your-ngrok-domain.ngrok-free.app` or your Ngrok domain).

---

## Demo Usage

- **Login**  
   Click the **Continue with Hellō** button to initiate the login flow.
- **Logout**  
   If logged in, the **Logout** button will be displayed. Use it to end the session.
- **View Auth Data**  
   The authentication details are displayed in a `pre` block for debugging purposes.

---

## Notes
- Ensure the application is running on HTTPS when deployed.
- Update your Hellō application's redirect URIs to include the appropriate endpoints (`/api/hellocoop`).

Enjoy using the Hellō Identity Provider for seamless user authentication!