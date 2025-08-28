<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory as ViewLayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Siel\Acumulus\Helpers\Message;
use Siel\Acumulus\Helpers\Severity;
use Siel\AcumulusMa2\Block\Adminhtml\Plugin\Rate;
use Siel\AcumulusMa2\Helper\Data;
use Throwable;

/**
 * Base Acumulus page controller.
 *
 * Note that the Siel\AcumulusMa2\Controller\Adminhtml\Order\Status controller
 * does not derive from this base class, as that handles a tab, not a full page.
 */
abstract class AbstractAcumulusPage extends AbstractAcumulus
{
    protected ViewLayoutFactory $layoutFactory;
    private PageFactory $resultPageFactory;

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
     *
     * @throws \Throwable
     */
    public function execute(): Page
    {
        // The execute method of our controller is the highest level from where
        // our code executes: e.g. all code in Block is executed when this
        // method is active, i.e. is on the execution stack. As the rate plugin
        // is only shown on our own pages, all that code is also only executed
        // while this method is on the execution stack.
        // So this method is a good place for our high-level exception catching.
        try {
            // Notice about rating our plugin.
            if ($this->getFormType() !== 'register') {
                $value = $this->getAcumulusContainer()->getConfig()->getShowRatePluginMessage();
                if (time() >= $value) {
                    $html = $this->layoutFactory
                        ->create()
                        ->createBlock(Rate::class)
                        ->toHtml();
                    $this->messageManager->addComplexNoticeMessage('rate_plugin', ['message' => $html]);
                }
            }

            // Create the form first: this will load the translations.
            $form = $this->getAcumulusForm();
            $form->process();
            // Force the creation of the fields to get connection error messages
            // added to the message manager.
            $form->getFields();
            foreach ($form->getMessages(Severity::RealMessages) as $message) {
                switch ($message->getSeverity()) {
                    case Severity::Success:
                        $this->messageManager->addSuccessMessage($message->format(Message::Format_PlainWithSeverity));
                        break;
                    case Severity::Info:
                    case Severity::Notice:
                        $this->messageManager->addNoticeMessage($message->format(Message::Format_PlainWithSeverity));
                        break;
                    case Severity::Warning:
                        $this->messageManager->addWarningMessage($message->format(Message::Format_PlainWithSeverity));
                        break;
                    case Severity::Error:
                        $this->messageManager->addErrorMessage($message->format(Message::Format_PlainWithSeverity));
                        break;
                    case Severity::Exception:
                        /** @noinspection PhpParamsInspection Will be an \Exception, will not be null. */
                        $this->messageManager->addExceptionMessage($message->getException());
                        break;
                    default:
                        break;
                }
            }
        } catch (Throwable $e) {
            // We handle our "own" exceptions but only when we can process them
            // as we want, i.e. show it as an error at the beginning of the
            // form. That's why we start catching only after we have a form, and
            // stop catching just before postRenderForm().
            try {
                $crashReporter = $this->getAcumulusContainer()->getCrashReporter();
                $message = $crashReporter->logAndMail($e);
                $this->messageManager->addErrorMessage($message);
            }
            catch (Throwable) {
                // We do not know if we have informed the user per mail or
                // screen, so assume not, and rethrow the original exception.
                throw $e;
            }
        }

        // To get the messages on the result page, I had to move these lines
        // below the form handling.
        /** @var \Magento\Backend\Model\View\Result\Page $page */
        $page = $this->resultPageFactory->create();
        if ($this->getFormType() !== 'register') {
            $page->setActiveMenu('Siel_Acumulus::acumulus_' . $this->getFormType());
        }
        $page->getConfig()->getTitle()->prepend($this->t($this->getFormType() . '_form_header'));

        return $page;
    }
}
