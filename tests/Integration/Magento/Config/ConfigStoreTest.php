<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Magento\Config;

use Siel\Acumulus\Config\Config;
use Siel\Acumulus\Config\ConfigStore;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Tests\Magento\TestCase;

class ConfigStoreTest extends TestCase
{
    protected static function getAcumulusContainer(): Container
    {
        return new Container('Magento\TestWebShop');
    }

    protected function getConfig(): Config
    {
        return $this->getAcumulusContainer()->getConfig();
    }

    public function testSave()
    {
        $config = $this->getConfig();
        $valuesBefore = $config->getAll();
        $valuesBefore['showPluginV8Message'] = 200;
        $config->save($valuesBefore);

        $this->AssertSame(200, $config->get('showPluginV8Message'));
        $valuesAfter = $config->getAll();
        $valuesBefore['showPluginV8Message'] = 200;
        $this->AssertSame($valuesBefore, $valuesAfter);
    }

}
