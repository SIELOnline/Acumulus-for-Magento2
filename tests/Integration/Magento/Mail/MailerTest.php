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
        $classParts = explode('\\', get_class($this));
        // Extract parts between 'Integration' and 'Mail'.
        array_pop($classParts);
        $shopName = '';
        while (($shopNamePart = array_pop($classParts)) !== 'Integration') {
            $shopName = $shopNamePart . '\\' . $shopName;
        }
        $subject = "___$shopName test mail___";
        $bodyText = 'Text test message';
        $bodyHtml = '<p>HTML Test message</p>';
        $mailer = self::getContainer()->getMailer();
        $result = $mailer->sendAdminMail($subject, $bodyText, $bodyHtml);
        $this->assertTrue($result, 'Sending mail failed');
        $this->assertMailServerReceivedMail($subject, $bodyText, $bodyHtml);
    }

    protected function assertMailContentsMatches(string $emlFile, string $bodyText, string $bodyHtml): void
    {
        // Switch the eml file path to Magento mount.
        $emlFile = str_replace('C:\ProgramData\Changemaker Studios\Papercut SMTP\Incoming\\', '/home/erwin/Papercut-SMTP/', $emlFile);
        parent::assertMailContentsMatches($emlFile, $bodyText, $bodyHtml);
    }
}
