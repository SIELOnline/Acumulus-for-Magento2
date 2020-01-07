<?php
namespace Siel\AcumulusMa2\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Siel\AcumulusMa2\Helper\Data;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Base block for rendering Acumulus forms.
 */
abstract class AbstractAcumulus extends Generic
{
    /**
     * @var string
     */
    private $type = '';

    /**
     * @var \Siel\AcumulusMa2\Helper\Data
     */
    private $helper;

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
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $class = static::class;
        if (strrpos($class, '\Interceptor') !== false) {
            $class = substr($class, 0, -strlen('\Interceptor'));
        }
        switch ($class) {
            case 'Siel\AcumulusMa2\Block\Adminhtml\Config\Form':
                $this->type = 'config';
                break;
            case 'Siel\AcumulusMa2\Block\Adminhtml\Config\AdvancedForm':
                $this->type = 'advanced';
                break;
            case 'Siel\AcumulusMa2\Block\Adminhtml\Batch\Form':
                $this->type = 'batch';
                break;
            default:
                $this->helper->getAcumulusContainer()->getLog()->error("Unknown block type $class");
                break;
        }
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Siel\Acumulus\Helpers\Form
     */
    public function getAcumulusForm()
    {
        if (!$this->acumulusForm) {
            $this->acumulusForm = $this->helper->getAcumulusContainer()->getForm($this->type);
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
    protected function t($key)
    {
        return $this->helper->t($key);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        // Populate the form using the FormMapper.
        /** @var \siel\Acumulus\Magento\Helpers\FormMapper $mapper */
        $mapper = $this->helper->getAcumulusContainer()->getFormMapper();
        $mapper->setMagentoForm($form)->map($this->getAcumulusForm());

        // setUseContainer(true) makes the save button work ...
        /** @noinspection PhpUndefinedMethodInspection */
        $form->setUseContainer(true);
        $this->setForm($form);
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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
