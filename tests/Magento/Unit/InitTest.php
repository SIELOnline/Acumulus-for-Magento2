<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento\Unit;

use Magento\TestFramework\TestCase\AbstractController;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Tests that WooCommerce and Acumulus have been initialized.
 */
class InitTest extends AbstractController
{
    private function getHelper(): Data
    {
        return $this->_objectManager->get(Data::class);
    }

    protected function getContainer(): Container
    {
        return $this->getHelper()->getAcumulusContainer();
    }

    /**
     * A single test to see if the test framework (including the plugins) has been
     * initialized correctly:
     * 1 We have access to the Container.
     * 2 Magento has been initialized.
     * 3 We can retrieve an Order
     */
    public function testInit(): void
    {
        // 1.
        $container = $this->getContainer();
        // 2.
        $environmentInfo = $container->getEnvironment()->get();
        $this->assertMatchesRegularExpression('|\d+\.\d+\.\d+|', $environmentInfo['shopVersion']);

        /** @var \Siel\Acumulus\Magento\Invoice\Source $source */
        $source = $container->createSource(Source::Order, 6);
        $this->assertSame('Erwin', $source->getSource()->getCustomerFirstname());
    }
}
