@echo off
rem Check usage and arguments.
if dummy==dummy%1 (
echo Usage: %~n0 version
exit /B 1;
)
set version=%1

del Magento-2.1.x-Acumulus-%version%.zip 2> nul

rem zip package.
"C:\Program Files\7-Zip\7z.exe" a -tzip Magento-2.1.x-Acumulus-%version%.zip Siel | findstr /i "Failed Error"
"C:\Program Files\7-Zip\7z.exe" t Magento-2.1.x-Acumulus-%version%.zip | findstr /i "Processing Everything Failed Error"
