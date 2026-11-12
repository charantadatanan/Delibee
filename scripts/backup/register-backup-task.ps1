#Requires -Version 5.1
<#
.SYNOPSIS
    Register (or remove) a Windows Scheduled Task that runs the deliBEE database backup.

.DESCRIPTION
    Points Windows Task Scheduler at scripts\backup\backup-database.ps1 and runs
    it daily at -Time. The task runs as the current user and only while that user
    is logged on. Re-running this script replaces the existing task.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\register-backup-task.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\register-backup-task.ps1 -Time 22:00 -KeepDays 30

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\register-backup-task.ps1 -OffsiteDir "$env:USERPROFILE\OneDrive\delibee-backups"

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\register-backup-task.ps1 -Remove
#>
[CmdletBinding()]
param(
    [string]$TaskName = "Delibee Database Backup",
    [string]$Time = "21:30",
    [int]$KeepDays = 14,
    [string]$OutputDir,
    [string]$OffsiteDir,
    [string]$PgDump = "C:\Program Files\PostgreSQL\18\bin\pg_dump.exe",
    [switch]$Remove
)

$ErrorActionPreference = "Stop"

if ($Remove) {
    $existing = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue

    if ($null -eq $existing) {
        Write-Host "No scheduled task named '$TaskName'."
        return
    }

    Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false
    Write-Host "Removed scheduled task '$TaskName'."
    return
}

$backupScript = Join-Path $PSScriptRoot "backup-database.ps1"

if (-not (Test-Path $backupScript)) {
    throw "Backup script not found at $backupScript"
}

$arguments = "-NoProfile -ExecutionPolicy Bypass -File `"$backupScript`" -KeepDays $KeepDays -PgDump `"$PgDump`""

if ($OutputDir) {
    $arguments += " -OutputDir `"$OutputDir`""
}

if ($OffsiteDir) {
    $arguments += " -OffsiteDir `"$OffsiteDir`""
}

$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument $arguments `
    -WorkingDirectory $PSScriptRoot

$trigger = New-ScheduledTaskTrigger -Daily -At $Time

$settings = New-ScheduledTaskSettingsSet `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Hours 1)

Register-ScheduledTask `
    -TaskName $TaskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Description "Daily PostgreSQL backup for the deliBEE database" `
    -Force | Out-Null

$task = Get-ScheduledTask -TaskName $TaskName

Write-Host "Registered '$($task.TaskName)' ($($task.State)) to run daily at $Time."
Write-Host "Action: powershell.exe $arguments"
