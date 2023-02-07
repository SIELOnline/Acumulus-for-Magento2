<?php

declare(strict_types=1);

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
     *
     * @noinspection MagicMethodsValidityInspection _construct is the Magento way to
     *   have your own constructor.
     */
    protected function _construct(): void
    {
        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        $this->_init(\Siel\AcumulusMa2\Model\Entry::class, \Siel\AcumulusMa2\Model\ResourceModel\Entry::class);
    }
}
