@echo off
title PROYECTO MAPAF - Iniciar XAMPP (Apache + PHP + MySQL)
color 0A
echo ======================================================================
echo          PROYECTO MAPAF - SISTEMA SCADA TACTICO ^& TELEMETRIA
echo        SENA - Centro de la Industria, la Empresa y los Servicios
echo                         Ficha: 3413988-ADSO
echo ======================================================================
echo.

:: 1. Verificar si existe C:\xampp
if not exist "C:\xampp" (
    echo [ERROR] No se encontro la carpeta C:\xampp en tu equipo.
    echo Asegurate de tener XAMPP instalado en C:\xampp.
    pause
    exit /b
)

:: 2. Iniciar MySQL si no esta corriendo
echo [1/3] Verificando servicio MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo  - MySQL ya esta en ejecucion.
) else (
    echo  - Iniciando MySQL desde XAMPP...
    start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    timeout /t 3 /nobreak >nul
)

:: 3. Iniciar Apache si no esta corriendo
echo [2/3] Verificando servidor Apache...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo  - Apache ya esta en ejecucion.
) else (
    echo  - Iniciando Apache desde XAMPP...
    start "" /B "C:\xampp\apache\bin\httpd.exe"
    timeout /t 3 /nobreak >nul
)

:: 4. Sincronizar archivos a htdocs
echo [3/3] Desplegando archivos a C:\xampp\htdocs\mapaf...
if not exist "C:\xampp\htdocs\mapaf" mkdir "C:\xampp\htdocs\mapaf"
xcopy /E /Y /I "%~dp0backend_php\*" "C:\xampp\htdocs\mapaf\" >nul

echo.
echo ======================================================================
echo  TODO LISTO: Apache, PHP 8 y MySQL mapaf_db estan activos.
echo ======================================================================
echo.
echo Abriendo PROYECTO MAPAF en el navegador...
start http://localhost/mapaf/

echo.
echo Presiona cualquier tecla para cerrar esta ventana (los servidores seguiran activos).
pause >nul
