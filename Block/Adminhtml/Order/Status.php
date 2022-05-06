<?php

namespace Siel\AcumulusMa2\Block\Adminhtml\Order;

use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Siel\Acumulus\Helpers\Form;
use Siel\Acumulus\Helpers\Message;
use Siel\Acumulus\Helpers\Severity;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

class Status extends AbstractBlock implements TabInterface
{
    use HelperTrait;

    protected FormFactory $formFactory;
    protected Validator $_formKeyValidator;
    protected bool $hasAuthorization;

    protected \Magento\Framework\Data\Form $form;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->hasAuthorization = $context->getAuthorization()->isAllowed('Siel_Acumulus::batch');
        $this->helper = $helper;
        $this->setFormType();
        // Create the form first: this will load the translations.
        $this->acumulusForm = $this->getAcumulusForm();

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel(): string
    {
        return $this->t('invoice_form_title');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle(): string
    {
        return $this->t('invoice_form_header');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab(): bool
    {
        return $this->hasAuthorization
               && $this->getAcumulusContainer()->getConfig()->getInvoiceStatusSettings()['showInvoiceStatus'];
    }

    /**
     * @inheritdoc
     */
    public function isHidden(): bool
    {
        return !$this->canShowTab();
    }

    /**
     * Retrieve order
     */
    public function setSource()
    {
        $id = $this->getRequest()->getParam('order_id');
        if (!empty($id)) {
            $source = $this->getAcumulusContainer()->createSource(Source::Order, $id);
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->getAcumulusForm()->setSource($source);
        }
    }

    /**
     * Prepares the Magento form based on interaction with the Acumulus form.
     *
     * This functionality is extracted from _toHtml() to place it within the
     * "exception catching at the highest level".
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareForm(): Status
    {
        $this->form = $this->formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        if ($this->getAcumulusContainer()->getConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
            // Create the form first: this will load the translations.
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if (!$this->acumulusForm->hasSource()) {
                $this->setSource();
            }
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if ($this->acumulusForm->hasSource()) {
                // Populate the form using the FormMapper.
                /** @var \siel\Acumulus\Magento\Helpers\FormMapper $mapper */
                $mapper = $this->getAcumulusContainer()->getFormMapper();
                $mapper->setMagentoForm($this->form)->map($this->acumulusForm);
                /** @noinspection PhpUndefinedMethodInspection */
                $this->form->setUseContainer(false);
                $this->form->addValues($this->acumulusForm->getFormValues());
            }
        }
        return $this;
    }

    protected function _toHtml(): string
    {
        $output = '';
        if ($this->getAcumulusContainer()->getConfig()->getInvoiceStatusSettings()['showInvoiceStatus']) {
            // Create the form first: this will load the translations.
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            if ($this->acumulusForm->hasSource()) {
                $url = $this->getUrl('acumulus/order/status', ['_current' => true]);
                $wait = $this->t('wait');
                $output .= '<div id="acumulus-invoice" class="acumulus-area" data-acumulus-wait="'
                           . $wait . '" data-acumulus-url="' . $url . '">';
                $output .= $this->showNotices($this->acumulusForm);
                $output .= '<div class="admin__page-section-title"><span class="title">'
                           . $this->getTabTitle()
                           . '</span></div>';
                $output .= $this->form->getHtml();
                $output .= '</div>';
                $output .= '';
            } else {
                $output .= '<div>Unknown source</div>';
            }
        } else {
            $output .= '<div>Not enabled</div>';
        }
        return $output;
    }

    /**
     * Action method that renders any notices coming from the form(s).
     *
     * @param \Siel\Acumulus\Helpers\Form $acumulusForm
     *
     * @return string
     */
    private function showNotices(Form $acumulusForm): string
    {
        $output = '';
        foreach ($acumulusForm->getMessages() as $message) {
            $output .= $this->renderNotice(
                $message->format(Message::Format_PlainWithSeverity),
                $this->severityToNoticeClass($message->getSeverity())
            );
        }
        return $output;
    }

    /**
     * Converts a Severity constant into a notice class.
     *
     * @param int $severity
     *
     * @return string
     */
    private function severityToNoticeClass(int $severity): string
    {
        switch ($severity) {
            case Severity::Success:
                $class = 'success';
                break;
            case Severity::Info:
            case Severity::Notice:
                $class = 'info';
                break;
            case Severity::Warning:
                $class = 'warning';
                break;
            case Severity::Error:
            case Severity::Exception:
                $class = 'error';
                break;
            default:
                $class = '';
                break;
        }
        return $class;
    }

    /**
     * Renders a notice.
     *
     * @param string $message
     * @param string $type
     *   The type of notice, used to construct css classes to distinguish the
     *   different types of messages. error, warning, info, etc.
     * @param string $id
     *   An optional id to use for the outer tag.
     * @param string $class
     *   Optional css classes to add.
     * @param bool $isHtml
     *   Indicates whether $message is html or plain text. plain text will be
     *   embedded in a <p>.
     *
     * @return string
     *   The rendered notice.
     */
    private function renderNotice(
        string $message,
        string $type,
        string $id = '',
        string $class = '',
        bool $isHtml = false
    ): string
    {
        if (!empty($id)) {
            $id = ' id="' . $id . '"';
        }
        if (!empty($class)) {
            $class = " $class";
        }

        $result = "<div$id class='notice notice-$type$class'>";
        if (!$isHtml) {
            $result .= '<p>';
        }
        $result .= $message;
        if (!$isHtml) {
            $result .= '</p>';
        }
        $result .= '</div>';
        return $result;
    }
}
