#Requires -Version 5.1
<#
.SYNOPSIS
    Create a PostgreSQL custom-format backup of the deliBEE database.

.DESCRIPTION
    Reads the DB_* settings from the project's .env file, writes a timestamped
    custom-format dump into storage\backups, and removes dumps older than
    -KeepDays. Designed to be run manually or from Windows Task Scheduler.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup-database.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup-database.ps1 -KeepDays 30
#>
[CmdletBinding()]
param(
    [string]$OutputDir,
    [int]$KeepDays = 14,
    [string]$PgDump = "C:\Program Files\PostgreSQL\18\bin\pg_dump.exe"
)

$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot

if (-not $OutputDir) {
    $OutputDir = Join-Path $projectRoot "storage\backups"
}

$envFile = Join-Path $projectRoot ".env"

if (-not (Test-Path $envFile)) {
    throw "No .env file found at $envFile"
}

if (-not (Test-Path $PgDump)) {
    throw "pg_dump not found at '$PgDump'. Pass -PgDump <path to pg_dump.exe>."
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
}
finally {
    $env:PGPASSWORD = $previousPassword
}

$cutoff = (Get-Date).AddDays(-$KeepDays)
$removed = 0

Get-ChildItem -Path $OutputDir -Filter "delibee-*.dump" |
    Where-Object { $_.LastWriteTime -lt $cutoff } |
    ForEach-Object {
        Remove-Item $_.FullName -Force
        $removed++
    }

$sizeMb = [math]::Round((Get-Item $file).Length / 1MB, 2)
Write-Host "Backup written: $file ($sizeMb MB)"
Write-Host "Removed $removed backup(s) older than $KeepDays day(s)."
