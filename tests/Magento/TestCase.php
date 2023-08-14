<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento;

use Magento\TestFramework\Helper\Bootstrap;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Invoice\Translations;
use Siel\AcumulusMa2\Helper\Data;

/**
 * TestCase implements some common methods for testing the Acumulus Magento functionality.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    private static function getHelper(): Data
    {
        return Bootstrap::getObjectManager()->get(Data::class);
    }

    protected static function getAcumulusContainer(): Container
    {
        return self::getHelper()->getAcumulusContainer();
    }

    /**
     * @beforeClass
     *   Adds translations that are not added by default when the Translator is created.
     */
    public static function addTranslations(): void
    {
        self::getAcumulusContainer()->getTranslator()->add(new Translations());
    }
}
