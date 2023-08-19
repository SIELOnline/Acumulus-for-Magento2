<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Magento\Unit\Invoice;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Siel\Acumulus\Api;
use Siel\Acumulus\Magento\Invoice\Source;
use Siel\Acumulus\Tests\Magento\TestCase;

/**
 * SourceTest tests the Magento Source class.
 */
class SourceTest extends TestCase
{
    public function getSourceBasicDataProvider(): array
    {
        return [
            [Source::Order, 6],
            [Source::CreditNote, 3],
        ];
    }

    /**
     * Tests basic methods from Source (type and id related).
     *
     * @dataProvider getSourceBasicDataProvider
     */
    public function testSourceBasic(
        string $sourceType,
        int $sourceId
    ): void
    {
        $source = self::getAcumulusContainer()->createSource($sourceType, $sourceId);
        $label = self::getAcumulusContainer()->getTranslator()->get($sourceType);
        $this->assertSame($sourceType, $source->getType());
        $this->assertSame(strtolower($label), $source->getTypeLabel(MB_CASE_LOWER));
        $this->assertSame(strtoupper($label), $source->getTypeLabel(MB_CASE_UPPER));
        $this->assertSame(ucfirst($label), $source->getTypeLabel(MB_CASE_TITLE));
        $this->assertSame($sourceId, $source->getId());
        if ($sourceType === Source::Order) {
            $this->assertInstanceOf(Order::class, $source->getSource());
            $this->assertSame($source, $source->isOrder());
            $this->assertSame($source, $source->getOrder());
            $this->assertNull($source->isCreditNote());
        } else {
            $this->assertInstanceOf(Creditmemo::class, $source->getSource());
            $this->assertSame($source, $source->isCreditNote());
            $this->assertSame(Source::Order, $source->getOrder()->getType());
            $this->assertNull($source->isOrder());
        }
    }

    public function getSourceGettersDataProvider(): array
    {
        return [
            [Source::Order, 6, '000000005', 1, '2022-12-01', 'complete', 'banktransfer', Api::PaymentStatus_Paid, null, 'FR'],
            [Source::CreditNote, 3, 'CM000000003', -1, '2022-12-01', Creditmemo::STATE_REFUNDED, 'banktransfer', Api::PaymentStatus_Due, '2022-12-01', 'FR'],
        ];
    }

    /**
     * Tests getter methods from Source.
     *
     * @dataProvider getSourceGettersDataProvider
     *
     * @param string|int $status
     */
    public function testSourceGetters(
        string $sourceType,
        int $sourceId,
        ?string $reference,
        int $sign,
        ?string $date,
        $status,
        ?string $paymentMethod,
        ?int $paymentStatus,
        ?string $paymentDate,
        string $countryCode
    ): void
    {
        $source = self::getAcumulusContainer()->createSource($sourceType, $sourceId);
        $this->assertSame($reference, $source->getInvoiceReference());
        $this->assertEquals($sign, $source->getSign());
        $this->assertSame($date, $source->getDate());
        $this->assertSame($status, $source->getStatus());
        $this->assertSame($paymentMethod, $source->getPaymentMethod());
        $this->assertSame($paymentStatus, $source->getPaymentStatus());
        $this->assertSame($paymentDate, $source->getPaymentDate());
        $this->assertSame($countryCode, $source->getCountryCode());
    }

    public function getCreditNoteDataProvider(): array
    {
        return [
          [Source::Order, 4, 1, 3],
          [Source::Order, 6, 0, null],
          [Source::CreditNote, 3, 1, 3],
        ];
    }

    /**
     * Tests getter methods from Source related to Credit notes.
     *
     * @dataProvider getCreditNoteDataProvider
     */
    public function testSourceCreditNote(string $sourceType, int $sourceId, int $count, ?int $id): void
    {
        $source = self::getAcumulusContainer()->createSource($sourceType, $sourceId);
        $creditNotes = $source->getCreditNotes();
        if ($sourceType === Source::CreditNote) {
            $this->assertSame($source->getType(), $creditNotes[0]->getType());
            $this->assertSame($source->getId(), $creditNotes[0]->getId());
        }
        $this->assertCount($count, $creditNotes);
        if ($count === 0) {
            $this->assertNull($source->getCreditNote());
        } else {
            $this->assertSame($id, $creditNotes[0]->getId());
            $this->assertSame($id, (int) $source->getCreditNote()->getId());
            $this->assertSame($id, (int) $source->getCreditNote()->getId(0));
        }
        $this->assertNull($source->getCreditNote($count));
    }

    public function getInvoiceDataProvider(): array
    {
        return [
          [Source::Order, 6, 5, '000000005', '2023-02-07'],
          [Source::Order, 5, null, null, null],
          [Source::CreditNote, 3, 3, 'CM000000003', '2022-12-01'],
        ];
    }

    /**
     * Tests getter methods from Source related to (attached) invoices.
     *
     * @dataProvider getInvoiceDataProvider
     */
    public function testSourceInvoice(string $sourceType, int $sourceId, ?int $id, ?string $reference, ?string $date): void
    {
        $source = self::getAcumulusContainer()->createSource($sourceType, $sourceId);
        $this->assertSame($id, $source->getInvoiceId());
        $this->assertSame($reference, $source->getInvoiceReference());
        $this->assertSame($date, $source->getInvoiceDate());
    }
}
