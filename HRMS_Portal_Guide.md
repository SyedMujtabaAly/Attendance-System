## AIMS HRMS (PHP Version) – Portal Guide

### 1. Overview

This portal is a web-based Human Resource Management System focused on **attendance tracking** with **late detection** and **admin controls**. It has two main roles:

- **Employees** – can log in and view their own attendance and statistics.
- **Admins (HR/management)** – can see everyone’s attendance, import data from the punch machine (Excel/CSV), and correct records.

The system is implemented in **PHP, HTML, CSS** with a lightweight **SQLite** database.

---

### 2. User Roles and Capabilities

#### 2.1 Employee Portal (Normal users)

**Login**
- Employees log in using:
  - **Employee ID** (e.g. `EMP001`)
  - **Password**

**Dashboard (`dashboard.php`)**
- Shows **today’s status**:
  - `Checked In`, `Checked Out`, or `Not Punched In`
  - Late/On‑time badge based on shift start time
- Shows **today’s punch-in and punch-out times** (if present).
- Shows **monthly summary**:
  - Total **Present** days (has punch-in)
  - Total **Late** days
  - Total **Absent** days (in the selected month)
- Lists **all attendance entries for the current month** with status:
  - `On Time`, `Late`, or `Absent`.

> Note: Manual punch in/out buttons exist but the primary, accurate data source is the **imported file from the punch machine**.

**My Attendance Details (`employee_attendance.php`)**
- Employee can view **only their own** attendance.
- Filter by **month**.
- For each day:
  - Date
  - Punch In time
  - Punch Out time (if any)
  - Status (`On Time`, `Late`, `Absent`).

Employees **cannot**:
- See other employees’ records.
- Edit attendance times.
- Import or delete attendance.

---

#### 2.2 Admin Portal (Staff users)

Admins are users with `is_staff = 1`. They have full visibility and control.

**Admin Home / Navigation**
- After login:
  - **Dashboard** (same as employee for their own record).
  - **Attendance Report** – all employees, per month.
  - **Import Attendance** – upload Excel/CSV punch data.

**Attendance Report (`attendance_report.php`)**

For a selected **month** and optional **department**, admin sees one row per employee with:

- **Employee ID**
- **Name**
- **Department**
- **Present** – number of days with punch-in during that month.
- **Late** – number of late days in that month.
- **Late % (month)** – percentage of present days that are late.
- **Total Late (all time)** – total late days across full history.
- **Absent** – approximate days in month without punch-in.
- **Actions**
  - `View` – open detailed attendance page for that employee and month.

This lets HR quickly see:
- Who is consistently late.
- Who has many late arrivals over time.
- Department-wise attendance performance.

**Employee Attendance Details (Admin view)**

When an admin opens `employee_attendance.php` for an employee:

- Same table as employee sees (daily entries for the selected month).
- Plus an **admin-only summary** at the top:
  - **Total late days (all time)**.
  - **Late consistency** – e.g. *30% of 50 present days*.

Admins can also click **Edit** on any record to correct times.

**Edit Attendance (`admin_edit_attendance.php`)**

Admin can:
- Adjust **Punch In Time**.
- Adjust **Punch Out Time** (optional).
- System recalculates **is_late** using the employee’s assigned shift start time.

Use cases:
- Fix mistakes in imported data.
- Correct special cases (manual corrections approved by HR).

**Import Attendance (`import_attendance.php`)**

Purpose: Replace manual punching by importing a **file from the biometric punch machine**.

- Only admins can access this page.
- Supports **CSV** files (Excel → Save As → CSV).
- Flexible header mapping – reads columns by name.

**Required columns (by name):**
- `Employee ID` – must match user’s Employee ID / username.
- `Date` – e.g. `2026-02-18`.
- `Punch In Time` – e.g. `09:00`.

**Optional columns:**
- `Employee Name` – for reference only (not required for matching).
- `Punch Out Time` – if your machine exports it.

**Template:**
- A ready-made template file exists in the project:
  - `attendance_template.csv`
- You can download it directly from the **Import Attendance** page.

**Behavior on import:**
- For each row:
  - Finds the user by **Employee ID**.
  - Inserts or updates the `attendance` record for that employee + date.
  - Computes `is_late` by comparing punch-in time with the employee’s shift start time.
- Summary shows:
  - Number of successful rows.
  - Number of error rows (with messages like “Employee not found”).

---

### 3. Admin Accounts and Credentials

There are three admin accounts designed for testing/production setup:

#### 3.1 Default Admin (created by `setup.php`)

- **Employee ID**: `ADMIN`
- **Password**: `admin123`
- **Role**: Global admin (can access all admin features).

> Recommended: Change this password after first login.

#### 3.2 HR Admin 1

- **Employee ID**: `HRADMIN1`
- **Password**: `Admin@123`
- **First name**: `HR`
- **Last name**: `Manager`
- **Email**: `hradmin1@example.com`
- **Role**: Staff (full admin capabilities).

#### 3.3 HR Admin 2

- **Employee ID**: `HRADMIN2`
- **Password**: `Admin@456`
- **First name**: `Shift`
- **Last name**: `Supervisor`
- **Email**: `hradmin2@example.com`
- **Role**: Staff (full admin capabilities).

**How to create HRADMIN1 and HRADMIN2 in your database**

From the `hrms_php` folder, run:

```bash
php create_admin_users.php
```

This script will:
- Connect to the existing SQLite database (`data/hrms.sqlite`).
- Create `HRADMIN1` and `HRADMIN2` as staff admins **only if they do not already exist**.

You can then log in to the portal using those credentials.

---

### 4. Security Considerations

- Change all default/test admin passwords in production:
  - `ADMIN / admin123`
  - `HRADMIN1 / Admin@123`
  - `HRADMIN2 / Admin@456`
- Restrict access to:
  - `setup.php`
  - `create_admin_users.php`
  - Any database files under `data/`.
- Prefer HTTPS and secure cookies for real deployments.

---

### 5. How to Export This Guide as PDF

This file is `HRMS_Portal_Guide.md` in the `hrms_php` folder.

To create a **PDF**:

1. Open `HRMS_Portal_Guide.md` in VS Code, Cursor, or any Markdown viewer.
2. Use **Print** or **Export as PDF**:
   - VS Code / Cursor: “Print” → choose **Microsoft Print to PDF**.
   - Or paste the content into Word/Google Docs → **File → Download as PDF**.

The resulting PDF can be shared as the **official portal documentation** for admins and employees.

