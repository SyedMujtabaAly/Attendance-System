# Attendly — Attendance Management System

A lightweight, role-based attendance web application built with PHP and SQLite. Attendly gives employees a clean self-service dashboard and gives HR staff the reporting and data-management tools needed to review attendance at a glance.

> Portfolio project: this repository demonstrates server-rendered PHP, secure session authentication, relational data modeling, role-based authorization, CSV processing, and responsive interface design without a framework.

## What it does

### Employee experience

- Secure registration and Employee ID login
- One-click punch in and punch out
- Automatic late detection based on assigned shifts
- Monthly present, late, and weekday absence summaries
- Personal attendance history with status indicators

### HR and admin experience

- Department and month filters
- Team attendance and punctuality reporting
- Per-employee attendance history
- Authorized attendance corrections
- CSV import with flexible columns and row-level feedback

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 7.4+ |
| Database | SQLite with PDO |
| Frontend | Semantic HTML5 and responsive CSS |
| Authentication | PHP sessions and password hashing |
| Data exchange | CSV import |

## Security and quality highlights

- Prepared statements for database access
- PHP password hashing and verification
- CSRF tokens on state-changing forms
- Session ID rotation after login
- HTTP-only, SameSite session cookies
- Output escaping to reduce XSS risk
- Server-side role checks for HR-only features
- Unique database constraints to prevent duplicate daily records

## Quick start

### Requirements

- PHP 7.4 or newer
- PDO SQLite and SQLite3 PHP extensions

### Run locally

```bash
git clone <your-repository-url>
cd Attendance-System
php setup.php
php -S localhost:8080
```

Open [http://localhost:8080](http://localhost:8080).

On Windows, run `start.bat` or `./start.ps1`; either script initializes the database when necessary and starts the development server.

### Demo administrator

The setup command creates a local demo administrator when the user table is empty:

```text
Employee ID: ADMIN
Password:    admin123
```

This credential is intended for local demonstration only. Change or remove it before any public deployment.

## Project structure

```text
Attendance-System/
├── config/                  # Database connection and shared helpers
├── css/                     # Responsive application styles
├── data/                    # Local SQLite runtime data (gitignored)
├── includes/                # Shared header and footer
├── sql/                     # Schema, indexes, and starter data
├── dashboard.php            # Employee overview and punch actions
├── attendance_report.php    # HR reporting workspace
├── employee_attendance.php  # Employee record detail
├── import_attendance.php    # CSV import workflow
├── setup.php                # Local database initialization
└── index.php                # Product landing page
```

## CSV import format

Download `attendance_template.csv` or provide a CSV with these headers:

```csv
Employee ID,Employee Name,Date,Punch In Time,Punch Out Time
EMP001,Alex Morgan,2026-09-28,08:55,17:04
```

`Employee ID`, `Date`, and `Punch In Time` are required. Employee name and punch-out time are optional.

## Data model

- **departments** define organizational groups.
- **shifts** define start and end times.
- **users** represent employees and their role, department, and shift.
- **attendance** stores one record per employee per date.

## Production considerations

This project is designed as a portfolio prototype. Before production use:

1. Serve it behind HTTPS and configure secure headers.
2. Move secrets and environment-specific settings to environment variables.
3. Disable public access to setup and maintenance scripts.
4. Replace the demo administrator credential.
5. Add rate limiting, password recovery, audit logs, and automated tests.
6. Use a production database and web server instead of PHP's built-in server.

## Roadmap

- [ ] Paid-time-off and holiday calendars
- [ ] Exportable PDF/CSV reports
- [ ] Employee and shift administration screens
- [ ] Audit trail for attendance corrections
- [ ] Automated unit and integration tests

## License

Add the license that matches how you want others to use this portfolio project before publishing it publicly.
