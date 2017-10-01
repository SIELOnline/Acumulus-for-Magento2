<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml\Batch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Siel\AcumulusMa2\Controller\Adminhtml\AbstractAcumulus;

/**
 * Acumulus batch controller.
 */
class Index extends AbstractAcumulus
{
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context, $resultPageFactory);
        $this->type = 'batch';
    }
}
