<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento\Integration;

use Siel\Acumulus\Fld;
use Siel\Acumulus\Invoice\Source;
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
            'NL consument' => [Source::Order, 6],
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
        $invoiceSource = self::getAcumulusContainer()->createSource($type, $id);
        $invoiceAddResult = self::getAcumulusContainer()->createInvoiceAddResult('SendInvoiceTest::testCreateAndCompleteInvoice()');
        $invoice = self::getAcumulusContainer()->getInvoiceCreate()->create($invoiceSource, $invoiceAddResult);
        $result = $invoice->toArray();
        $testData = new TestData();
        // Get order from Order{id}.json.
        $expected = $testData->get($type, $id);
        if ($expected !== null) {
            // Save order to Order{id}.latest.json, so we can compare differences ourselves.
            $testData->save($type, $id, false, $result);
            $this->assertCount(1, $result);
            $this->assertArrayHasKey(Fld::Customer, $result);
            $this->compareAcumulusObject($expected[Fld::Customer], $result[Fld::Customer], Fld::Customer, $excludeFields);
        } else {
            // File does not yet exist: first time for a new test order: save order to Order{id}.json.
            // Will raise a warning that no asserts have been executed.
            $testData->save($type, $id, true, $result);
        }
    }

    private function compareAcumulusObject(array $expected, array $object, string $objectName, array $excludeFields): void
    {
        foreach ($expected as $field => $value) {
            if (!in_array($field, $excludeFields, true)) {
                $this->assertArrayHasKey($field, $object);
                switch ($field) {
                    case 'invoice':
                    case 'emailAsPdf':
                        $this->compareAcumulusObject($value, $object[$field], $field, $excludeFields);
                        break;
                    case 'lines':
                        foreach ($value as $index => $line) {
                            $this->compareAcumulusObject($line, $object[$field][$index], $field, $excludeFields);
                        }
                        break;
                    default:
                        $this->assertEquals($value, $object[$field], "$objectName::$field");
                        break;
                }
            }
        }
    }
}
