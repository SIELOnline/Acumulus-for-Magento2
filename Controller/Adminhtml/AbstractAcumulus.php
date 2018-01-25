<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Base Acumulus config controller.
 */
abstract class AbstractAcumulus extends Action
{
    /** @var string */
    private $type = '';

    /** @var PageFactory  */
    private $resultPageFactory;

    /** @var \Siel\AcumulusMa2\Helper\Data */
    private $helper;

    public function __construct(Context $context, PageFactory $resultPageFactory, Data $helper, $type)
    {
        $this->type = $type;
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Helper method to translate strings.
     *
     * @param string $key
     *  The key to get a translation for.
     *
     * @return string
     *   The translation for the given key or the key itself if no translation
     *   could be found.
     */
    protected function t($key)
    {
        return $this->helper->t($key);
    }

    /**
     * Checks if a user has permissions to visit the related pages.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $resource = $this->type === 'batch' ? 'batch' : 'config';
        return $this->_authorization->isAllowed("Siel_Acumulus::$resource");
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
            $form = $this->helper->getAcumulusContainer()->getForm($this->type);
            $form->process();
            foreach ($form->getSuccessMessages() as $message) {
                $this->messageManager->addSuccessMessage($message);
            }
            foreach ($form->getWarningMessages() as $message) {
                $this->messageManager->addWarningMessage($message);
            }
            foreach ($form->getErrorMessages() as $message) {
                $this->messageManager->addErrorMessage($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
        // To get the messages on the result page, I had to move these lines
        // to below of the form handling.
        $page = $this->resultPageFactory->create();
        $this->_setActiveMenu("Siel_Acumulus::acumulus_{$this->type}");
        $page->getConfig()->getTitle()->prepend($this->t("{$this->type}_form_header"));
        return $page;
    }
}
