<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Block\Adminhtml\Plugin;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Data\FormFactory;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

/**
 * Block for rendering the "Rate Acumulus module" question.
 */
class Rate extends Template
{
    use HelperTrait;

    protected FormFactory $formFactory;
    protected Validator $_formKeyValidator;

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
        $this->setHelper($helper);
        $this->setFormType();

        // Create the form first: this will load the translations.
        /** @var \Siel\Acumulus\Shop\InvoiceStatusForm $acumulusForm */
        $this->acumulusForm = $this->getAcumulusForm();

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml(): string
    {
        // Create the form first: this will load the translations.
        /** @var \Siel\Acumulus\Shop\RatePluginForm $acumulusForm */
        $acumulusForm = $this->getAcumulusForm();
        $output = '';
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
        $url = $this->getUrl('acumulus/plugin/rate');
        $wait = $this->t('wait');
        $output .= '<div id="acumulus-rate-plugin-message" class="acumulus-area" data-acumulus-wait="'
                   . $wait . '" data-acumulus-url="' . $url . '">';
        $output .= $form->getHtml();
        $output .= '</div>';
        $output .= '';
        return $output;
    }
}
