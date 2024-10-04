<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento;

use Magento\TestFramework\Helper\Bootstrap;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Tests\AcumulusTestUtils as BaseAcumulusTestUtils;

use Siel\AcumulusMa2\Helper\Data;

use function dirname;

/**
 * AcumulusTestUtils contains Magento specific test functionalities.
 */
trait AcumulusTestUtils
{
    use BaseAcumulusTestUtils {
        copyLatestTestSources as protected parentCopyLatestTestSources;
    }

    protected static function getAcumulusHelper(): ?Data
    {
        return Bootstrap::getObjectManager()?->get(Data::class);
    }

    protected static function getAcumulusContainer(): Container
    {
        return self::getAcumulusHelper()?->getAcumulusContainer() ?? new Container('Magento', 'nl');
    }

    protected function getTestsPath(): string
    {
        return dirname(__FILE__, 2);
    }

    public function copyLatestTestSources(): void
    {
        $this->parentCopyLatestTestSources();
    }
}
