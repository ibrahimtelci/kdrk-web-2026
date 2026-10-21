param([string]$ProjectPath = 'D:\OTHER_COMPANY_2025\KDRK-web-2026')
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path -LiteralPath $ProjectPath).Path
$manifest = Get-Content -LiteralPath (Join-Path $PSScriptRoot 'original-hashes.json') -Raw | ConvertFrom-Json
$sourceRoot = Join-Path $PSScriptRoot 'files'
$sourceFiles = Get-ChildItem -LiteralPath $sourceRoot -Recurse -File
foreach ($file in $sourceFiles) {
    $relative = $file.FullName.Substring($sourceRoot.Length).TrimStart('\').Replace('\','/')
    $target = Join-Path $projectRoot $relative
    if ($manifest.PSObject.Properties.Name -contains $relative) {
        if ((Get-FileHash -LiteralPath $target -Algorithm SHA256).Hash -ne $manifest.$relative) { throw "Source changed: $relative. No application files changed." }
    } elseif (Test-Path -LiteralPath $target) { throw "Destination already exists: $relative" }
}
$backupRoot = Join-Path $projectRoot ('tools_v2\user-directory-backup-' + (Get-Date -Format 'yyyyMMdd-HHmmss'))
foreach ($property in $manifest.PSObject.Properties) {
    $backup = Join-Path $backupRoot $property.Name
    New-Item -ItemType Directory -Path (Split-Path -Parent $backup) -Force | Out-Null
    Copy-Item -LiteralPath (Join-Path $projectRoot $property.Name) -Destination $backup
}
& 'C:\xampp\php\php.exe' (Join-Path $PSScriptRoot 'migrate.php') $projectRoot
if ($LASTEXITCODE -ne 0) { throw 'Migration failed. Application files were not changed.' }
foreach ($file in $sourceFiles) {
    $relative = $file.FullName.Substring($sourceRoot.Length).TrimStart('\')
    $target = Join-Path $projectRoot $relative
    New-Item -ItemType Directory -Path (Split-Path -Parent $target) -Force | Out-Null
    Copy-Item -LiteralPath $file.FullName -Destination $target -Force
    Write-Output "Updated: $relative"
}
Write-Output "Backup: $backupRoot"
Write-Output 'User directory enabled. Refresh V2.'
