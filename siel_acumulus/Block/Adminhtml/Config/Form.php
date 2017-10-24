<?php
namespace Siel\AcumulusMa2\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Siel\AcumulusMa2\Helper\Data;
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
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $helper, 'config', $data);
    }
}
