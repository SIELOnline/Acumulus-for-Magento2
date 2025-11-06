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

    /**
     * This override returns the path to the mount in the Docker container.
     */
    protected static function getPapercutFolder(): string
    {
        return '/home/erwin/Papercut-SMTP';
    }
}
