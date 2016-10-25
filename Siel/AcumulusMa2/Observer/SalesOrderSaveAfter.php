<?php
namespace Siel\AcumulusMa2\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Siel Acumulus save order observer
 */
class SalesOrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Siel\AcumulusMa2\Helper\Data
     */
    protected $helper;

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
     * Forwards the Magento save order event to the Acumulus Invoice Manager.
     *
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Sales\Model\Order $order */
        /** @noinspection PhpUndefinedMethodInspection */
        $order = $event->getOrder();
        $source = $this->helper->getAcumulusConfig()->getSource(Source::Order, $order);
        $this->helper->getAcumulusConfig()->getManager()->sourceStatusChange($source);
    }
}
