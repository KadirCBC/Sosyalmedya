@echo off
title Meydan Sosyal Agi - Localhost:5000
cd /d "%~dp0"

echo ======================================================================
echo    M E Y D A N   Y E R E L   S O S Y A L   A G   P L A T F O R M U
echo ======================================================================
echo.

REM PHP yolunu kontrol et ve XAMPP altindaysa PATH e ekle
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
        echo [OK] PHP yolu C:\xampp\php olarak tanimlandi.
    ) else (
        echo [HATA] Sisteminizde PHP bulunamadi! Lutfen XAMPP veya PHP yukleyiniz.
        pause
        exit /b 1
    )
)

REM .env dosyasini kontrol et
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo [OK] .env dosyasi olusturuldu.
    )
)

REM Veritabani dosyasini kontrol et
if not exist "database" mkdir database
if not exist "database\database.sqlite" (
    type nul > "database\database.sqlite"
    echo [OK] database\database.sqlite olusturuldu.
)

REM Tarayicida http://localhost:5000 adresini ac
echo [OK] Tarayicinizda http://localhost:5000 aciliyor...
start "" http://localhost:5000

echo.
echo ======================================================================
echo   Sunucu http://localhost:5000 adresinde aktif olarak calisiyor!
echo   Durdurmak icin bu pencerede CTRL + C tuslarina basiniz.
echo ======================================================================
echo.

REM Sunucuyu baslat
if exist "vendor\autoload.php" (
    php artisan serve --host=127.0.0.1 --port=5000
) else (
    php -S 127.0.0.1:5000 server.php
)

pause
