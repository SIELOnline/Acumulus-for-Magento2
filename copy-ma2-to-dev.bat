@echo off
rem Copy files in our folder structure to development installation.
mklink /J D:\Projecten\Acumulus\Magento\www21\app\code\Siel D:\Projecten\Acumulus\Webkoppelingen\Magento21\Siel
mkdir D:\Projecten\Acumulus\Magento\www21\vendor\siel
mklink /J D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus
