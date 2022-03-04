<?php
namespace Siel\AcumulusMa2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Siel\Acumulus\Magento\Helpers\Registry;

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
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

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
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var \Siel\AcumulusMa2\Helper\Data $helper */
        $helper = Registry::getInstance()->get('Siel\AcumulusMa2\Helper\Data');
        $helper->getAcumulusContainer()->getConfigUpgrade()->upgrade('');

        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }
}
