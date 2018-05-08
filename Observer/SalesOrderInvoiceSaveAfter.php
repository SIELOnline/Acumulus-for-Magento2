<?php
namespace Siel\AcumulusMa2\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Siel Acumulus save order observer
 */
class SalesOrderInvoiceSaveAfter implements ObserverInterface
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

    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * Forwards the Magento save order event to the Acumulus Invoice Manager.
     *
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        $invoice = $event->getInvoice();
        $source = $this->helper->getAcumulusContainer()->getSource(Source::Order, $invoice->getOrderId());
        $this->helper->getAcumulusContainer()->getInvoiceManager()->invoiceCreate($source);
    }
}
