<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

/**
 * Base Acumulus config controller.
 */
abstract class AbstractAcumulus extends Action
{
    use HelperTrait;

    /** @var PageFactory  */
    private $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory, Data $helper)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->setFormType();
        parent::__construct($context);
    }

    /**
     * Checks if a user has permissions to visit the related pages.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $resource = [
            'config' => 'config',
            'advanced' => 'config',
            'batch' => 'batch',
            'invoice' => 'invoice',
        ];
        return $this->_authorization->isAllowed('Siel_Acumulus::' . $resource[$this->getFormType()]);
    }

    /**
     * Load the page defined in view/adminhtml/layout/acumulus_config_index.xml.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            // Create the form first: this will load the translations.
            $form = $this->getAcumulusContainer()->getForm($this->getFormType() === 'invoice' ? 'batch' : $this->getFormType());
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
        $this->_setActiveMenu('Siel_Acumulus::acumulus_' . $this->getFormType());
        $page = $this->resultPageFactory->create();
        $page->getConfig()->getTitle()->prepend($this->t($this->getFormType() . '_form_header'));

        return $page;
    }
}
