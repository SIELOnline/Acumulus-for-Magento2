<?php
/**
 * @noinspection PhpDeprecationInspection
 *    \Magento\Backend\Block\Widget\Form\Generic has been deprecated in favour
 *    of UI component implementation.
 */

namespace Siel\AcumulusMa2\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button as Button;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

/**
 * Base block for rendering Acumulus forms.
 */
abstract class AbstractAcumulusPage extends Generic
{
    use HelperTrait;

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
        $this->setFormType();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm(): AbstractAcumulusPage
    {
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        // Populate the form using the FormMapper.
        /** @var \siel\Acumulus\Magento\Helpers\FormMapper $mapper */
        $mapper = $this->getAcumulusContainer()->getFormMapper();
        $mapper->setMagentoForm($form)->map($this->getAcumulusForm());

        // setUseContainer(true) makes the save button work ...
        /** @noinspection PhpUndefinedMethodInspection */
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues(): AbstractAcumulusPage
    {
        $this->getForm()->addValues($this->getAcumulusForm()->getFormValues());
        return parent::_initFormValues();
    }

    /**
     * @inheritdoc
     */
    public function _prepareLayout()
    {
        // Ensure translations are loaded.
        $acumulusForm = $this->getAcumulusForm();
        if ($acumulusForm->isFullPage()) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->getToolbar()->addChild(
                'back_button',
                Button::class,
                [
                    'label' => $this->t('button_cancel'),
                    'onclick' => "window.location.href = '" . $this->getUrl('admin/dashboard') . "'",
                    'class' => 'action-back'
                ]
            );
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->getToolbar()->addChild(
                'save_button',
                Button::class,
                [
                    'label' => $this->t('button_submit_' . $this->formType),
                    'class' => 'save primary',
                    'onclick' => 'document.getElementById("edit_form").submit()',
                ]
            );
        }
        return parent::_prepareLayout();
    }
}
