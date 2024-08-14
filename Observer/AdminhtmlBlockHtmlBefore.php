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
 * @noinspection PhpUnused  Observers are instantiated by the event handler
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
            $message = $this->helper->getAcumulusContainer()->getCheckAccount()->doCheck();
            if (empty($message)) {
                $menuModel->remove('Siel_Acumulus::acumulus_register');
            }
        }
    }
}
