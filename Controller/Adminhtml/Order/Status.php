<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory as ViewLayoutFactory;
use Siel\Acumulus\Helpers\Severity;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Controller\Adminhtml\AbstractAcumulus;
use Siel\AcumulusMa2\Helper\Data;
use Throwable;

/**
 * Acumulus order/status controller.
 */
class Status extends AbstractAcumulus
{
    protected ViewLayoutFactory $layoutFactory;
    protected RawFactory $resultRawFactory;

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
     *
     * @throws \Throwable
     */
    public function execute(): Raw
    {
        // See the documentation in the
        // {@see \Siel\AcumulusMa2\Controller\Adminhtml\AbstractAcumulusPage::execute}
        // method. This method is an override and thus should copy the exception
        // handling from that method.

        /** @var \Siel\AcumulusMa2\Block\Adminhtml\Order\Status $block */
        $block = $this->layoutFactory
            ->create()
            ->createBlock(\Siel\AcumulusMa2\Block\Adminhtml\Order\Status::class);
        /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $acumulusForm */
        $acumulusForm = $this->getAcumulusForm();

        if ($this->getAcumulusContainer()->getConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
            try {
                // Create the form first: this will load the translations.
                $id = $this->getRequest()->getParam('order_id');
                $source = $this->getAcumulusContainer()->createSource(Source::Order, $id);
                $acumulusForm->setSource($source);
                $acumulusForm->process();
            } catch (Throwable $e) {
                // We handle our "own" exceptions but only when we can process
                // them as we want, i.e. show it as an error at the beginning of
                // the form. That's why we start catching only after we have a
                // form, and stop catching just before $block->toHtml().
                try {
                    $crashReporter = $this->getAcumulusContainer()->getCrashReporter();
                    $message = $crashReporter->logAndMail($e);
                    $acumulusForm->createAndAddMessage($message, Severity::Exception);
                } catch (Throwable $inner) {
                    // We do not know if we have informed the user per mail or
                    // screen, so assume not, and rethrow the original exception.
                    throw $e;
                }
            }
            $html = $block->toHtml();
        } else {
            $html = '<div>Not enabled</div>';
        }

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($html);
        return $resultRaw;
    }
}
