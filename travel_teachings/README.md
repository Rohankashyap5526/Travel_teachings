# TravelTeachings v2.0 — Production Deployment Guide

## 📁 Directory Structure

```
travelteachings/
├── .htaccess                  ← Apache security rules
├── index.php                  ← Homepage
├── study.php                  ← Study material listing
├── about.php                  ← About Dr. Renu Malra
├── contact.php                ← Contact / Ask Us form
├── admin.php                  ← Admin login (public)
├── dashboard.php              ← Admin dashboard (protected)
├── logout.php                 ← Admin logout
├── setup.sql                  ← Run once to create DB tables
│
├── app/
│   ├── config/
│   │   └── config.php         ← All configuration (DB, security, paths)
│   ├── core/
│   │   └── Database.php       ← PDO Singleton
│   ├── controllers/
│   │   └── Notes.php          ← Notes CRUD + AI context builder
│   └── helpers/
│       ├── Security.php       ← CSRF, sessions, rate-limiting, validation
│       └── Stats.php          ← Visitor & download tracking
│
├── api/
│   ├── chat.php               ← RAG chatbot (Groq API)
│   └── download.php           ← Secure download + tracking
│
├── includes/
│   ├── bootstrap.php          ← Loads all classes & starts session
│   ├── header.php             ← Shared HTML header + nav
│   └── footer.php             ← Shared footer + chatbot widget
│
├── assets/
│   ├── css/
│   │   ├── main.css           ← Full design system
│   │   └── admin.css          ← Admin extras
│   ├── js/
│   │   └── main.js            ← Hamburger, chatbot, search, scroll reveal
│   └── images/                ← Copy all PNG files here
│
└── notes/                     ← PDF upload directory (chmod 755)
```

## 🚀 Deployment Steps

### 1. Upload Files
Upload all files to your `public_html/` directory on Hostinger.

### 2. Copy Image Assets
Copy all PNG files from original project to `assets/images/`:
```
logo.png, k1.png, R67.png, f.png, i.png, R.png, l.png, RE.png, lock.png
```

### 3. Run Database Setup
In Hostinger → phpMyAdmin, open your database and run `setup.sql`.
This creates the `visits`, `downloads`, `admin_log`, and `settings` tables.

### 4. Configure DB Credentials
Edit `app/config/config.php` — update these constants:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');
```

### 5. Set Admin Credentials
In `app/config/config.php`, update:
```php
define('ADMIN_PASSWORD_HASH', password_hash('YourNewPassword', PASSWORD_BCRYPT));
define('ADMIN_USERNAME',      'YourAdminUsername');
```

### 6. Create Notes Directory
```bash
mkdir -p notes
chmod 755 notes
```

### 7. Set Up Groq API Key (for AI Chatbot)
- Go to https://console.groq.com → get a free API key
- Log into Admin Dashboard → Settings → paste your key
- OR set it directly in `app/config/config.php`:
  ```php
  define('GROQ_API_KEY', 'gsk_your_key_here');
  ```

## 🔐 Security Features

- **CSRF tokens** on every form
- **Password hashing** with bcrypt (cost 12)
- **Rate limiting** — 5 failed login attempts triggers 15-min lockout
- **Session hardening** — HttpOnly, SameSite=Strict, secure cookies
- **Input sanitization** — all user input sanitized via `Security::clean()`
- **File validation** — checks MIME type (not just extension), max 20MB
- **SQL injection prevention** — PDO prepared statements throughout
- **No credentials in HTML** — all DB passwords server-side only
- **HTTP security headers** — CSP, X-Frame-Options, X-XSS-Protection
- **Directory traversal protection** — filename regex validation
- **Timing-safe login** — prevents user enumeration

## ✨ New Features vs Original

| Feature | Original | v2.0 |
|---|---|---|
| UI Design | Basic CSS | Editorial Navy+Gold design system |
| Visits tracking | localStorage (per-browser) | **Server-side, real counts** |
| Downloads tracking | localStorage (per-browser) | **Server-side, real counts** |
| Admin password | Plaintext in PHP | **Bcrypt hashed** |
| DB credentials | Hardcoded everywhere | **Single config file** |
| CSRF protection | None | **On every form** |
| AI Chatbot | None | **RAG chatbot via Groq** |
| Notes search | None | **Live search + category filters** |
| Admin dashboard | Basic upload/delete | **Full analytics + logs** |
| File upload | No validation | **MIME + size validation** |
| Security headers | None | **Full CSP + headers** |
| Directory structure | Flat | **MVC-style organised** |
