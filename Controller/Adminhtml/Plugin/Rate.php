<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml\Plugin;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory as ViewLayoutFactory;
use Siel\AcumulusMa2\Controller\Adminhtml\AbstractAcumulus;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Acumulus plugin/rate controller.
 */
class Rate extends AbstractAcumulus
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
        // Create the form first: this will load the translations.
        /** @var \Siel\Acumulus\Shop\RatePluginForm $acumulusForm */
        $acumulusForm = $this->getAcumulusForm();
        $acumulusForm->process();
        // Processing the form may result in that the form should be removed.
        $html = $this->layoutFactory->create()->createBlock('Siel\AcumulusMa2\Block\Adminhtml\Plugin\Rate')->toHtml();
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($html);

        return $resultRaw;
    }
}
