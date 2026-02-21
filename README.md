# HRMS - PHP Version (HTML, PHP, CSS)

This is the same HRMS (Human Resource Management System) as the Django project, built with **HTML**, **PHP**, and **CSS**. It uses SQLite by default so you can run it without installing MySQL.

## Features

- **Home** – Landing page with Login / Register
- **Login / Register / Logout** – Employee ID and password
- **Dashboard** – Today’s status, Punch In / Punch Out, month summary, attendance list
- **Late detection** – Marks attendance as late if punch-in is after shift start time
- **Attendance Report** (staff only) – Filter by month and department, view all employees
- **Employee Attendance** – Detailed records for an employee (own or by staff)

## Requirements

- PHP 7.4+ (with PDO SQLite extension, usually enabled by default)

## Setup

1. **Run the one-time setup** (creates `data/` and SQLite database):

   ```bash
   php setup.php
   ```

   This also creates a default **admin** user:
   - **Employee ID:** `ADMIN`
   - **Password:** `admin123`
   - **is_staff:** Yes (can access Attendance Report)

2. **Run the built-in PHP server** (from the `hrms_php` folder):

   ```bash
   cd hrms_php
   php -S localhost:8080
   ```

3. Open **http://localhost:8080** in your browser.

## Project Structure

```
hrms_php/
├── config/
│   ├── database.php   # DB connection (SQLite / MySQL)
│   └── init.php       # Session, helpers, current user
├── css/
│   └── style.css      # Main styles
├── includes/
│   ├── header.php     # Layout header + nav
│   └── footer.php     # Layout footer
├── sql/
│   └── schema.sql     # Table definitions + seed data
├── data/              # Created by setup (SQLite DB)
├── index.php          # Home
├── login.php
├── register.php
├── logout.php
├── dashboard.php      # Employee dashboard
├── punch_in.php       # POST: punch in
├── punch_out.php      # POST: punch out
├── attendance_report.php   # Staff: report by month/department
├── employee_attendance.php # Attendance details per employee
├── setup.php          # One-time DB setup
└── README.md
```

## Using MySQL Instead of SQLite

1. Create a database (e.g. `hrms`).
2. In `config/database.php`, comment out the SQLite block and uncomment the MySQL block; set host, dbname, username, and password.
3. Run the SQL in `sql/schema.sql` in your MySQL client (adjust `AUTOINCREMENT` to `AUTO_INCREMENT` and `INTEGER` primary keys if needed for MySQL).
4. Create an admin user manually (set `is_staff = 1`) or adapt `setup.php` for MySQL.

## Making a User “Staff” (Admin)

Staff users can open **Attendance Report**. To set an existing user as staff, run:

```sql
UPDATE users SET is_staff = 1 WHERE username = 'EMPLOYEE_ID';
```

(Or use the default `ADMIN` / `admin123` account created by `setup.php`.)

## Security Note

- Change the default admin password after first login.
- In production, remove or restrict access to `setup.php`.
- Use HTTPS and secure session settings on a real server.
