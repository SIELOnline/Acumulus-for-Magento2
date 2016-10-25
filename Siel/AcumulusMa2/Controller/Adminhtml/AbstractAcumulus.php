<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Base Acumulus config controller.
 */
class AbstractAcumulus extends Action
{
    /** @var string */
    protected $type = '';

    /** @var PageFactory  */
    protected $resultPageFactory;

    /** @var \Siel\AcumulusMa2\Helper\Data */
    protected $helper;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->helper = $this->_objectManager->get('Siel\AcumulusMa2\Helper\Data');
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
    protected function t($key) {
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
            $form = $this->helper->getAcumulusConfig()->getForm($this->type);
            $form->process();
            foreach($form->getSuccessMessages() as $message) {
                $this->messageManager->addSuccess($message);
            }
            foreach($form->getWarningMessages() as $message) {
                $this->messageManager->addWarning($message);
            }
            foreach($form->getErrorMessages() as $message) {
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, $e->getMessage());
        }
        // To get the messages on the result page, I had to move these lines
        // to below of the form handling.
        $page = $this->resultPageFactory->create();
        $this->_setActiveMenu("Siel_Acumulus::acumulus_{$this->type}");
        $page->getConfig()->getTitle()->prepend($this->t("{$this->type}_form_header"));
        return $page;
    }
}
