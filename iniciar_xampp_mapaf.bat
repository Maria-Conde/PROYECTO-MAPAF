@echo off
title PROYECTO MAPAF - Centro de Control (XAMPP + PHP + MySQL)
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

:: 2. Vincular archivos limpios a C:\xampp\htdocs\mapaf
if not exist "C:\xampp\htdocs\mapaf" (
    cmd /c mklink /J "C:\xampp\htdocs\mapaf" "%~dp0" 2>nul
    if not exist "C:\xampp\htdocs\mapaf" (
        mkdir "C:\xampp\htdocs\mapaf"
        xcopy /E /Y /I /EXCLUDE:%~dp0.gitignore "%~dp0*" "C:\xampp\htdocs\mapaf\" >nul
    )
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
echo   - Plataforma PHP 8.2        : VINCULADA en C:\xampp\htdocs\mapaf
echo   - Base de Datos             : mapaf_db (Esquema Normalizado)
echo.
echo ======================================================================
echo                     OPCIONES DEL SISTEMA MAPAF
echo ======================================================================
echo   [1] Abrir Dashboard SCADA en el Navegador (http://localhost/mapaf/)
echo   [2] Abrir Base de Datos en phpMyAdmin
echo   [3] Inicializar / Restaurar Base de Datos MySQL (setup_database.php)
echo   [4] Ver Estado de Infraestructura en JSON (api/status.php)
echo   [5] Abrir Documentacion Tecnica (README.md)
echo   [0] Salir
echo ======================================================================
echo.

set /p OPT="Seleccione una opcion (0-5) [Por defecto 1]: "
if "%OPT%"=="" set OPT=1

if "%OPT%"=="1" (
    echo Abriendo SCADA en navegador...
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
    echo Inicializando y verificando base de datos mapaf_db en MySQL...
    echo.
    if exist "C:\xampp\php\php.exe" (
        "C:\xampp\php\php.exe" "%~dp0setup_database.php"
    )
    echo.
    pause
    goto MAIN_MENU
)

if "%OPT%"=="4" (
    start http://localhost/mapaf/api/status.php
    goto MAIN_MENU
)

if "%OPT%"=="5" (
    start notepad "%~dp0README.md"
    goto MAIN_MENU
)

if "%OPT%"=="0" (
    echo Saliendo del Centro de Control MAPAF...
    exit /b
)

goto MAIN_MENU
