# Attendly Quick Start Guide

## 🚀 Fastest Way to Run (Windows)

### Option 1: Double-click the batch file
1. Navigate to the `Attendance-System` folder
2. **Double-click `start.bat`**
3. Open your browser to **http://localhost:8080**

### Option 2: Use PowerShell script
1. Right-click in the `Attendance-System` folder
2. Select **"Open PowerShell window here"**
3. Run: `.\start.ps1`
4. Open your browser to **http://localhost:8080**

---

## 📋 Manual Steps (if scripts don't work)

### Step 1: Install PHP (if not installed)
1. Download PHP from: https://www.php.net/downloads
2. Choose **Windows downloads** → **VS16 x64 Non Thread Safe** (or Thread Safe)
3. Extract to `C:\php` (or any folder)
4. Add PHP to PATH:
   - Press `Win + X` → System → Advanced system settings
   - Click "Environment Variables"
   - Under "System variables", find "Path" → Edit
   - Add `C:\php` (or your PHP folder path)
   - Click OK on all dialogs
5. **Restart your terminal/PowerShell**

### Step 2: Verify PHP Installation
Open PowerShell or Command Prompt and run:
```bash
php --version
```
You should see PHP version info. If not, PHP is not in PATH.

### Step 3: Setup Database (First Time Only)
Open PowerShell/CMD in the `Attendance-System` folder and run:
```bash
php setup.php
```
This creates:
- `data/` folder
- `data/hrms.sqlite` database
- Default admin user (Employee ID: `ADMIN`, Password: `admin123`)

### Step 4: Start the Server
In the same folder, run:
```bash
php -S localhost:8080
```

You should see:
```
PHP 8.x.x Development Server started at http://localhost:8080
```

### Step 5: Open in Browser
Open your web browser and go to:
```
http://localhost:8080
```

---

## 🔑 Default Login Credentials

**Admin Account (can view Attendance Report):**
- **Employee ID:** `ADMIN`
- **Password:** `admin123`

**Note:** Change this password after first login!

---

## 📝 What You Can Do

1. **Login** with the admin account or register a new employee
2. **Dashboard** - See today's status, punch in/out, monthly summary
3. **Punch In/Out** - Record your attendance
4. **Attendance Report** (Admin only) - View all employees' attendance
5. **My Attendance** - View detailed attendance records

---

## 🛑 To Stop the Server

Press **Ctrl + C** in the terminal where the server is running.

---

## ❓ Troubleshooting

### "PHP is not recognized"
- PHP is not installed or not in PATH
- Install PHP and add it to PATH (see Step 1 above)
- Restart your terminal after adding to PATH

### "Database connection failed"
- Make sure you ran `php setup.php` first
- Check that `data/hrms.sqlite` exists
- Make sure the `data/` folder has write permissions

### "Port 8080 already in use"
- Another application is using port 8080
- Change the port in `start.bat` or `start.ps1`:
  - Change `localhost:8080` to `localhost:8081` (or any other port)
  - Update the browser URL accordingly

### "Permission denied" on Windows
- Right-click the `Attendance-System` folder → Properties → Security
- Make sure your user has "Full control" or at least "Modify" permissions

---

## 🌐 Using a Different Port

If port 8080 is busy, you can use any other port:

```bash
php -S localhost:3000
```

Then open: `http://localhost:3000`
