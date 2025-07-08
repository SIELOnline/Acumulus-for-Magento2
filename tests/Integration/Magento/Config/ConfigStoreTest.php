<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Magento\Config;

use Siel\Acumulus\Config\Config;
use Siel\Acumulus\Tests\Magento\TestCase;

/**
 * ConfigStoreTest test the Magento ConfigStore.
 *
 * More specifically, the cache cleaning on save does not seem to work automatically.
 */
class ConfigStoreTest extends TestCase
{
    protected function getConfig(): Config
    {
        return self::getContainer()->getConfig();
    }

    public function testSave(): void
    {
        $now = time();
        $config = $this->getConfig();
        $valuesBefore = $config->getAll();
        $valuesBefore['showPluginV8Message'] = $now;
        $config->save($valuesBefore);

        $this->assertSame($now, $config->get('showPluginV8Message'));

        $valuesAfter = $config->getAll();
        $this->assertEquals($valuesBefore, $valuesAfter);
    }
}
