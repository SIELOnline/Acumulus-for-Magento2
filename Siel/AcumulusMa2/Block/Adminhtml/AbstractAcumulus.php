<?php
namespace Siel\AcumulusMa2\Block\Adminhtml;

use Magento\Backend\Block\Widget\Form\Generic;
use Siel\Acumulus\Magento2\Helpers\FormMapper;

/**
 * Base block for rendering Acumulus forms.
 */
class AbstractAcumulus extends Generic
{
    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var \Siel\AcumulusMa2\Helper\Data
     */
    protected $helper;

    /**
     * @var \Siel\Acumulus\Helpers\Form
     */
    private $acumulusForm;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Siel\AcumulusMa2\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Siel\Acumulus\Helpers\Form
     */
    public function getAcumulusForm()
    {
        if (!$this->acumulusForm) {
            $this->acumulusForm = $this->helper->getAcumulusConfig()->getForm($this->type);
        }
        return $this->acumulusForm;
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
     * Adding product form elements for editing attribute
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        // Populate the form using the FormMapper.
        $mapper = new FormMapper();
        $mapper->map($form, $this->getAcumulusForm()->getFields());

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getAcumulusForm()->getFormValues());
        return parent::_initFormValues();
    }

    /**
     * {@inheritdoc}
     */
    public function _prepareLayout()
    {
        // Ensure translations are loaded.
        $this->getAcumulusForm();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => $this->t('button_cancel'),
                'onclick' => "window.location.href = '" . $this->getUrl('admin/dashboard') . "'",
                'class' => 'action-back'
            ]
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => $this->t('button_save'),
                'class' => 'save primary',
                'onclick' => 'document.getElementById("edit_form").submit()',
            ]
        );

        return parent::_prepareLayout();
    }
}
