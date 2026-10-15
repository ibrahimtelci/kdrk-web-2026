@echo off
rem KDRK yerel geliştirme sunucusu — http://localhost:8000
rem Önce XAMPP Control Panel'den MySQL'i başlatın.
cd /d "%~dp0.."
C:\xampp\php\php.exe -d extension=sodium -S localhost:8000 -t public_html tools\dev-router.php
