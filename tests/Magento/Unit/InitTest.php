<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento\Unit;

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Tests\Magento\TestCase;

/**
 * Tests that Magento and Acumulus have been initialized.
 */
class InitTest extends TestCase
{
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
        $container = self::getAcumulusContainer();
        // 2.
        $environmentInfo = $container->getEnvironment()->get();
        $this->assertMatchesRegularExpression('|\d+\.\d+\.\d+|', $environmentInfo['shopVersion']);

        /** @var \Siel\Acumulus\Magento\Invoice\Source $source */
        $source = $container->createSource(Source::Order, 6);
        $this->assertSame('Erwin', $source->getSource()->getCustomerFirstname());
    }
}
