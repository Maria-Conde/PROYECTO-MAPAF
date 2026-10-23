@echo off
title PROYECTO MAPAF - Subir Cambios a GitHub
color 0B
cls
echo ======================================================================
echo          PROYECTO MAPAF - SINCRONIZACION CON GITHUB
echo ======================================================================
echo.
cd /d "%~dp0"
echo Enviando commits a https://github.com/Maria-Conde/PROYECTO-MAPAF.git...
echo.
git push origin main
echo.
if %ERRORLEVEL% EQU 0 (
    echo ======================================================================
    echo   [EXITO] Los cambios se han subido correctamente a tu repositorio!
    echo ======================================================================
) else (
    echo [AVISO] Si te solicita inicio de sesion, completa la autorizacion en
    echo la ventana de tu navegador para que GitHub acepte el envio.
)
echo.
pause
