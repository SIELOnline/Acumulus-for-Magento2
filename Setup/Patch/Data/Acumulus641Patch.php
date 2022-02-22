<?php
namespace Siel\AcumulusMa2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Siel\Acumulus\Magento\Magento2\Helpers\Registry;

/**
 * Class Acumulus641Patch
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
        $helper->getAcumulusContainer()->getConfig()->upgrade('');

        $this->moduleDataSetup->getConnection()->endSetup();
        return $this;
    }
}
