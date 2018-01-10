@echo off
rem Link license files to here.
del D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2\license.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2\license.txt D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\license.txt
del D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2\licentie-nl.pdf 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\licentie-nl.pdf
del D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2\leesmij.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2\leesmij.txt D:\Projecten\Acumulus\Webkoppelingen\leesmij.txt

rem Link license files to example module.
del D:\Projecten\Acumulus\Webkoppelingen\Magento2\Siel\AcumulusCustomiseInvoice\license.txt 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento2\Siel\AcumulusCustomiseInvoice\license.txt D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\license.txt
del D:\Projecten\Acumulus\Webkoppelingen\Magento2\Siel\AcumulusCustomiseInvoice\licentie-nl.pdf 2> nul
mklink /H D:\Projecten\Acumulus\Webkoppelingen\Magento2\Siel\AcumulusCustomiseInvoice\licentie-nl.pdf D:\Projecten\Acumulus\Webkoppelingen\libAcumulus\licentie-nl.pdf
