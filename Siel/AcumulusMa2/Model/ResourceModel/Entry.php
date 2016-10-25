<?php
namespace Siel\AcumulusMa2\Model\ResourceModel;

/**
 * Acumulus Entry resource model
 */
class Entry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
