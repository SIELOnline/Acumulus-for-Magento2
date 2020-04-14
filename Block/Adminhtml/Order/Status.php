<?php

namespace Siel\AcumulusMa2\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Siel\Acumulus\Invoice\Source;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

class Status extends Template implements TabInterface
{
    use HelperTrait;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->formFactory = $formFactory;
        $this->helper = $helper;
        $this->setFormType();

        // Create the form first: this will load the translations.
        /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $acumulusForm */
        $this->acumulusForm = $this->getAcumulusForm();

        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return $this->t('acumulus_invoice_title');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return $this->t('acumulus_invoice_header');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        // @nth: We could use ACL settings to selectively show later.
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        // Always show the tab.
        return false;
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Get Class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('acumulus/order/status', ['_current' => true]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        // Create the form first: this will load the translations.
        /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $acumulusForm */
        $acumulusForm = $this->getAcumulusForm();
        $output = '';
        if ($acumulusForm->hasSource()) {
            $form = $this->formFactory->create(
                ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
            );
            // Populate the form using the FormMapper.
            /** @var \siel\Acumulus\Magento\Helpers\FormMapper $mapper */
            $mapper = $this->getAcumulusContainer()->getFormMapper();
            $mapper->setMagentoForm($form)->map($acumulusForm);
            /** @noinspection PhpUndefinedMethodInspection */
            $form->setUseContainer(false);
            $form->addValues($this->getAcumulusForm()->getFormValues());
            $url = $this->getUrl('acumulus/order/status', ['_current' => true]);
            $wait = $this->t('wait');
            $output .= '<div id="acumulus-invoice-status-overview" class="acumulus-area" data-acumulus-wait="' . $wait . '" data-acumulus-url="' . $url . '">';
            $output .= $this->showNotices($acumulusForm);
            $output .= '<div class="admin__page-section-title"><span class="title">' . $this->getTabTitle() . '</span></div>';
            $output .= $form->getHtml();
            $output .= '</div>';
            $output .= '';
        } else {
            $output .= '<div>Unknown source</div>';
        }
        return $output;
    }

    /**
     * Action method that renders any notices coming from the form(s).
     *
     * @param \Siel\Acumulus\Helpers\Form $form
     *
     * @return string
     */
    public function showNotices($form) {
        $output = '';
        if (isset($form)) {
            foreach ($form->getErrorMessages() as $message) {
                $output .= $this->renderNotice($message, 'error');
            }
            foreach ($form->getWarningMessages() as $message) {
                $output .= $this->renderNotice($message, 'warning');
            }
            foreach ($form->getSuccessMessages() as $message) {
                $output .= $this->renderNotice($message, 'success');
            }
        }
        return $output;
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
     *   Optional css classes to add (besides those that are already added to
     *   get a (dismissible) notice in WP style.
     * @param bool $isHtml
     *   Indicates whether $message is html or plain text. plain text will be
     *   embedded in a <p>.
     *
     * @return string
     *   The rendered notice.
     */
    private function renderNotice($message, $type, $id = '', $class = '', $isHtml = false) {
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