<?php
use \Magento\Framework\Component\ComponentRegistrar;

// Using the function dirname() instead of __DIR__ prevents errors about invalid
// template files when symbolic links are used.
ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Siel_AcumulusMa2', isset($file) ? dirname($file) : __DIR__);
