<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpStaticAsDynamicMethodCallInspection
 */

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Magento;

use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Tests\Magento\TestCase;

/**
 * InvoiceCreateTest tests the process of creating an {@see Invoice}.
 */
class InvoiceCreateTest extends TestCase
{
    /**
     * @todo: define a test refund with a manual line.
     * @todo: can we define tests for the supported modules (see observer AcumulusInvoiceCollectAfter)
     * @todo: add a margin scheme invoice.
     */
    public function InvoiceDataProvider(): array
    {
        $dataPath = __DIR__ . '/Data';
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
        $this->_testCreate($dataPath, $type, $id, $excludeFields);
    }
}
