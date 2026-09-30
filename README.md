# 🏠 NestFinder

A property listing platform built with PHP and MySQL where users can list, browse, shortlist, compare, and manage properties, while admins oversee everything through a dedicated admin panel.

---

## ✨ Features

**Users**
- Signup / Login / Remember Me / Password change
- Upload properties with images & videos
- Filter by location, price, BHK, type, tenant
- Shortlist & compare properties side-by-side
- Report fake or suspicious listings
- Notifications for shortlists & reports on your listings

**Admins**
- Dashboard with stats & analytics
- Manage users, properties, shortlists, and reports
- Approve / reject / edit / delete listings (single or bulk)
- Site settings, maintenance mode & activity logs
- CSV export for shortlists

---

## 🛠 Tech Stack

PHP 8.2 · MySQL/MariaDB · HTML/CSS/JS · Font Awesome

---

## 🚀 Installation

1. **Clone into your web root**
   ```bash
   git clone https://github.com/yourusername/nestfinder.git
   ```

2. **Create upload folders**
   ```bash
   mkdir uploads uploads/profiles
   chmod 777 uploads -R
   ```

3. **Import the database**
   - Create a DB named `nestfinder` in phpMyAdmin
   - Import `nestfinder.sql`

4. **Configure `db.php`** (if needed)
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "nestfinder";
   ```

5. **Open in browser**
   ```
   http://localhost/nestfinder/
   ```

---

## 🔑 Admin Access

Sign up normally, then run this in phpMyAdmin:
```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

---

## 🔒 Security

- Bcrypt password hashing
- Prepared statements & input escaping
- Account lockout after 5 failed logins
- MIME-type validation on uploads
- Session-based auth with admin role check

---
📞 Contact
Project Maintainer: Daneshwari Kaplish
Email: kaplish.daneshwari@gmail.com
GitHub: @d-kaplish

⭐ **If you like this project, give it a star!**
