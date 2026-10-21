@echo off
cd /d "%~dp0.."
"C:\xampp\php\php.exe" -d extension=sodium -S localhost:8001 -t public_htm_v2 tools_v2/dev-router.php
