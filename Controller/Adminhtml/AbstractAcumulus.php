<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Siel\AcumulusMa2\Helper\Data;
use Siel\AcumulusMa2\Helper\HelperTrait;

use function is_bool;

/**
 * Base Acumulus config controller.
 *
 * Note that the Siel\AcumulusMa2\Controller\Adminhtml\Order\Status controller
 * does not derive from this base class, as that handles a tab, not a full page.
 */
abstract class AbstractAcumulus extends Action
{
    use HelperTrait;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Siel\AcumulusMa2\Helper\Data $helper
     */
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
     *
     * @noinspection PhpMissingParentCallCommonInspection The base implementation is not needed
     */
    protected function _isAllowed(): bool
    {
        $resource = [
            'register' => 'config',
            'activate' => 'config',
            'settings' => 'config',
            'mappings' => 'config',
            'batch' => 'batch',
            'invoice' => 'batch',
            'rate' => true,
        ];
        $resource = $resource[$this->getFormType()];
        return is_bool($resource) ? $resource : $this->_authorization->isAllowed("Siel_Acumulus::$resource");
    }
}
