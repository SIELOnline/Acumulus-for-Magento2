<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento;

use Magento\TestFramework\Helper\Bootstrap;
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

    protected static string $shopNamespace = 'Magento\TestWebShop';
    protected static string $language = 'nl';

    protected static function getTestsPath(): string
    {
        return dirname(__FILE__, 2);
    }

    protected static function getHelper(): ?Data
    {
        return Bootstrap::getObjectManager()?->get(Data::class);
    }

    public function copyLatestTestSources(): void
    {
        $this->parentCopyLatestTestSources();
    }
}
