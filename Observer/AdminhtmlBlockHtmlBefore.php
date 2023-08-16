<?php

declare(strict_types=1);

namespace Siel\AcumulusMa2\Observer;

use Magento\Backend\Block\Menu as MenuBlock;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Siel\AcumulusMa2\Helper\Data;

/**
 * AdminhtmlBlockHtmlBefore deletes the menu-items from this Acumulus module that are not
 * active.
 *
 * Thanks to https://magento.stackexchange.com/a/236547/38943.
 *
 * @noinspection PhpUnused
 */
class AdminhtmlBlockHtmlBefore implements ObserverInterface
{
    private Data $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function execute(Observer $observer): void
    {
        /** @noinspection PhpUndefinedMethodInspection  handled by magic __call */
        $block = $observer->getBlock();

        if ($block instanceof MenuBlock) {
            $menuModel = $block->getMenuModel();
            $usesNewCode = $this->helper->getAcumulusContainer()->getShopCapabilities()->usesNewCode();
            if ($usesNewCode) {
                $menuModel->remove('Siel_Acumulus::acumulus_config');
                $menuModel->remove('Siel_Acumulus::acumulus_advanced_config');
            } else {
                $menuModel->remove('Siel_Acumulus::acumulus_settings');
                $menuModel->remove('Siel_Acumulus::acumulus_mappings');
            }
        }
    }
}
