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

:: 2. Sincronizar archivos a C:\xampp\htdocs\mapaf
echo [1/3] Sincronizando archivos a C:\xampp\htdocs\mapaf...
if not exist "C:\xampp\htdocs\mapaf" mkdir "C:\xampp\htdocs\mapaf"
xcopy /E /Y /I "%~dp0backend_php\*" "C:\xampp\htdocs\mapaf\" >nul

:: 3. Iniciar MySQL si no esta activo
echo [2/3] Verificando MySQL...
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

:: 4. Lanzar servidor PHP integrado en puerto 8080
echo [3/3] Iniciando Servidor Web PHP en http://localhost:8080...
echo.
echo ======================================================================
echo  SERVIDOR ACTIVO EN: http://localhost:8080
echo  (Manten esta ventana abierta mientras uses la plataforma)
echo ======================================================================
echo.

start http://localhost:8080

"C:\xampp\php\php.exe" -S localhost:8080 -t "C:\xampp\htdocs\mapaf"
pause
