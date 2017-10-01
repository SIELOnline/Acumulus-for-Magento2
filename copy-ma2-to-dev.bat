@echo off
rem Copy files in our folder structure to development installation.
mkdir D:\Projecten\Acumulus\Magento\www21\app\code\siel
mklink /J D:\Projecten\Acumulus\Magento\www21\app\code\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\Magento21\siel_acumulus
mkdir D:\Projecten\Acumulus\Magento\www21\vendor\siel
mklink /J D:\Projecten\Acumulus\Magento\www21\vendor\siel\acumulus D:\Projecten\Acumulus\Webkoppelingen\libAcumulus
