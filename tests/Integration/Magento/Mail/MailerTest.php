<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Magento\Mail;

use Siel\Acumulus\Tests\Magento\TestCase;

use function get_class;

/**
 * MailerTest tests whether the mailer class mails messages to the mail server.
 *
 * This test is mainly used to test if the mail feature still works in new versions of the
 * shop.
 */
class MailerTest extends TestCase
{
    public function testMailer(): void
    {
        $this->_testMailer(hasTextPart: false);
    }

    /** Overrides of the {@see \Siel\Acumulus\Tests\Utils\Mail} trait. */
    /**
     * Returns the file path with the server path replaced by the path as it is
     * linked/mounted in this test setup.
     */
    protected static function getEmlFileLink(string $emlFile): string
    {
        return str_replace(
            ['C:\ProgramData\Changemaker Studios\Papercut SMTP\Incoming\\', 'C:\Users\erwin\AppData\Roaming\Changemaker Studios\Papercut SMTP\\'],
            ['/home/erwin/Papercut-SMTP-ProgramData/', '/home/erwin/Papercut-SMTP-AppData/'],
            $emlFile
        );
    }

    protected static function getPapercutFolderUI(): string
    {
        return '/home/erwin/Papercut-SMTP-AppData';
    }

    protected static function getPapercutFolderService(): string
    {
        return '/home/erwin/Papercut-SMTP-ProgramData';
    }
}
