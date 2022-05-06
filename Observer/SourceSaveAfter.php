<?php
/**
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

namespace Siel\AcumulusMa2\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Helper\Data;
use Throwable;

/**
 * Siel Acumulus source (order, credit-memo, invoice) save after  observer.
 */
class SourceSaveAfter implements ObserverInterface
{
    private Data $helper;

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
     * Forwards the Magento save order/credit-memo/invoice event to the Acumulus
     * Invoice Manager.
     *
     * @param EventObserver $observer
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        try {
            switch ($event->getName()) {
                case 'sales_order_save_after':
                    $invoiceSourceType = Source::Order;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $invoiceSourceOrId = $event->getOrder();
                    break;
                case 'sales_order_invoice_save_after':
                    $invoiceSourceType = Source::Order;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $invoice = $event->getInvoice();
                    $invoiceSourceOrId = $invoice->getOrderId();
                    break;
                case 'sales_order_creditmemo_save_after':
                    $invoiceSourceType = Source::CreditNote;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $invoiceSourceOrId = $event->getCreditmemo();
                    break;
                default:
                    assert(false, __METHOD__ . ': we do not handle event ' . $event->getName());
            }
            $source = $this->helper->getAcumulusContainer()->createSource($invoiceSourceType, $invoiceSourceOrId);
            $this->helper->getAcumulusContainer()->getInvoiceManager()->sourceStatusChange($source);
        } catch (Throwable $e) {
            try {
                $crashReporter = $this->helper->getAcumulusContainer()->getCrashReporter();
                // We do not know if we are on the admin side, so we should not
                // try to display the message returned by logAndMail().
                $crashReporter->logAndMail($e);
            } catch (Throwable $inner) {
                // We do not know if we have informed the user per mail or
                // screen, so assume not, and rethrow the original exception.
                throw $e;
            }
        }
    }
}
