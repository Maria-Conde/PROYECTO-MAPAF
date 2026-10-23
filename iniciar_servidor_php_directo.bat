@echo off
title PROYECTO MAPAF - Servidor PHP Directo (Puerto 8080)
color 0B
cls
echo ======================================================================
echo          PROYECTO MAPAF - SERVIDOR PHP DIRECTO DE DESARROLLO
echo        SENA - Centro de la Industria, la Empresa y los Servicios
echo                         Ficha: 3413988-ADSO
echo ======================================================================
echo.

:: 1. Verificar PHP en C:\xampp\php\php.exe
if not exist "C:\xampp\php\php.exe" (
    echo [ERROR] No se encontro php.exe en C:\xampp\php\php.exe.
    pause
    exit /b
)

:: 2. Iniciar MySQL si no esta activo
echo [1/2] Verificando MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo  - MySQL ya esta activo en puerto 3306.
) else (
    echo  - Iniciando MySQL...
    pushd "C:\xampp"
    if exist "mysql_start.bat" (
        start "" /min "C:\xampp\mysql_start.bat"
    ) else (
        start "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    )
    popd
    timeout /t 2 /nobreak >nul
)

:: 3. Lanzar servidor PHP integrado directamente desde la raiz del proyecto
echo [2/2] Iniciando Servidor Web PHP en http://localhost:8080...
echo.
echo ======================================================================
echo  SERVIDOR ACTIVO EN: http://localhost:8080
echo  (Manten esta ventana abierta mientras uses la plataforma)
echo ======================================================================
echo.

start http://localhost:8080

"C:\xampp\php\php.exe" -S localhost:8080 -t "%~dp0"
pause
