@echo off

::Specify the PHP binaries path here
set PHP_PATH=bin\php
::Specify the main PHP executable here
set PHP_EXE=php.exe

if exist "%PHP_PATH%\%PHP_EXE%" (
    "%PHP_PATH%\%PHP_EXE%" phartools.php %*
) else (
    echo PHP Executable not found. Please set the right PHP path.
)
