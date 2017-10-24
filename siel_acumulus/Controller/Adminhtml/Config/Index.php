<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml\Config;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Siel\AcumulusMa2\Controller\Adminhtml\AbstractAcumulus;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Acumulus config controller.
 */
class Index extends AbstractAcumulus
{
    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, Data $helper)
    {
        parent::__construct($context, $resultPageFactory, $helper,'config');
    }
}
