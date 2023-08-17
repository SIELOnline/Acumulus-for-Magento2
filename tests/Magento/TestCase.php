<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento;

use Magento\TestFramework\Helper\Bootstrap;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Tests\AcumulusTestUtils;
use Siel\AcumulusMa2\Helper\Data;

/**
 * TestCase implements some common methods for testing the Acumulus Magento functionality.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use AcumulusTestUtils;

    private function getHelper(): Data
    {
        return Bootstrap::getObjectManager()->get(Data::class);
    }

    protected function getAcumulusContainer(): Container
    {
        return $this->getHelper()->getAcumulusContainer();
    }
}
