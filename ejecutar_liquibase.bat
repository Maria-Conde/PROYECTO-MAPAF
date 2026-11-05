@echo off
title PROYECTO MAPAF - Gestor de Migraciones Liquibase
color 0B
cls

:MENU
cls
echo ======================================================================
echo          PROYECTO MAPAF - GESTOR DE MIGRACIONES LIQUIBASE 4.27
echo        SENA - Centro de la Industria, la Empresa y los Servicios
echo                         Ficha: 3413988-ADSO
echo ======================================================================
echo.
echo   [1] Liquibase Update   - Aplicar ChangeSets pendientes y montar BD
echo   [2] Liquibase Status   - Ver estado de ChangeSets y sumas MD5
echo   [3] Liquibase Rollback - Revertir ultimo ChangeSet ejecutado
echo   [4] Liquibase Reset    - Reconstruccion y montaje limpio desde cero
echo   [5] Ver Changelog Maestro XML (db.changelog-master.xml)
echo   [6] Abrir Asistente Web (setup_database.php)
echo   [0] Salir
echo.
echo ======================================================================
echo.

set /p OPT="Seleccione una opcion (0-6) [Por defecto 1]: "
if "%OPT%"=="" set OPT=1

if "%OPT%"=="1" (
    cls
    "C:\xampp\php\php.exe" "%~dp0scripts\liquibase_runner.php" update
    pause
    goto MENU
)

if "%OPT%"=="2" (
    cls
    "C:\xampp\php\php.exe" "%~dp0scripts\liquibase_runner.php" status
    pause
    goto MENU
)

if "%OPT%"=="3" (
    cls
    set /p COUNT="Cuantos ChangeSets desea revertir? [1]: "
    if "%COUNT%"=="" set COUNT=1
    "C:\xampp\php\php.exe" "%~dp0scripts\liquibase_runner.php" rollback %COUNT%
    pause
    goto MENU
)

if "%OPT%"=="4" (
    cls
    echo [ADVERTENCIA] Se reinicializara toda la base de datos con Liquibase.
    set /p CONFIRM="Desea continuar? (S/N): "
    if /I "%CONFIRM%"=="S" (
        "C:\xampp\php\php.exe" "%~dp0scripts\liquibase_runner.php" reset
    )
    pause
    goto MENU
)

if "%OPT%"=="5" (
    start notepad "%~dp0db\changelog\db.changelog-master.xml"
    goto MENU
)

if "%OPT%"=="6" (
    start http://localhost/mapaf/setup_database.php
    goto MENU
)

if "%OPT%"=="0" (
    exit /b
)

goto MENU

