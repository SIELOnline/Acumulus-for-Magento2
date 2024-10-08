<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Siel\Acumulus\Magento\Helpers\Registry;
use Siel\AcumulusMa2\Helper\Data;

/**
 * Class Acumulus641Patch ensures that the configVersion value gets an initial
 * value. After that, upgrading config values will be done automatically in the
 * Config class itself.
 *
 * Patches, like this one, get executed on install, so also on installation the
 * configVersion will be set.
 */
class Acumulus641Patch implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply(): static
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var \Siel\AcumulusMa2\Helper\Data $helper */
        $helper = Registry::getInstance()->get(Data::class);
        $helper->getAcumulusContainer()->getConfigUpgrade()->upgrade('');

        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }
}
