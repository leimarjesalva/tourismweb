# Legazpi Explorer — Local Setup (XAMPP)

This README explains how to run the project locally on Windows using XAMPP (Apache + MySQL). Follow the steps below.

## 1) Copy project to XAMPP web root
Open PowerShell or Command Prompt and copy the `capstone` folder into XAMPP's `htdocs`:

```powershell
xcopy /E /I "E:\capstone" "C:\xampp\htdocs\capstone"
```

Or move the folder using Explorer.

## 2) Configure database credentials and admin password
- Open `backend/db.php` and update `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` if different.
- Change the `ADMIN_USER` and `ADMIN_PASS` constants to secure values.

## 3) Create the database schema
Option A — phpMyAdmin
- Open `http://localhost/phpmyadmin` and import `backend/schema.sql`.

Option B — MySQL client (command line)

```powershell
# adjust path to mysql if necessary
cd C:\xampp\mysql\bin
mysql -u root -p < "C:\xampp\htdocs\capstone\backend\schema.sql"
```

If you already have the `events` table, ensure it has the `image` column:

```sql
ALTER TABLE events ADD COLUMN image VARCHAR(255) DEFAULT NULL;
```

## 4) Ensure uploads directory is writable
The upload handler will create `backend/uploads` automatically. If it doesn't, create it and grant write permissions (Windows example):

```powershell
mkdir "C:\xampp\htdocs\capstone\backend\uploads"
icacls "C:\xampp\htdocs\capstone\backend\uploads" /grant "IIS_IUSRS:(OI)(CI)M" /T
```

Usually XAMPP on Windows already grants Apache write permissions to project folders.

## 5) Configure PHP upload limits (if needed)
If large uploads fail, edit `C:\xampp\php\php.ini` and increase these values:

```
upload_max_filesize = 8M
post_max_size = 16M
max_execution_time = 60
```

Restart Apache after editing `php.ini`.

## 6) Start XAMPP services
Open the XAMPP Control Panel and start **Apache** and **MySQL**.

## 7) Configure Google Sign-In (optional)
1. Create an OAuth 2.0 Client ID in Google Cloud Console (type: Web application). Add origin `http://localhost`.
2. Copy the client ID and set it in `script.js` replacing `YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com`.

## 8) Open the site
- Main site: http://localhost/capstone/index.html
- Admin dashboard: http://localhost/capstone/admin.html

Default admin credentials are stored in `backend/db.php` (change them!).

## 9) Test image upload and event creation
1. Login to Admin page.
2. Create an event, choose an image (jpg/png/webp), and submit.
3. The image is uploaded to `backend/uploads/` and the event will show in the Community Events feed on the main page.

## 10) Troubleshooting
- Database connection errors: confirm credentials in `backend/db.php` and that `capstone_db` exists. Check Apache/PHP error log at `C:\xampp\apache\logs\error.log`.
- Upload errors: confirm `upload_max_filesize` and `post_max_size` in `php.ini`, and that `backend/uploads` is writable.
- Google Sign-In issues: ensure you set the proper client ID and that origin `http://localhost` is allowed in Google Cloud Console.

## 11) Security notes (recommended)
- Change admin credentials and don't use hard-coded passwords in production.
- Consider moving uploads outside webroot and serving through a protected proxy.
- Add server-side virus scanning / content validation for uploads before using in production.

---
If you'd like, I can add a `README` section with screenshots, or I can add a quick script to automate copying/importing for Windows. Which would you prefer next?
