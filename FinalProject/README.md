# ECOSCAN - Local Setup (PHP + MySQL)

This project contains a front-end and PHP endpoints for sign-up and login that store data in a MySQL database.

## Requirements
- XAMPP (or WAMP) with Apache and MySQL
- Place this project in your web server folder, e.g. `C:\xampp\htdocs\FinalProject`

## Quick setup
1. Start Apache and MySQL in XAMPP/WAMP.
2. Create the database:
   - Option A (phpMyAdmin): Open `http://localhost/phpmyadmin`, create a new database named `ecoscan`, then import the `db.sql` file included in this project (use the Import tab).
   - Option B (SQL): Run the SQL in `db.sql` to create the `ecoscan` database and tables.
3. Ensure `db_connect.php` credentials match your MySQL configuration (defaults assume `root` with empty password).
4. Open `http://localhost/FinalProject/index.html` in your browser.

## Available endpoints
- `signup.php` — POST: `name`, `email`, `password`, `confirm_password`, `admin_code`, `pet_name`, `birth_city`, `mother_maiden`
- `login.php` — POST: `email`, `password`
- `dashboard.php` — protected page, redirects to `index.html` if not authenticated
- `logout.php` — destroys session and redirects back to home

## Notes
- Passwords are hashed with PHP `password_hash`.
- Responses are JSON for AJAX handlers in `script.js`.
- After creating the database, you can (optionally) remove or move `db.sql` from the webroot for safety.

If you'd like, I can add server-side validation, CSRF protection, or email verification next. I can also help test the signup/login flow locally.
