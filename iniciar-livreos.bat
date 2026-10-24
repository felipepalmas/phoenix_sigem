@echo off
title LivreOS - Servidor Local
echo ========================================================
echo        Iniciando LivreOS - Servidor Local
echo ========================================================
echo.
echo Acesse no seu navegador: http://127.0.0.1:8000
echo Utilize suas credenciais autorizadas para login institucional.
echo.
echo Pressione CTRL+C para encerrar o servidor.
echo.

set "PHP_CMD=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if not exist "%PHP_CMD%" (
    set "PHP_CMD=php"
)

cd /d "%~dp0LivreOS\public_html"
"%PHP_CMD%" -S 127.0.0.1:8000 router.php
pause
