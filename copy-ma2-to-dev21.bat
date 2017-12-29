@echo off
rem Copy files in our folder structure to development installation.
mkdir D:\Projecten\Acumulus\Magento\www21\vendor\siel
mklink /J D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus
mklink /J D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus-ma2 D:\Projecten\Acumulus\Webkoppelingen\Magento21\siel-acumulus-ma2
mklink /J D:\Projecten\Acumulus\Magento\www21\app\code\Siel D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel
