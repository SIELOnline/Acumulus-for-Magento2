<?php
namespace Siel\AcumulusMa2\Model\ResourceModel\Entry;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Acumulus Entries collection
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model and model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Siel\AcumulusMa2\Model\Entry', 'Siel\AcumulusMa2\Model\ResourceModel\Entry');
    }
}
