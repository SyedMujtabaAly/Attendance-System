# Attendly - PowerShell Start Script

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Attendly - Starting Application" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if PHP is installed
try {
    $phpVersion = php --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "PHP not found"
    }
    Write-Host "PHP found: $($phpVersion -split "`n" | Select-Object -First 1)" -ForegroundColor Green
} catch {
    Write-Host "ERROR: PHP is not installed or not in PATH." -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install PHP from: https://www.php.net/downloads" -ForegroundColor Yellow
    Write-Host "Or add PHP to your system PATH." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if database exists, if not run setup
if (-not (Test-Path "data\hrms.sqlite")) {
    Write-Host "Database not found. Running setup..." -ForegroundColor Yellow
    Write-Host ""
    php setup.php
    Write-Host ""
    Write-Host "Setup complete!" -ForegroundColor Green
    Write-Host ""
}

# Start PHP built-in server
Write-Host "Starting PHP server on http://localhost:8080" -ForegroundColor Green
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""
php -S localhost:8080
