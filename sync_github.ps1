# ==============================================================================
# PROYECTO MAPAF - Automatizador de Sincronizacion Segura con GitHub
# ==============================================================================

param(
    [string]$Message = ""
)

$ErrorActionPreference = "Stop"
$projectRoot = $PSScriptRoot
Set-Location $projectRoot

Write-Host "======================================================================" -ForegroundColor Cyan
Write-Host "    PROYECTO MAPAF - SINCRONIZACION AUTOMATICA CON GITHUB" -ForegroundColor Cyan
Write-Host "======================================================================" -ForegroundColor Cyan

# 1. Comprobar repositorio y rama
$currentBranch = (git branch --show-current).Trim()
if (-not $currentBranch) {
    Write-Host "[ERROR] No se pudo determinar la rama de Git actual." -ForegroundColor Red
    exit 1
}

# 2. Detectar cambios pendientes
$statusPorcelain = (git status --porcelain)
$hasUncommittedChanges = ($statusPorcelain.Count -gt 0 -and $statusPorcelain -ne "")
$commitsAhead = (git log origin/$currentBranch..HEAD --oneline 2>$null)

if (-not $hasUncommittedChanges -and -not $commitsAhead) {
    Write-Host "[INFO] El arbol de trabajo esta limpio y el repositorio ya esta al dia con GitHub." -ForegroundColor Green
    Write-Host "Rama: $currentBranch | Sin cambios pendientes." -ForegroundColor White
    exit 0
}

# 3. Validar Seguridad: Prevenir fuga de secretos
if ($hasUncommittedChanges) {
    $sensitivePatterns = @('\.env$', '\.key$', '\.pem$', 'credentials\.json')
    foreach ($line in $statusPorcelain) {
        $file = $line.Substring(3).Trim()
        foreach ($pattern in $sensitivePatterns) {
            if ($file -match $pattern -and $file -notmatch '\.env\.example') {
                Write-Host "[ALERTA DE SEGURIDAD] Se bloqueo la subida: Archivo sensible detectado: $file" -ForegroundColor Red
                Write-Host "Asegurate de excluir este archivo en .gitignore antes de continuar." -ForegroundColor Yellow
                exit 1
            }
        }
    }

    # 4. Comprobacion de sintaxis PHP en archivos modificados
    $phpExe = "C:\xampp\php\php.exe"
    if (Test-Path $phpExe) {
        $phpFiles = git status --porcelain | Where-Object { $_ -match '\.php$' } | ForEach-Object { $_.Substring(3).Trim() }
        foreach ($pf in $phpFiles) {
            $fullPath = Join-Path $projectRoot $pf
            if (Test-Path $fullPath) {
                $lintOutput = & $phpExe -l $fullPath 2>&1
                if ($LASTEXITCODE -ne 0) {
                    Write-Host "[ERROR DE SINTAXIS] Fallo la verificacion en $pf :" -ForegroundColor Red
                    Write-Host $lintOutput -ForegroundColor Red
                    exit 1
                }
            }
        }
    }

    # 5. Generar mensaje de commit si no fue proporcionado
    if (-not $Message) {
        $changedCount = ($statusPorcelain | Measure-Object).Count
        $Message = "chore: sincronizacion automatica de archivos en MAPAF"
    }

    # 6. Preparar y realizar commit
    $env:MAPAF_SYNCING = "1"
    git add .
    git commit -m "$Message"
    $commitCode = $LASTEXITCODE
    $env:MAPAF_SYNCING = "0"
    if ($commitCode -ne 0) {
        Write-Host "[ERROR] No se pudo crear el commit en Git." -ForegroundColor Red
        exit 1
    }
    Write-Host "OK - Commit creado: $Message" -ForegroundColor Green
}

# 7. Subir a GitHub (git push)
Write-Host "[*] Enviando commits a origin/$currentBranch..." -ForegroundColor Yellow

$env:GIT_TERMINAL_PROMPT = "0"
$env:GCM_INTERACTIVE = "never"
$origPref = $ErrorActionPreference
$ErrorActionPreference = "SilentlyContinue"
$pushOutput = (git push origin $currentBranch 2>&1) | Out-String
$pushCode = $LASTEXITCODE
$ErrorActionPreference = $origPref

Write-Host "======================================================================" -ForegroundColor Cyan
if ($pushCode -eq 0) {
    Write-Host "  [EXITO] SINCRONIZACION CON GITHUB COMPLETADA CORRECTAMENTE" -ForegroundColor Green
    Write-Host "======================================================================" -ForegroundColor Cyan
    Write-Host "  - Estado:            SINCRONIZADO (100% al dia)" -ForegroundColor Green
    Write-Host "  - Rama:              $currentBranch" -ForegroundColor White
    Write-Host "  - Repositorio:       https://github.com/Maria-Conde/PROYECTO-MAPAF" -ForegroundColor White
    Write-Host "  - Ultimo Commit:     $((git log -1 --oneline))" -ForegroundColor White
    exit 0
} else {
    Write-Host "  [AVISO] COMMITS GUARDADOS LOCALMENTE, PERO REQUIERE AUTENTICACION" -ForegroundColor Yellow
    Write-Host "======================================================================" -ForegroundColor Cyan
    Write-Host "  - Estado:            Commits guardados localmente; Push en espera" -ForegroundColor Yellow
    Write-Host "  - Rama:              $currentBranch" -ForegroundColor White
    Write-Host "  - Ultimo Commit:     $((git log -1 --oneline))" -ForegroundColor White
    Write-Host "  - Detalle:           $pushOutput" -ForegroundColor Red
    Write-Host ""
    Write-Host "  [SOLUCION]: Ejecuta 'subir_a_github.bat' para autenticar tu cuenta una sola vez." -ForegroundColor Yellow
    exit 2
}
