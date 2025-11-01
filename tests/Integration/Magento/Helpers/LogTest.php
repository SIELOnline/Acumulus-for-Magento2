<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Magento\Helpers;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\DirectoryList;
use Siel\Acumulus\Tests\Magento\TestCase;

/**
 * LogTest tests whether the log class logs messages to a log file.
 *
 * This test is mainly used to test if the log feature still works in new versions of the
 * shop.
 */
class LogTest extends TestCase
{
    private function getLogFolder(): string
    {
        /** @var DirectoryList $directory */
        $directory = ObjectManager::getInstance()->get(DirectoryList::class);
        return $directory->getRoot() . '/var/log';
    }

    protected function getLogPath(): string
    {
        return $this->getLogFolder() . '/acumulus.log';
    }

    public function testLog(): void
    {
        $this->_testLog();
    }
}
