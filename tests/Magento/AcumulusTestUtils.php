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

    /**
     * Returns the file path with the server path replaced by the path as it is
     * linked/mounted in this test setup.
     */
    protected static function getEmlFileLink(string $emlFile): string
    {
        return str_replace('C:\ProgramData\Changemaker Studios\Papercut SMTP\Incoming\\', '/home/erwin/Papercut-SMTP/', $emlFile);
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
