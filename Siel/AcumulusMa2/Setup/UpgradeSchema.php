<?php

namespace Siel\AcumulusMa2\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Siel\Acumulus\Magento2\Helpers\Registry;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if($context->getVersion()) {
            // Previous version found, this is an upgrade, not an installation.
            /** @var \Siel\AcumulusMa2\Helper\Data $helper */
            $helper = Registry::getInstance()->get('Siel\AcumulusMa2\Helper\Data');
            $helper->getAcumulusConfig()->upgrade($context->getVersion());
        }
        $setup->endSetup();
    }
}
