@echo off
rem Copy files in our folder structure to development installation.
mkdir D:\Projecten\Acumulus\Magento\www21\vendor\siel 2> nul
rmdir /s /q D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus 2> nul
mklink /J D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus
rmdir /s /q D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus-ma2 2> nul
mklink /J D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus-ma2 D:\Projecten\Acumulus\Webkoppelingen\Magento2\siel-acumulus-ma2
mkdir D:\Projecten\Acumulus\Magento\www21\app\code 2> nul
rmdir /s /q D:\Projecten\Acumulus\Magento\www21\app\code\Siel 2> nul
mklink /J D:\Projecten\Acumulus\Magento\www21\app\code\Siel D:\Projecten\Acumulus\Webkoppelingen\Magento2\Siel
