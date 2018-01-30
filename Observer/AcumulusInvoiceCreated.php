<?php
namespace Siel\AcumulusMa2\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Siel\Acumulus\Invoice\Creator;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Meta;
use Siel\Acumulus\Tag;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Class AcumulusInvoiceCreated
 */
class AcumulusInvoiceCreated implements ObserverInterface
{

    /**
     * @var \Siel\AcumulusMa2\Helper\Data
     */
    private $helper;

    /**
     * SalesOrderSaveAfter constructor.
     *
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Processes the event triggered before an invoice will be sent to Acumulus.
     *
     * The EventObserver contains the following data properties:
     * array|null invoice
     *   The invoice in Acumulus format as will be sent to Acumulus or null if
     *   another observer already decided that the invoice should not be sent to
     *   Acumulus.
     * \Siel\Acumulus\Invoice\Source invoiceSource
     *   Wrapper around the original Magento order or refund for which the
     *   invoice has been created.
     * \Siel\Acumulus\Invoice\Result localResult
     *   Any local error or warning messages that were created locally.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var array $invoice */
        $invoice = $observer->getDataByKey('invoice');
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $observer->getDataByKey('source');

        $this->supportPaycheckout($invoice, $invoiceSource);

        // Pass changes back to Acumulus.
        $observer->setData('invoice', $invoice);
    }

    /**
     * Adds support for the paycheckout module.
     *
     * The paycheckout module allows to add a payment fee to an order. If it
     * does so, the amount is stored in the columns:
     * - base_paycheckout_surcharge_amount
     * - base_paycheckout_surcharge_tax_amount
     * which the module has added to the sales_order and sales_creditmemo
     * tables.
     *
     * @see https://www.paycheckout.com/magento-payment-provider
     *
     * @param array $invoice
     * @param \Siel\Acumulus\Invoice\Source $invoiceSource
     */
    protected function supportPaycheckout(array &$invoice, Source $invoiceSource)
    {
        if ((float) $invoiceSource->getSource()->getBasePaycheckoutSurchargeAmount() !== 0.0) {
            $sign = $invoiceSource->getType() === Source::CreditNote ? -1 : 1;
            $paymentEx = (float) $sign * $invoiceSource->getSource()->getBasePaycheckoutSurchargeAmount();
            $paymentVat = (float) $sign * $invoiceSource->getSource()->getBasePaycheckoutSurchargeTaxAmount();
            $paymentInc = $paymentEx + $paymentVat;
            $line = [
                        Tag::Product => $this->helper->t('payment_costs'),
                        Tag::Quantity => 1,
                        Tag::UnitPrice => $paymentEx,
                        Meta::UnitPriceInc => $paymentInc,
                    ];
            $line += Creator::getVatRangeTags($paymentVat, $paymentEx);
            $line += [
                        Meta::FieldsCalculated => [Meta::UnitPriceInc],
                        Meta::LineType => Creator::LineType_PaymentFee,
                     ];
            $invoice['customer']['invoice']['line'][] = $line;

            // Add these amounts to the invoice totals.
            // @see \Siel\Acumulus\Magento\Invoice\Creator\getInvoiceTotals()
            $invoice['customer']['invoice'][Meta::InvoiceAmountInc] += $paymentInc;
            $invoice['customer']['invoice'][Meta::InvoiceVatAmount] += $paymentVat;
        }
    }
}
