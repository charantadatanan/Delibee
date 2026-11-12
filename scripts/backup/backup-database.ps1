#Requires -Version 5.1
<#
.SYNOPSIS
    Create a PostgreSQL custom-format backup of the deliBEE database.

.DESCRIPTION
    Reads the DB_* settings from the project's .env file, writes a timestamped
    custom-format dump into storage\backups, verifies the archive with
    pg_restore --list, copies it to -OffsiteDir when set, and removes dumps
    older than -KeepDays. Designed to be run manually or from Windows Task
    Scheduler.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\backup-database.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\backup-database.ps1 -KeepDays 30

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\backup-database.ps1 -OffsiteDir "$env:USERPROFILE\OneDrive\delibee-backups"
#>
[CmdletBinding()]
param(
    [string]$OutputDir,
    [string]$OffsiteDir,
    [int]$KeepDays = 14,
    [string]$PgDump = "C:\Program Files\PostgreSQL\18\bin\pg_dump.exe",
    [string]$PgRestore
)

$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)

if (-not $OutputDir) {
    $OutputDir = Join-Path $projectRoot "storage\backups"
}

if (-not $OffsiteDir) {
    $OffsiteDir = $env:BACKUP_OFFSITE_DIR
}

$envFile = Join-Path $projectRoot ".env"

if (-not (Test-Path $envFile)) {
    throw "No .env file found at $envFile"
}

if (-not (Test-Path $PgDump)) {
    throw "pg_dump not found at '$PgDump'. Pass -PgDump <path to pg_dump.exe>."
}

if (-not $PgRestore) {
    $PgRestore = Join-Path (Split-Path -Parent $PgDump) "pg_restore.exe"
}

if (-not (Test-Path $PgRestore)) {
    throw "pg_restore not found at '$PgRestore'. Pass -PgRestore <path to pg_restore.exe>."
}

$config = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)\s*=\s*(.*)\s*$') {
        $config[$matches[1]] = $matches[2].Trim().Trim('"').Trim("'")
    }
}

foreach ($key in @("DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD")) {
    if (-not $config.ContainsKey($key) -or [string]::IsNullOrWhiteSpace($config[$key])) {
        throw "$key is missing from $envFile"
    }
}

New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$file = Join-Path $OutputDir ("delibee-{0}.dump" -f $timestamp)

$previousPassword = $env:PGPASSWORD
$env:PGPASSWORD = $config["DB_PASSWORD"]

try {
    & $PgDump `
        --host $config["DB_HOST"] `
        --port $config["DB_PORT"] `
        --username $config["DB_USERNAME"] `
        --format=custom `
        --file=$file `
        $config["DB_DATABASE"]

    if ($LASTEXITCODE -ne 0) {
        throw "pg_dump failed with exit code $LASTEXITCODE"
    }

    & $PgRestore --list $file | Out-Null

    if ($LASTEXITCODE -ne 0) {
        Remove-Item $file -Force
        throw "Backup failed verification: pg_restore could not read the archive."
    }
}
finally {
    $env:PGPASSWORD = $previousPassword
}

$sizeMb = [math]::Round((Get-Item $file).Length / 1MB, 2)
$cutoff = (Get-Date).AddDays(-$KeepDays)
$removed = 0

Get-ChildItem -Path $OutputDir -Filter "delibee-*.dump" |
    Where-Object { $_.LastWriteTime -lt $cutoff } |
    ForEach-Object {
        Remove-Item $_.FullName -Force
        $removed++
    }

Write-Host "Backup written: $file ($sizeMb MB)"
Write-Host "Removed $removed backup(s) older than $KeepDays day(s)."

if ($OffsiteDir) {
    New-Item -ItemType Directory -Force -Path $OffsiteDir | Out-Null

    $offsiteFile = Join-Path $OffsiteDir (Split-Path -Leaf $file)
    Copy-Item -LiteralPath $file -Destination $offsiteFile -Force

    $sourceHash = (Get-FileHash -LiteralPath $file -Algorithm SHA256).Hash
    $copyHash = (Get-FileHash -LiteralPath $offsiteFile -Algorithm SHA256).Hash

    if ($sourceHash -ne $copyHash) {
        Remove-Item -LiteralPath $offsiteFile -Force
        throw "Off-site copy verification failed."
    }

    $offsiteRemoved = 0
    Get-ChildItem -Path $OffsiteDir -Filter "delibee-*.dump" |
        Where-Object { $_.LastWriteTime -lt $cutoff } |
        ForEach-Object {
            Remove-Item $_.FullName -Force
            $offsiteRemoved++
        }

    Write-Host "Off-site copy: $offsiteFile ($sizeMb MB)"
    Write-Host "Removed $offsiteRemoved off-site backup(s) older than $KeepDays day(s)."
}
