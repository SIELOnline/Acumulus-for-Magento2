<?php

namespace Siel\AcumulusMa2\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Siel\Acumulus\Magento\Magento2\Helpers\Registry;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()) {
            $currentVersion = $context->getVersion();
            // Previous version found, this is an upgrade, not an installation.
            /** @var \Siel\AcumulusMa2\Helper\Data $helper */
            $helper = Registry::getInstance()->get('Siel\AcumulusMa2\Helper\Data');
            $helper->getAcumulusContainer()->getConfig()->upgrade($currentVersion);

            if (version_compare($currentVersion, '6.0.0', '<')) {
                $this->upgrade600($setup);
            }
        }

        $setup->endSetup();
    }

    private function upgrade600(SchemaSetupInterface $installer)
    {
        $installer->startSetup();

        /**
         * Drop and recreate index (to make it non-unique).
         */
        $installer
            ->getConnection()
            ->dropIndex('acumulus_entry', 'siel_acumulus_entry_id');
        $installer
            ->getConnection()
            ->addIndex('acumulus_entry', 'siel_acumulus_entry_id', 'entry_id');

    }
}
