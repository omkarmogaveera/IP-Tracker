# Enterprise IP Tracking and Authentication System

Welcome to the Enterprise IP Tracking project! This robust PHP application provides a secure authentication system (Login/Signup) coupled with detailed IP address tracking and geographic location logging.

## 🗂️ Project Structure & File Explanation

The project is structured following enterprise best practices, separating the Frontend UI, Backend APIs, and Core Logic.

### 1. The Core Logic (`/classes/`)
*   **`Tracker.php`**: The brain behind the IP tracking. It detects the user's IP from server headers, contacts the external `ip-api.com` service to get the geographic coordinates and location names, and saves this data securely into the database. *(Note: For local testing, it uses `api.ipify.org` to find your public IP).*
*   **`User.php`**: Handles all user-related database operations. It securely hashes passwords using PHP's native `bcrypt` algorithm during signup, and verifies those hashes during login.

### 2. Backend APIs (`/api/`)
*   **`auth/signup.php`**: A RESTful POST endpoint that receives user registration data, validates it, and tells `User.php` to save it.
*   **`auth/login.php`**: A RESTful POST endpoint that receives login credentials. If the password matches, it immediately tells `Tracker.php` to log the successful attempt (along with the IP data).
*   **`tracking/get-ip-info.php`**: A standalone GET endpoint that fetches the current user's IP and location. *This API is highly reusable! You can copy this file into any future project to easily get user locations.*

### 3. Frontend & Assets
*   **`index.html` & `signup.html`**: The user interface. Built with Bootstrap and custom CSS to achieve a modern "glassmorphism" aesthetic.
*   **`dashboard.html`**: The page users see after logging in, displaying their full tracked location data and a dynamic map.
*   **`assets/js/app.js` & `dashboard.js`**: These scripts intercept form submissions and use the `fetch()` API to talk to the PHP backend without reloading the page. 
*   **`assets/css/style.css`**: Contains the styling, animations, and color palettes.

### 4. Database Config
*   **`config/database.php`**: Contains the credentials to connect to the MySQL database using PHP Data Objects (PDO).
*   **`config/schema.sql`**: The blueprint of the database. It creates the `users` table and the `login_logs` table (which is linked to the `users` table via a Foreign Key).

---

## 🌍 How IP Address and Geo-Location Tracking Works

1.  **IP Extraction**: When a user visits the login page, their browser sends a request to the server. The server can see the "Return Address" of this request—this is the IP address. `Tracker.php` reads this from the `$_SERVER` superglobal array.
2.  **Geo-Location Lookup**: IP addresses are assigned to Internet Service Providers (ISPs) in blocks. Databases exist that map these IP blocks to physical locations. Our app takes the user's IP and sends it to a free external service called `ip-api.com`.
3.  **Data Retrieval**: `ip-api.com` cross-references the IP with its massive database and replies with a JSON object containing the Country, State, City, ZIP code, Timezone, and ISP name.
4.  **Logging**: `Tracker.php` parses this JSON and saves all the details into your `login_logs` database table for permanent auditing.


