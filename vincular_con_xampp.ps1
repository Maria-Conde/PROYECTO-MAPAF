# ==============================================================================
# PROYECTO MAPAF - Vinculador y Desplegador a XAMPP (Apache + PHP + MySQL)
# SENA ADSO - Ficha 3413988
# ==============================================================================

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "          PROYECTO MAPAF - VINCULACIÓN DIRECTA CON XAMPP              " -ForegroundColor Cyan
Write-Host "        SENA - Centro de la Industria, la Empresa y los Servicios     " -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host ""

$xamppPath = "C:\xampp"
$projectRoot = $PSScriptRoot
$backendSource = Join-Path $projectRoot "backend_php"
$htdocsTarget = Join-Path $xamppPath "htdocs\mapaf"

# 1. Verificar existencia de C:\xampp
if (-not (Test-Path $xamppPath)) {
    Write-Host "[ERROR] No se encontró XAMPP en $xamppPath." -ForegroundColor Red
    Write-Host "Por favor verifica la ruta de instalación de XAMPP." -ForegroundColor Yellow
    exit 1
}

Write-Host "[1/5] XAMPP detectado en: $xamppPath" -ForegroundColor Green

# 2. Copiar y vincular archivos a C:\xampp\htdocs\mapaf
Write-Host "[2/5] Vinculando y copiando archivos a $htdocsTarget..." -ForegroundColor Yellow

if (-not (Test-Path $htdocsTarget)) {
    New-Item -ItemType Directory -Path $htdocsTarget -Force | Out-Null
}

Copy-Item -Path "$backendSource\*" -Destination $htdocsTarget -Recurse -Force
Write-Host "      ✓ Archivos copiados: index.php, api/, patterns/, auth/, domain/, antipatterns/" -ForegroundColor Green

# 3. Iniciar MySQL si no está activo
Write-Host "[3/5] Verificando servicio de Base de Datos MySQL (Puerto 3306)..." -ForegroundColor Yellow
$mysqlProc = Get-Process -Name "mysqld" -ErrorAction SilentlyContinue

if ($mysqlProc) {
    Write-Host "      ✓ MySQL ya está en ejecución (PID: $($mysqlProc.Id))." -ForegroundColor Green
} else {
    Write-Host "      Iniciando MySQL..." -ForegroundColor Yellow
    $mysqlBat = Join-Path $xamppPath "mysql_start.bat"
    if (Test-Path $mysqlBat) {
        Start-Process -FilePath $mysqlBat -WorkingDirectory $xamppPath -WindowStyle Hidden
    } else {
        Start-Process -FilePath "$xamppPath\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=$xamppPath\mysql\bin\my.ini --standalone" -WorkingDirectory $xamppPath -WindowStyle Hidden
    }
    Start-Sleep -Seconds 3
    Write-Host "      ✓ MySQL iniciado en segundo plano." -ForegroundColor Green
}

# 4. Inicializar Base de Datos MySQL (mapaf_db)
Write-Host "[4/5] Ejecutando setup_database.php para crear mapaf_db en MySQL..." -ForegroundColor Yellow
$phpExe = Join-Path $xamppPath "php\php.exe"
$setupScript = Join-Path $htdocsTarget "setup_database.php"

if (Test-Path $phpExe) {
    & $phpExe $setupScript
} else {
    Write-Host "      [AVISO] No se encontró php.exe en $phpExe" -ForegroundColor Yellow
}

# 5. Iniciar Apache si no está activo
Write-Host "[5/5] Verificando servidor web Apache..." -ForegroundColor Yellow
$apacheProc = Get-Process -Name "httpd" -ErrorAction SilentlyContinue

if ($apacheProc) {
    Write-Host "      ✓ Apache ya está en ejecución." -ForegroundColor Green
} else {
    Write-Host "      Iniciando Apache..." -ForegroundColor Yellow
    $apacheBat = Join-Path $xamppPath "apache_start.bat"
    if (Test-Path $apacheBat) {
        Start-Process -FilePath $apacheBat -WorkingDirectory $xamppPath -WindowStyle Hidden
    } else {
        Start-Process -FilePath "$xamppPath\apache\bin\httpd.exe" -ArgumentList "-d $xamppPath\apache" -WorkingDirectory $xamppPath -WindowStyle Hidden
    }
    Start-Sleep -Seconds 2
    Write-Host "      ✓ Apache iniciado en segundo plano." -ForegroundColor Green
}

# Abrir XAMPP Control Panel si está cerrado
$controlProc = Get-Process -Name "xampp-control" -ErrorAction SilentlyContinue
if (-not $controlProc) {
    $controlExe = Join-Path $xamppPath "xampp-control.exe"
    if (Test-Path $controlExe) {
        Start-Process -FilePath $controlExe
    }
}

Write-Host ""
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "  ¡PROYECTO MAPAF VINCULADO EXITOSAMENTE A XAMPP!                    " -ForegroundColor Green
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "  - Carpeta en XAMPP: C:\xampp\htdocs\mapaf\                         " -ForegroundColor White
Write-Host "  - Base de datos MySQL: mapaf_db (Puerto 3306)                      " -ForegroundColor White
Write-Host "  - Servidor Apache: Activo en http://localhost/mapaf/               " -ForegroundColor White
Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Abriendo la plataforma en tu navegador..." -ForegroundColor Yellow

Start-Process "http://localhost/mapaf/"
