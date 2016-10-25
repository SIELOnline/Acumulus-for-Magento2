<?php
namespace Siel\AcumulusMa2\Block\Adminhtml\Config;

use Siel\AcumulusMa2\Block\Adminhtml\AbstractAcumulus;

/**
 * Block for rendering the Acumulus config form.
 */
class Form extends AbstractAcumulus
{
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
        $this->type = 'config';
        parent::__construct($context, $registry, $formFactory, $helper, $data);
    }
}
