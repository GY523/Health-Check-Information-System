@echo off
REM ============================================
REM Deployment Script: Dev to XAMPP
REM ============================================
REM This script copies your development files to XAMPP htdocs
REM 
REM USAGE: Double-click this file or run from command prompt
REM 

echo ============================================
echo Deploying to XAMPP...
echo ============================================

REM Set source and destination paths
set SOURCE=c:\IDE\Health Check Information System
set DEST=c:\xampp\htdocs\server_loaning_system

REM Create destination folder if it doesn't exist
if not exist "%DEST%" mkdir "%DEST%"

REM Copy all files (excluding this script and documentation)
echo Copying files...
xcopy "%SOURCE%\*.php" "%DEST%\" /Y /E /EXCLUDE:%SOURCE%\exclude.txt
xcopy "%SOURCE%\*.sql" "%DEST%\" /Y
xcopy "%SOURCE%\config" "%DEST%\config\" /Y /E /I
xcopy "%SOURCE%\admin" "%DEST%\admin\" /Y /E /I
xcopy "%SOURCE%\user" "%DEST%\user\" /Y /E /I
xcopy "%SOURCE%\auth" "%DEST%\auth\" /Y /E /I
xcopy "%SOURCE%\includes" "%DEST%\includes\" /Y /E /I
xcopy "%SOURCE%\assets" "%DEST%\assets\" /Y /E /I

echo ============================================
echo Deployment Complete!
echo ============================================
echo Files copied to: %DEST%
echo Open browser: http://localhost/server_loaning_system/
echo ============================================

pause
