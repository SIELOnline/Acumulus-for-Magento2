<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory as ViewLayoutFactory;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Controller\Adminhtml\AbstractAcumulus;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Acumulus order/status controller.
 */
class Status extends AbstractAcumulus
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     */
    public function __construct(
        Action\Context $context,
        RawFactory $resultRawFactory,
        ViewLayoutFactory $layoutFactory,
        Data $helper
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context, $helper);
    }

    /**
     * Generate order status overview for ajax request.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        if ($this->getAcumulusContainer()->getConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
            // Create the form first: this will load the translations.
            /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $acumulusForm */
            $acumulusForm = $this->getAcumulusForm();
            $id = $this->getRequest()->getParam('order_id');
            $source = $this->getAcumulusContainer()->getSource(Source::Order, $id);
            $acumulusForm->setSource($source);
            $acumulusForm->process();
            $html = $this->layoutFactory->create()->createBlock('Siel\AcumulusMa2\Block\Adminhtml\Order\Status')->toHtml();
        } else {
            $html = '<div>Not enabled</div>';
        }

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($html);
        return $resultRaw;
    }
}
