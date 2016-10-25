<?php
/**
 * @author Buro RaDer
 * @copyright Copyright (c) 2016 Buro RaDer (http://www.burorader.com/)
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Siel_AcumulusMa2', __DIR__);

// See http://magento.stackexchange.com/a/114966/38943.
$vendorDir = require BP . '/app/etc/vendor_path.php';
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";
/** @var \Composer\Autoload\ClassLoader $composerAutoloader */
$composerAutoloader = include $vendorAutoload;
$composerAutoloader->addPsr4('Siel\\Acumulus\\', array(__DIR__ . '/src/Siel/Acumulus'));
