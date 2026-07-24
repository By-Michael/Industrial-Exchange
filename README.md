# Industrial Exchange

A small school project — a web app where industries/businesses can list surplus
materials or products and other businesses can browse and message them about it.
Basically a simple marketplace for industrial goods.

Nothing fancy here, just plain PHP + MySQL, built to learn the basics of
login systems, CRUD, and sending emails from a web app.

## What it does

- Sign up / log in with email + password (passwords are hashed, not stored in plain text)
- Add, edit, and delete product listings (name, category, quantity, price, location, etc.)
- Browse all listed products on the market page
- Message another user about a product (basic buyer-seller chat per product)
- Forgot password? Get a 6-digit OTP code emailed to you to reset it
- Edit your profile (name/email) and change your password

## How it's built

- **PHP** for all the logic (no framework, just plain PHP)
- **MySQL** for the database (tables are auto-created on first run, see `database.php`)
- **PHPMailer** to send the OTP emails through Gmail SMTP
- **HTML/CSS** for the front end, nothing complicated

## Project structure

```
index.php           - all the HTML/pages (login, signup, market, profile, messages...)
functions.php       - all the logic (handles form submits, sessions, OTP, etc.)
database.php        - connects to MySQL and creates tables if they don't exist
config.example.php  - template for your credentials, safe to commit
style.css           - styling
PHPMailer/           - library used to send emails
```

## Setup

1. Make sure you have PHP and MySQL running (e.g. via XAMPP/WAMP/Laragon).
2. Copy `config.example.php` to `config.php` and fill in your own database and SMTP details:
   ```
   cp config.example.php config.php
   ```
3. For the SMTP part, if using Gmail, you need an **App Password** (not your normal
   Gmail password) — you can generate one from your Google Account security settings.
4. Put the project folder in your server's root (e.g. `htdocs` for XAMPP).
5. Open it in the browser, e.g. `http://localhost/industry`. The database and
   tables get created automatically the first time it runs.

## Notes

- This was made for a school assignment, so it's kept simple on purpose —
  no framework, no fancy architecture, just enough to show the core features working.
- `config.php` holds real credentials and is not included in git (see `.gitignore`).
  Use `config.example.php` as a reference for what values are needed.
