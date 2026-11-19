# build-package.ps1
# Builds the mod_worldclocks install zip with 7-Zip. PowerShell's own zip makes entries
# Joomla's installer can't read; see Joomla-Brain/PACKAGE-BUILD-NOTES.md.
#
# The version comes from the manifest. integrity.json is generated first, and
# CHANGELOG.md + CHANGELOG.html go in at the zip root (the build stops if either
# is missing). Produces mod_worldclocks_v<version>_<yyyyMMdd>_<HHmm>.zip (working copy)
# and mod_worldclocks_v<version>.zip (the one to release).
#
# Usage:  .\build-package.ps1

$ErrorActionPreference = 'Stop'

$extName  = 'mod_worldclocks'
$srcDir   = Join-Path $PSScriptRoot '.'
$manifest = Join-Path $srcDir 'mod_worldclocks.xml'
$items    = @('mod_worldclocks.xml', 'LICENSE', 'integrity.json', 'language', 'media', 'services', 'src', 'tmpl')

$sevenZip = @(
    'C:\Program Files\7-Zip\7z.exe',
    'C:\Program Files (x86)\7-Zip\7z.exe'
) | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $sevenZip) { throw '7-Zip (7z.exe) not found. Install it from https://7-zip.org.' }

[xml]$xml = Get-Content $manifest -Raw -Encoding UTF8
$version  = $xml.SelectSingleNode('/extension/version').InnerText.Trim()
if (-not $version) { throw "No <version> in $manifest" }

foreach ($log in 'CHANGELOG.md', 'CHANGELOG.html') {
    if (-not (Test-Path (Join-Path $PSScriptRoot $log))) { throw "$log is missing; every release zip carries it." }
}

$integrityTool = @(
    (Join-Path $PSScriptRoot '.joomla-brain\tools\integrity\New-IntegrityList.ps1'),
    (Join-Path (Split-Path $PSScriptRoot -Parent) 'Joomla-Brain\tools\integrity\New-IntegrityList.ps1')
) | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $integrityTool) { throw 'Joomla-Brain tools/integrity not found.' }
& $integrityTool -ExtensionDir $srcDir -Manifest $manifest

$stage = Join-Path ([IO.Path]::GetTempPath()) ("build_{0}_{1}" -f $extName, [guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $stage | Out-Null
try {
    foreach ($item in $items) {
        $path = Join-Path $srcDir $item
        if (-not (Test-Path $path)) { throw "Missing $item in $srcDir" }
        Copy-Item $path -Destination $stage -Recurse
    }
    Copy-Item (Join-Path $PSScriptRoot 'CHANGELOG.md'), (Join-Path $PSScriptRoot 'CHANGELOG.html') -Destination $stage

    $stamp   = Get-Date -Format 'yyyyMMdd_HHmm'
    $zipPath = Join-Path $PSScriptRoot "${extName}_v${version}_${stamp}.zip"
    $clean   = Join-Path $PSScriptRoot "${extName}_v${version}.zip"
    Remove-Item $zipPath, $clean -Force -ErrorAction SilentlyContinue

    Push-Location $stage
    try {
        & $sevenZip a -tzip $zipPath * | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "7-Zip failed (exit $LASTEXITCODE)" }
    } finally {
        Pop-Location
    }
    Copy-Item $zipPath $clean
} finally {
    Remove-Item $stage -Recurse -Force
}

Write-Host "Built $zipPath"
Write-Host "Release copy $clean"
Write-Host ("SHA256 {0}" -f (Get-FileHash $clean -Algorithm SHA256).Hash)
