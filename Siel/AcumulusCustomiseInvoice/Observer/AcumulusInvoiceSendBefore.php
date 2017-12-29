<?php
namespace Siel\AcumulusCustomiseInvoice\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Siel\Acumulus\Api;
use Siel\Acumulus\Meta;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Siel Acumulus save order observer
 */
class AcumulusInvoiceSendBefore implements ObserverInterface
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
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var array $invoice */
        $invoice = $observer->getDataByKey('invoice');
        /** @var \Siel\Acumulus\Invoice\Source $invoiceSource */
        $invoiceSource = $observer->getDataByKey('source');
        /** @var \Siel\Acumulus\Invoice\Result $localResult */
        $localResult = $observer->getDataByKey('localResult');

        // Here you can make changes to the raw invoice based on your specific
        // situation, e.g. setting or correcting tax rates before the completor
        // strategies execute.

        // NOTE: the example below is now an option in the advanced settings:
        // Prevent sending 0-amount invoices (free products).
        if (empty($invoice) || $invoice['customer']['invoice'][Meta::InvoiceAmountInc] == 0) {
            $invoice = null;
        } else {
            // Here you can make changes to the invoice based on your specific
            // situation, e.g. setting the payment status to its correct value:
            $invoice['customer']['invoice']['testpaymentstatus'] = Api::PaymentStatus_Due;
        }

        // Pass changes back to Acumulus.
        $observer->setData('invoice', $invoice);
    }
}
