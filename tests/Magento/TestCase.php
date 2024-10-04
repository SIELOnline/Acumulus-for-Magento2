<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Siel\Acumulus\Helpers\Container;
use Siel\AcumulusMa2\Helper\Data;

/**
 * TestCase implements some common methods for testing the Acumulus Magento functionality.
 */
class TestCase extends PHPUnitTestCase
{
    use AcumulusTestUtils;
}
