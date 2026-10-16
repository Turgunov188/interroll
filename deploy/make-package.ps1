# Inter Roll - hostingga yuklash uchun arxiv yig'adi.
#   PowerShell'da loyiha papkasidan:   powershell -ExecutionPolicy Bypass -File deploy\make-package.ps1
# Natija: deploy\interroll-hosting.zip  (ichida laravel\ va public_html\ papkalari)
# Lokal loyihaga tegmaydi: hammasi deploy\build ichida yig'iladi, vendor u yerda --no-dev bilan o'rnatiladi.

$ErrorActionPreference = 'Stop'
$root  = Split-Path -Parent $PSScriptRoot
$build = Join-Path $PSScriptRoot 'build'
$app   = Join-Path $build 'laravel'
$web   = Join-Path $build 'public_html'
$zip   = Join-Path $PSScriptRoot 'interroll-hosting.zip'

Write-Host "1/5  Tozalash..."
if (Test-Path $build) { Remove-Item $build -Recurse -Force }
if (Test-Path $zip)   { Remove-Item $zip -Force }
New-Item -ItemType Directory -Path $app, $web | Out-Null

Write-Host "2/5  Loyiha fayllari (vendor, .env, testlar, loglarsiz)..."
$excludeDirs = @(
    "$root\vendor", "$root\node_modules", "$root\.idea", "$root\.git", "$root\.vscode",
    "$root\deploy", "$root\tests", "$root\public",
    "$root\storage\logs", "$root\storage\framework\cache\data",
    "$root\storage\framework\sessions", "$root\storage\framework\views"
)
$excludeFiles = @('.env', '.env.backup', '.phpunit.result.cache', '*.log', 'database.sqlite')
robocopy $root $app /E /NFL /NDL /NJH /NJS /NP /XD $excludeDirs /XF $excludeFiles | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy xato bilan tugadi ($LASTEXITCODE)" }

# empty folders Laravel needs
foreach ($d in 'storage\logs', 'storage\framework\cache\data', 'storage\framework\sessions', 'storage\framework\views', 'bootstrap\cache') {
    New-Item -ItemType Directory -Force -Path (Join-Path $app $d) | Out-Null
}

# caches built on this computer hold Windows paths and dev packages: the hosting builds its own
Get-ChildItem (Join-Path $app 'bootstrap\cache') -File -Filter *.php | Remove-Item -Force

# old page files (Uzbek URLs, before English ones) are not needed on the hosting
$site = Join-Path $app 'resources\site'
Get-ChildItem $site -File -Filter *.html | Remove-Item -Force
$old = 'aloqa','blog-shore-a','ishlar','katalog','kuzatish','mahsulot','qayta-qoplash','tanlash-ustasi','zavod','order-tracking'
foreach ($lang in 'ru','uz','en','tr') {
    foreach ($name in $old) { Remove-Item (Join-Path $site "$lang\$name.html") -Force -ErrorAction SilentlyContinue }
}

Write-Host "3/5  public_html (css, js, shriftlar + hosting uchun index.php va .htaccess)..."
robocopy "$root\public" $web /E /NFL /NDL /NJH /NJS /NP /XD "$root\public\storage" /XF index.php .htaccess hot | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy xato bilan tugadi ($LASTEXITCODE)" }
Copy-Item "$PSScriptRoot\public_html\index.php" $web -Force
Copy-Item "$PSScriptRoot\public_html\.htaccess" $web -Force
Copy-Item "$PSScriptRoot\env.production.txt" (Join-Path $build 'env.production.txt') -Force

Write-Host "4/5  composer install --no-dev ..."
Push-Location $app
try {
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts
    if ($LASTEXITCODE -ne 0) { throw "composer install ishlamadi" }
    php artisan package:discover --ansi
} finally { Pop-Location }

Write-Host "5/5  Arxiv..."
$tar = Get-Command tar.exe -ErrorAction SilentlyContinue
if ($tar) {
    Push-Location $build
    try { tar.exe -a -c -f $zip laravel public_html env.production.txt } finally { Pop-Location }
} else {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($build, $zip)
}

$mb = [math]::Round((Get-Item $zip).Length / 1MB, 1)
Write-Host ""
Write-Host "Tayyor: $zip  ($mb MB)" -ForegroundColor Green
Write-Host "Keyingi qadam: deploy\README.md"
