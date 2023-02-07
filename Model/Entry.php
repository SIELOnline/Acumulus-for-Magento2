<?php

declare(strict_types=1);

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
     *
     * @noinspection MagicMethodsValidityInspection _construct is the Magento way to
     *   have your own constructor.
     */
    protected function _construct(): void
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $this->_init(\Siel\AcumulusMa2\Model\ResourceModel\Entry::class);
    }
}
