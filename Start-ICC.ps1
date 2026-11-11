param([string]$PhpPath = '')
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
if (-not $PhpPath) {
    $phpCommand=Get-Command php -ErrorAction SilentlyContinue
    if ($phpCommand) { $PhpPath=$phpCommand.Source }
    elseif (Test-Path -LiteralPath 'C:\OSPanel\modules\PHP-8.4\php.exe') { $PhpPath='C:\OSPanel\modules\PHP-8.4\php.exe' }
    else { throw 'Установите PHP 8.3+ или укажите -PhpPath.' }
}
if (-not (Test-Path -LiteralPath 'vendor\autoload.php')) { throw 'Сначала выполните composer install.' }
foreach ($path in @('bootstrap\cache','storage\framework\cache\data','storage\framework\sessions','storage\framework\views','storage\logs','storage\app\private')) { New-Item -ItemType Directory -Path $path -Force | Out-Null }
if (-not (Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.sqlite.example' -Destination '.env'
    New-Item -ItemType File -Path 'database\database.sqlite' -Force | Out-Null
    & $PhpPath artisan key:generate
    if ($LASTEXITCODE) { throw 'Не удалось создать APP_KEY.' }
    & $PhpPath artisan migrate --seed
    if ($LASTEXITCODE) { throw 'Не удалось создать базу. Проверьте расширения PHP.' }
}
$env:LARAVEL_PUBLIC_PATH=Join-Path $PSScriptRoot 'public'
$runtimeTemp=Join-Path $PSScriptRoot 'storage\framework'
$apiProcess=$null
$clientProcess=$null
try {
    $apiProcess=Start-Process -FilePath $PhpPath -ArgumentList @('-d',('sys_temp_dir="'+$runtimeTemp+'"'),'-d','upload_max_filesize=6M','-d','post_max_size=8M','-S','127.0.0.1:8000','-t','public','vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php') -WorkingDirectory $PSScriptRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput 'storage\logs\server.out' -RedirectStandardError 'storage\logs\server.err'
    $clientProcess=Start-Process -FilePath $PhpPath -ArgumentList @('-S','127.0.0.1:5173','-t','client') -WorkingDirectory $PSScriptRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput 'storage\logs\client.out' -RedirectStandardError 'storage\logs\client.err'
    Start-Sleep -Seconds 1
    if ($apiProcess.HasExited -or $clientProcess.HasExited) { throw 'Сервер не запустился. Проверьте порты 8000/5173 и storage/logs/*.err.' }
    Write-Host 'ICC: http://127.0.0.1:5173'
    Write-Host 'API: http://127.0.0.1:8000/api/health'
    Write-Host 'Аккаунты: admin@icc.test, creator@icc.test, user@icc.test. Пароль: DemoPass123!'
    Start-Process 'http://127.0.0.1:5173'
    [void](Read-Host 'Нажмите Enter, чтобы остановить оба сервера')
} finally {
    foreach ($process in @($apiProcess,$clientProcess)) { if ($process -and -not $process.HasExited) { Stop-Process -Id $process.Id -ErrorAction SilentlyContinue } }
}
