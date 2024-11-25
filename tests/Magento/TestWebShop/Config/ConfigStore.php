<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\tests\Magento\TestWebShop\Config;

/**
 * ConfigStore changes the configKey so that test config data will not overwrite real data.
 */
class ConfigStore extends \Siel\Acumulus\Magento\Config\ConfigStore
{
    public function __construct()
    {
        $this->configKey = 'acumulus-test';
    }
}
