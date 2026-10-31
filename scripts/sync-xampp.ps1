param([string]$Target = 'C:\xampp\htdocs\zariya-saree-store')
$ErrorActionPreference = 'Stop'
$source = [IO.Path]::GetFullPath((Split-Path $PSScriptRoot -Parent))
$destination = [IO.Path]::GetFullPath($Target)
if ($destination -ne 'C:\xampp\htdocs\zariya-saree-store') { throw 'This helper only syncs the named Zariya XAMPP installation.' }
if (!(Test-Path -LiteralPath (Join-Path $source 'artisan'))) { throw 'Laravel source not found.' }
if (!(Test-Path -LiteralPath (Join-Path $destination '.env'))) { throw 'Set up the XAMPP .env before syncing.' }
# Copy source without overwriting private environment, runtime storage, or dependencies.
robocopy $source $destination /E /XD .git .local-tools laravel-conversion node_modules vendor storage .next .npm-cache previews /XF '.env' '.env.*' '*.log' '*.sqlite' /NFL /NDL /NJH /NJS /NP
if ($LASTEXITCODE -gt 7) { throw "Robocopy failed: $LASTEXITCODE" }
Write-Output 'XAMPP source and built assets updated. Private environment, storage and dependencies preserved.'
