<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento\Integration;

use Siel\Acumulus\Fld;
use Siel\Acumulus\Helpers\Container;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Tests\AcumulusTestUtils;
use Siel\Acumulus\Tests\Magento\Data\TestData;

use Siel\Acumulus\Tests\Magento\TestCase;

use function in_array;

/**
 * InvoiceCreateTest tests the process of creating an {@see Invoice}.
 */
class InvoiceCreateTest extends TestCase
{
    public function InvoiceDataProvider(): array
    {
        return [
            'FR consument' => [Source::Order, 6],
            'FR consument refund' => [Source::CreditNote, 2],
        ];
    }

    /**
     * Tests the Creation process, i.e. collecting and completing an
     * {@see \Siel\Acumulus\Data\Invoice}.
     *
     * @dataProvider InvoiceDataProvider
     * @throws \JsonException
     */
    public function testCreate(string $type, int $id, array $excludeFields = []): void
    {
        $invoiceSource = $this->getAcumulusContainer()->createSource($type, $id);
        $invoiceAddResult = $this->getAcumulusContainer()->createInvoiceAddResult('SendInvoiceTest::testCreateAndCompleteInvoice()');
        $invoice = $this->getAcumulusContainer()->getInvoiceCreate()->create($invoiceSource, $invoiceAddResult);
        $result = $invoice->toArray();
        $testData = new TestData();
        // Get order from Order{id}.json.
        $expected = $testData->get($type, $id);
        if ($expected !== null) {
            // Save order to Order{id}.latest.json, so we can compare differences ourselves.
            $testData->save($type, $id, false, $result);
            $this->assertCount(1, $result);
            $this->assertArrayHasKey(Fld::Customer, $result);
            $this->compareAcumulusObjects($expected[Fld::Customer], $result[Fld::Customer], Fld::Customer, $excludeFields);
        } else {
            // File does not yet exist: first time for a new test order: save order to Order{id}.json.
            // Will raise a warning that no asserts have been executed.
            $testData->save($type, $id, true, $result);
        }
    }
}
