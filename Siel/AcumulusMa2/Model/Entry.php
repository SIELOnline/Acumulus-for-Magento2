<?php
namespace Siel\AcumulusMa2\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Acumulus entry model.
 *
 * @method int getEntryId()
 * @method Entry setEntryId(int $value)
 * @method string getToken()
 * @method Entry setToken(string $value)
 * @method string getSourceType()
 * @method Entry setSourceType(string $value)
 * @method int getSourceId()
 * @method Entry setSourceId(int $value)
 * @method string getCreated()
 * @method Entry setCreated(string $value)
 * @method string getUpdated()
 * @method Entry setUpdated(string $value)
 */
class Entry extends AbstractModel
{
    /**
     * Initialize Acumulus Entry Model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Siel\AcumulusMa2\Model\ResourceModel\Entry');
    }
}
