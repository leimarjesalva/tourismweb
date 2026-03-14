# How to Run Your Capstone System with Database Connection

## Prerequisites

1. **XAMPP Installed** - Make sure XAMPP is installed with Apache and MySQL
2. **Database Created** - phpMyAdmin database `capstone_db` with tables imported
3. **Project Files** - Your capstone project in `c:/xampp/htdocs/capstone`

---

## Step-by-Step Setup

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Click **Start** for **Apache** (web server)
3. Click **Start** for **MySQL** (database server)

### Step 2: Verify Database Connection
- Database config is in `backend/db.php`:
```
php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'capstone_db');
```

### Step 3: Import Database Schema
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: `capstone_db`
3. Click **Import**
4. Select and import: `backend/schema.sql`
5. (Optional) Import: `backend/seed_events.sql` for sample data

### Step 4: Run the Application
Open your browser and go to:
```
http://localhost/capstone/frontend/index.html
```

Or for admin panel:
```
http://localhost/capstone/frontend/admin_login.html
```

---

## Default Admin Login
- **Username:** admin
- **Password:** admin123

---

## Troubleshooting

### If database connection fails:
1. Check MySQL is running in XAMPP
2. Verify database `capstone_db` exists in phpMyAdmin
3. Check `backend/db.php` credentials (default: root/empty password)

### If pages are blank:
1. Check Apache is running
2. Check for PHP errors in XAMPP error log

### Quick Test:
Create a test file `test_db.php` in backend folder:
```
php
<?php
require 'db.php';
$db = get_db();
echo "Database connected successfully!";
?>
```

Access: http://localhost/capstone/backend/test_db.php

---

## Quick Commands

### Start XAMPP:
```
cmd
c:\xampp\xampp_start.exe
```

### Check MySQL is running:
- phpMyAdmin: http://localhost/phpmyadmin

### View project:
- Frontend: http://localhost/capstone/frontend/index.html
- Backend API: http://localhost/capstone/backend/api.php
