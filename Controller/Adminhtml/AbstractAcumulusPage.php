<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory as ViewLayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Base Acumulus page controller.
 *
 * Note that the Siel\AcumulusMa2\Controller\Adminhtml\Order\Status controller
 * does not derive from this base class, as that handles a tab, not a full page.
 */
abstract class AbstractAcumulusPage extends AbstractAcumulus
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /** @var PageFactory  */
    private $resultPageFactory;

    /**
     * AbstractAcumulusPage constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        ViewLayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $helper);
    }

    /**
     * Load the page defined in view/adminhtml/layout/acumulus_config_index.xml.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            // Notice about rating our plugin.
            $value = $this->getAcumulusContainer()->getConfig()->getShowRatePluginMessage();
            $time = time();
            if ($time >= $value) {
                $html = $this->layoutFactory->create()->createBlock('Siel\AcumulusMa2\Block\Adminhtml\Plugin\Rate')->toHtml();
                $this->messageManager->addComplexNoticeMessage('rate_plugin', ['message' => $html]);
            }

            // Create the form first: this will load the translations.
            $form = $this->getAcumulusForm();
            $form->process();
            // Force the creation of the fields to get connection error messages
            // added to the message manager.
            $form->getFields();
            foreach ($form->getSuccessMessages() as $message) {
                $this->messageManager->addSuccessMessage($message);
            }
            foreach ($form->getWarningMessages() as $message) {
                $this->messageManager->addWarningMessage($message);
            }
            foreach ($form->getErrorMessages() as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        // To get the messages on the result page, I had to move these lines
        // below the form handling.
        $page = $this->resultPageFactory->create();
        $this->_setActiveMenu('Siel_Acumulus::acumulus_' . $this->getFormType());
        $page->getConfig()->getTitle()->prepend($this->t($this->getFormType() . '_form_header'));

        return $page;
    }
}
