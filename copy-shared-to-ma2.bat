@echo off
rem Link Common library to here.
mklink /J D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel\AcumulusMa2\src\Siel D:\Projecten\Acumulus\Webkoppelingen\Library\Siel

rem Link license files to here.
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel\AcumulusMa2\changelog.txt   D:\Projecten\Acumulus\Webkoppelingen\changelog-4.x.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel\AcumulusMa2\license.txt     D:\Projecten\Acumulus\Webkoppelingen\license.txt
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel\AcumulusMa2\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\licentie-nl.pdf
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel\AcumulusMa2\leesmij.txt   D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt
