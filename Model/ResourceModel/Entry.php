<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Acumulus Entry resource model
 */
class Entry extends AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     *
     * @noinspection MagicMethodsValidityInspection _construct is the Magento way to
     *   have your own constructor.
     */
    protected function _construct(): void
    {
        $this->_init('acumulus_entry', 'entity_id');
    }
}
