-- HRMS PHP - Database Schema (SQLite / MySQL compatible)
-- Run this once to create tables, or use setup.php

-- Departments
CREATE TABLE IF NOT EXISTS departments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Shifts
CREATE TABLE IF NOT EXISTS shifts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Users (employees): username = employee_id for login
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    department_id INTEGER,
    shift_id INTEGER,
    phone VARCHAR(15),
    is_active INTEGER DEFAULT 1,
    is_staff INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (shift_id) REFERENCES shifts(id)
);

-- Attendance
CREATE TABLE IF NOT EXISTS attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    date DATE NOT NULL,
    punch_in_time TIME,
    punch_out_time TIME,
    is_late INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, date),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Indexes for common queries
CREATE INDEX IF NOT EXISTS idx_attendance_user_date ON attendance(user_id, date);
CREATE INDEX IF NOT EXISTS idx_attendance_date ON attendance(date);

-- Seed default data (optional)
INSERT OR IGNORE INTO departments (id, name, description) VALUES (1, 'Operations', 'General operations');
INSERT OR IGNORE INTO departments (id, name, description) VALUES (2, 'Surveillance', 'Surveillance department');
INSERT OR IGNORE INTO shifts (id, name, start_time, end_time) VALUES (1, 'Morning', '09:00', '17:00');
INSERT OR IGNORE INTO shifts (id, name, start_time, end_time) VALUES (2, 'Evening', '17:00', '01:00');
