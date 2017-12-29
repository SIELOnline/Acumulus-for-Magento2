<?php
namespace Siel\AcumulusCustomiseInvoice\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Siel Acumulus save order observer
 */
class AcumulusInvoiceSendAfter implements ObserverInterface
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
     * \Siel\Acumulus\Invoice\Result result
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
        /** @var \Siel\Acumulus\Invoice\Result $result */
        $result = $observer->getDataByKey('result');

        if ($result->getException())
        {
            // Serious error:
            if ($result->isSent())
            {
                // During sending.
            }
            else
            {
                // Before sending.
            }
        }
        elseif ($result->hasError())
        {
            // Invoice was sent to Acumulus but not created due to errors in the
            // invoice.
        }
        else
        {
            // Sent successfully, invoice has been created in Acumulus:
            if ($result->getWarnings())
            {
                // With warnings.
            }
            else
            {
                // Without warnings.
            }

            // Check if an entry id was created.
            $acumulusInvoice = $result->getResponse();
            if ( !empty($acumulusInvoice['entryid']))
            {
                $token = $acumulusInvoice['token'];
                $entryId = $acumulusInvoice['entryid'];
            }
            else
            {
                // If the invoice was sent as a concept, no entryid will be returned.
            }
        }
    }
}
