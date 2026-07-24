# Free Ways to See Your Project Live

You do not need to buy a domain or hosting to share this project with others on the internet! Here are the two best, completely free ways to do it.

---

## Method 1: The Instant Way (Using Ngrok)

**Ngrok** is a tool that creates a secure tunnel from the public internet directly to your computer's local XAMPP/WAMP server. It's the absolute fastest way to share what you have right now with friends.

**Pros:** Instant setup, no need to move files, no need to export databases.
**Cons:** The link only works while your computer is turned on and Ngrok is running.

1.  **Keep XAMPP Running:** Make sure Apache and MySQL are running in your XAMPP control panel.
2.  **Download Ngrok:** Go to [ngrok.com](https://ngrok.com/), sign up for a free account, and download the software for Windows.
3.  **Unzip and Authenticate:** Unzip the `ngrok.exe` file. Go to your Ngrok dashboard online to copy your authtoken, then run this command in your terminal where `ngrok.exe` is:
    `ngrok config add-authtoken YOUR_TOKEN_HERE`
4.  **Start the Tunnel:** Tell Ngrok to forward port 80 (the default port for XAMPP Apache) to the internet by running:
    `ngrok http 80`
5.  **Share the Link!** Ngrok will give you a "Forwarding" URL that looks like `https://a1b2c3d4.ngrok-free.app`. 
    *   Send this URL to a friend, but make sure to append your folder name: `https://a1b2c3d4.ngrok-free.app/IP_TrackingLogin/`
    *   *Note: Because Ngrok proxies the connection, sometimes the IP tracked might show up as the Ngrok server's IP rather than your friend's exact IP.*

---

## Method 2: The Permanent Way (Using InfinityFree)

**InfinityFree** is a truly free web hosting service that supports PHP and MySQL. It gives you a free subdomain (like `my-tracker.epizy.com`).

**Pros:** It is a real server on the internet. It works 24/7 even when your computer is off. It will track real IPs perfectly.
**Cons:** Takes about 10 minutes to set up and upload files.

### Step 1: Create a Free Account
1. Go to [infinityfree.com](https://infinityfree.com/) and register.
2. Click **Create Account**, choose a free subdomain (like `yourname-tracker`), and choose a domain extension (like `.epizy.com`).

### Step 2: Set up the Database
1. Go into your InfinityFree account control panel.
2. Click on **MySQL Databases**.
3. Create a new database (e.g., `epiz_12345678_iptracking`).
4. Click **Admin** to open phpMyAdmin.
5. In phpMyAdmin, click the **SQL** tab. Open your local `config/schema.sql` file on your computer, copy all the text, paste it into the SQL box, and hit **Go**.

### Step 3: Update your Code
Open `config/database.php` on your computer and change the credentials to match InfinityFree. You can find these details in your InfinityFree MySQL Databases page:
```php
define('DB_HOST', 'sqlxxx.epizy.com'); // Look for the "MySQL Host Name"
define('DB_NAME', 'epiz_12345678_iptracking'); // The DB you just created
define('DB_USER', 'epiz_12345678'); // Your InfinityFree username
define('DB_PASS', 'YourAccountPassword'); // The password you use for InfinityFree
```

### Step 4: Upload the Files
1. In your InfinityFree control panel, click on **Online File Manager**.
2. Open the `htdocs` folder. (This is where public files go).
3. **Delete** any default files in there.
4. Upload all the files and folders from your `IP_TrackingLogin` folder into `htdocs`. *(Upload `index.html`, the `api` folder, `classes`, etc. straight into `htdocs`)*.

### Step 5: Test it Live!
Go to the free web address you created in Step 1 (e.g., `http://yourname-tracker.epizy.com`). The project is now live on the internet!

---

## Method 3: The Modern Way (Push to GitHub -> Railway.app)

If you want the modern developer experience where you simply push your code to GitHub and it automatically updates live on the internet, **Railway.app** is the best platform that supports both PHP and MySQL. 

*Note: Railway is free to start, but they may require you to link a card just to verify you aren't a bot.*

### Step 1: Push your code to GitHub
1. Create a new, empty repository on your [GitHub](https://github.com/) account.
2. Open a terminal in your local `IP_TrackingLogin` folder and run these commands to push your code:
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   git push -u origin main
   ```

### Step 2: Create a Database on Railway
1. Go to [Railway.app](https://railway.app/) and log in with your GitHub account.
2. Click **New Project** -> **Provision MySQL**. 
3. Wait a few seconds for the database to spin up. Click on the new **MySQL block** on your canvas.
4. In the right-side menu, click on the **Connect** tab (or **Data** tab depending on the UI version). Here you will see your database credentials (Host, Port, User, Password, and Database Name). Keep this handy!

### Step 3: Connect your GitHub Repo
1. Back on your Railway project canvas, click the **+ New** button -> **GitHub Repo**.
2. Select the repository you just pushed your PHP code to.
3. Railway will instantly detect that it is a PHP project and start building it.

### Step 4: Link the Database to the App
Because I updated your code to automatically detect Railway environment variables, you don't need to change any code! Just pass the database details into your app:
1. On your Railway project canvas, click on your **GitHub repo block** (your PHP app) and go to the **Variables** tab.
2. Click **New Variable**.
3. **The Easy Way:** Select **Reference Variable**, choose your MySQL database from the dropdown, and Railway will automatically pull in all 5 variables (`MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLPORT`).
4. **The Manual Way:** If you don't see Reference Variable, manually add those 5 variables by copying the exact values from the MySQL block's **Connect** tab.
5. Once added, Railway will automatically redeploy your app with the new database connection!

### Step 5: Import the Schema & Go Live
1. You need to create your tables in the Railway database. Click on your **MySQL block** in Railway, go to the **Data** tab, click **New Table** -> **Raw Query**.
2. Paste the exact contents of your local `config/schema.sql` file and execute it to create your `users` and `login_logs` tables.
3. Finally, click on your **GitHub app block**, go to the **Settings** tab, scroll down to the **Networking** or **Domains** section, and click **Generate Domain**.
4. Railway will give you a live URL (e.g., `your-app-production.up.railway.app`). Click it, and your site is live!
