# ==============================================================================
# PROYECTO MAPAF - Liquibase Migration Runner (PowerShell)
# SENA ADSO - Ficha 3413988 | Upstream SCADA & Telemetría IIoT
# ==============================================================================

param(
    [ValidateSet("update", "status", "rollback", "reset")]
    [string]$Command = "status",

    [int]$Count = 1
)

$projectRoot = $PSScriptRoot
Set-Location $projectRoot

$phpExe = "C:\xampp\php\php.exe"
if (-not (Test-Path $phpExe)) {
    Write-Host "[ERROR] No se encontró PHP en $phpExe" -ForegroundColor Red
    exit 1
}

$runnerScript = Join-Path $projectRoot "scripts\liquibase_runner.php"

if ($Command -eq "rollback") {
    & $phpExe $runnerScript $Command $Count
} else {
    & $phpExe $runnerScript $Command
}
