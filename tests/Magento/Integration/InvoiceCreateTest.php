<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento\Integration;

use Siel\Acumulus\Fld;
use Siel\Acumulus\Invoice\Source;

use Siel\Acumulus\Tests\Magento\TestCase;

use function dirname;

/**
 * InvoiceCreateTest tests the process of creating an {@see Invoice}.
 */
class InvoiceCreateTest extends TestCase
{
    public function InvoiceDataProvider(): array
    {
        $dataPath = dirname(__FILE__, 2) . '/Data';
        return [
            'FR consument' => [$dataPath, Source::Order, 6],
            'FR consument refund' => [$dataPath, Source::CreditNote, 2],
            'FR bedrijf' => [$dataPath, Source::Order, 8],
            'NL consument, winkelwagenkorting' => [$dataPath, Source::Order, 9],
            'NL consument refund, winkelwagenkorting' => [$dataPath, Source::CreditNote, 4],
            'FR consument, NL levering, % cataloguskorting en € winkelwagenkorting' => [$dataPath, Source::Order, 10],
            'refund FR consument, NL levering, % cataloguskorting en € winkelwagenkorting' => [$dataPath, Source::CreditNote, 7],
        ];
    }

    /**
     * Tests the Creation process, i.e. collecting and completing an
     * {@see \Siel\Acumulus\Data\Invoice}.
     *
     * @dataProvider InvoiceDataProvider
     * @throws \JsonException
     */
    public function testCreate(string $dataPath, string $type, int $id, array $excludeFields = []): void
    {
        $invoiceSource = self::getAcumulusContainer()->createSource($type, $id);
        $invoiceAddResult = self::getAcumulusContainer()->createInvoiceAddResult('SendInvoiceTest::testCreateAndCompleteInvoice()');
        $invoice = self::getAcumulusContainer()->getInvoiceCreate()->create($invoiceSource, $invoiceAddResult);
        $result = $invoice->toArray();
        // Get order from Order{id}.json.
        $expected = $this->getTestSource($dataPath, $type, $id);
        if ($expected !== null) {
            // Save order to Order{id}.latest.json, so we can compare differences ourselves.
            $this->saveTestSource($dataPath, $type, $id, false, $result);
            $this->assertCount(1, $result);
            $this->assertArrayHasKey(Fld::Customer, $result);
            $this->compareAcumulusObjects($expected[Fld::Customer], $result[Fld::Customer], Fld::Customer, $excludeFields);
        } else {
            // File does not yet exist: first time for a new test order: save order to Order{id}.json.
            // Will raise a warning that no asserts have been executed.
            $this->saveTestSource($dataPath, $type, $id, true, $result);
        }
    }
}
