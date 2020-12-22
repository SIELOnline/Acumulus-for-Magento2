<?php
namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

/**
 * Base Acumulus config controller.
 *
 * Note that the Siel\AcumulusMa2\Controller\Adminhtml\Order\Status controller
 * does not derive from this base class, as that handles a tab, not a full page.
 */
abstract class AbstractAcumulus extends Action
{
    use HelperTrait;

    public function __construct(Context $context, Data $helper)
    {
        $this->helper = $helper;
        $this->setFormType();
        parent::__construct($context);
    }

    /**
     * Checks if a user has permissions to visit the related pages.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $resource = [
            'register' => 'config',
            'config' => 'config',
            'advanced' => 'config',
            'batch' => 'batch',
            'invoice' => 'batch',
            'rate' => true,
        ];
        $resource = $resource[$this->getFormType()];
        return is_bool($resource) ? $resource : $this->_authorization->isAllowed('Siel_Acumulus::' . $resource);
    }
}
