<?php
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
     */
    protected function _construct()
    {
        $this->_init('acumulus_entry', 'entity_id');
    }
}
