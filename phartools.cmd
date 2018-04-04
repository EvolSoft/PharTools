@echo off

::Specify the PHP executable path
set PHP_PATH=%~dp0\bin\php\php.exe

if exist "%PHP_PATH%" (
    "%PHP_PATH%" "%~dp0\phartools.php" %*
) else (
    echo PHP Executable not found. Please set the right PHP path.
)