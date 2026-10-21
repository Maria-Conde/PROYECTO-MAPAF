@echo off
title PROYECTO MAPAF - Centro de Control de Infraestructura (XAMPP + PHP + MySQL)
color 0A
cls

:MAIN_MENU
cls
echo ======================================================================
echo          PROYECTO MAPAF - SISTEMA SCADA TACTICO ^& TELEMETRIA IIOT
echo        SENA - Centro de la Industria, la Empresa y los Servicios
echo                         Ficha: 3413988-ADSO
echo ======================================================================
echo.

:: 1. Verificar si existe C:\xampp
if not exist "C:\xampp" (
    echo [ERROR] No se encontro la carpeta C:\xampp en tu equipo.
    echo Asegurate de tener XAMPP instalado en C:\xampp.
    echo.
    pause
    exit /b
)

:: 2. Vincular archivos de backend a C:\xampp\htdocs\mapaf
if not exist "C:\xampp\htdocs\mapaf" (
    cmd /c mklink /J "C:\xampp\htdocs\mapaf" "%~dp0backend_php" 2>nul
    if not exist "C:\xampp\htdocs\mapaf" (
        mkdir "C:\xampp\htdocs\mapaf"
        xcopy /E /Y /I "%~dp0backend_php\*" "C:\xampp\htdocs\mapaf\" >nul
    )
) else (
    xcopy /E /Y /I /D "%~dp0backend_php\*" "C:\xampp\htdocs\mapaf\" >nul 2>nul
)

:: 3. Iniciar MySQL si no esta corriendo
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if not "%ERRORLEVEL%"=="0" (
    echo [*] Iniciando MySQL en puerto 3306...
    pushd "C:\xampp"
    if exist "mysql_start.bat" (
        start "" /min "C:\xampp\mysql_start.bat"
    ) else (
        start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    )
    popd
    timeout /t 3 /nobreak >nul
)

:: 4. Iniciar Apache si no esta corriendo
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if not "%ERRORLEVEL%"=="0" (
    echo [*] Iniciando Apache en puerto 80...
    pushd "C:\xampp"
    if exist "apache_start.bat" (
        start "" /min "C:\xampp\apache_start.bat"
    ) else (
        start "" "C:\xampp\apache\bin\httpd.exe" -d "C:\xampp\apache"
    )
    popd
    timeout /t 2 /nobreak >nul
)

echo  ESTADO DE SERVICIOS:
echo   - Apache (HTTP / Puerto 80) : ACTIVO
echo   - MySQL (TCP / Puerto 3306) : ACTIVO
echo   - Backend PHP 8.2           : VINCULADO en C:\xampp\htdocs\mapaf
echo   - Base de Datos             : mapaf_db (8 Tablas Relacionales)
echo.
echo ======================================================================
echo                     OPCIONES DEL SISTEMA MAPAF
echo ======================================================================
echo   [1] Abrir Dashboard SCADA en el Navegador (http://localhost/mapaf/)
echo   [2] Abrir Base de Datos en phpMyAdmin
echo   [3] Ejecutar Diagnostico CLI de Patrones, Antipatrones y Dominios
echo   [4] Reinstalar / Restaurar Base de Datos MySQL (setup_database.php)
echo   [5] Ver Estado de Infraestructura en JSON (api/status.php)
echo   [6] Abrir Manual de Infraestructura (INFRAESTRUCTURA.md)
echo   [0] Salir
echo ======================================================================
echo.

set /p OPT="Seleccione una opcion (0-6) [Por defecto 1]: "
if "%OPT%"=="" set OPT=1

if "%OPT%"=="1" (
    echo Abriendo SCADA Prototype...
    start http://localhost/mapaf/
    goto MAIN_MENU
)

if "%OPT%"=="2" (
    echo Abriendo phpMyAdmin...
    start http://localhost/phpmyadmin/index.php?route=/database/structure&db=mapaf_db
    goto MAIN_MENU
)

if "%OPT%"=="3" (
    cls
    echo Ejecutando bateria de pruebas de patrones en PHP...
    echo.
    if exist "C:\xampp\php\php.exe" (
        "C:\xampp\php\php.exe" "%~dp0test_all_patterns_live.php"
    )
    echo.
    pause
    goto MAIN_MENU
)

if "%OPT%"=="4" (
    cls
    echo Reinstalando y verificando base de datos mapaf_db...
    echo.
    if exist "C:\xampp\php\php.exe" (
        "C:\xampp\php\php.exe" "%~dp0backend_php\setup_database.php"
    )
    echo.
    pause
    goto MAIN_MENU
)

if "%OPT%"=="5" (
    start http://localhost/mapaf/api/status.php
    goto MAIN_MENU
)

if "%OPT%"=="6" (
    start notepad "%~dp0INFRAESTRUCTURA.md"
    goto MAIN_MENU
)

if "%OPT%"=="0" (
    echo Saliendo del Centro de Control MAPAF...
    exit /b
)

goto MAIN_MENU
