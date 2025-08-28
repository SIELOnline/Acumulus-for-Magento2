<?php

declare(strict_types=1);

namespace Siel\Acumulus\Tests\Integration\Magento\Shop;

use DateTime;
use DateTimeZone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Siel\Acumulus\Invoice\Source;
use Siel\Acumulus\Magento\Helpers\Registry;
use Siel\Acumulus\Shop\AcumulusEntry;
use Siel\Acumulus\Shop\AcumulusEntryManager;
use Siel\Acumulus\Tests\Magento\TestCase;

/**
 * AcumulusEntryTest tests the CRUD actions on the acumulus entries storage in Joomla.
 *
 * @todo: Change annotations to attributes once PHPUnit 10 is used.
 */
class AcumulusEntryManagerTest extends TestCase
{
    // This should be an existing test source tht is not prone to further changes.
    private const testSourceType = Source::Order;
    private const testSourceId = 2;
    private const testEntryId = 1; // Acumulus entry ids are auto incrementing and will never equal this anymore.
    private const testToken = 'TESTTOKEN01234567898765TESTTOKEN';

    private function getAcumulusEntryManager(): AcumulusEntryManager
    {
        return static::getContainer()->getAcumulusEntryManager();
    }

    private function getShopTimeZone(): string
    {
        /** @var TimezoneInterface $timezone */
        $timezone = Registry::getInstance()->get(TimezoneInterface::class);
        return $timezone->getDefaultTimezone();
    }

    /**
     * This method is run to clean up the Entry record for the test source used in these
     * tests.
     */
    public function testDeleteForTestSource(): Source
    {
        $acumulusEntryManager = $this->getAcumulusEntryManager();
        $source = static::getContainer()->createSource(static::testSourceType, static::testSourceId);
        $entry = $acumulusEntryManager->getByInvoiceSource($source);
        self::assertTrue($entry === null || $acumulusEntryManager->delete($entry));
        return $source;
    }

    /**
     * Tests creating an acumulus entry and the getByInvoiceSource() method.
     *
     * @depends testDeleteForTestSource
     * @throws \Exception
     */
    public function testCreate(Source $source): Source
    {
        $acumulusEntryManager = $this->getAcumulusEntryManager();
        /** @noinspection PhpUnhandledExceptionInspection */
        $now = new DateTime('now', new DateTimeZone($this->getShopTimeZone()));
        self::assertTrue($acumulusEntryManager->save($source, AcumulusEntry::conceptIdUnknown, null));

        $entry = $acumulusEntryManager->getByInvoiceSource($source);
        self::assertInstanceOf(AcumulusEntry::class, $entry);
        self::assertSame(static::testSourceType, $entry->getSourceType());
        self::assertSame(static::testSourceId, $entry->getSourceId());
        self::assertSame(AcumulusEntry::conceptIdUnknown, $entry->getConceptId());
        self::assertNull($entry->getEntryId());
        self::assertNull($entry->getToken());
        // Checks that the timezone is correct, 25s is a large interval but is for when we are debugging.
        self::assertEqualsWithDelta(0, $this->getDiffInSeconds($entry->getCreated(), $now), 25);
        $diff = $this->getDiffInSeconds($entry->getCreated(), $entry->getUpdated());
        self::assertSame(0, $diff);

        return $source;
    }

    /**
     * Tests updating an acumulus entry and the getByEntryId() method.
     *
     * @depends testCreate
     * @throws \Exception
     */
    public function testUpdate(Source $source): Source
    {
        $acumulusEntryManager = $this->getAcumulusEntryManager();
        $entry = $acumulusEntryManager->getByInvoiceSource($source);
        $created = $entry->getCreated();
        $updated = $entry->getUpdated();
        $now = new DateTime();
        sleep(1);
        self::assertTrue($acumulusEntryManager->save($source, static::testEntryId, static::testToken));

        $entry = $acumulusEntryManager->getByEntryId(static::testEntryId);
        self::assertInstanceOf(AcumulusEntry::class, $entry);
        self::assertSame(Source::Order, $entry->getSourceType());
        self::assertSame(static::testSourceId, $entry->getSourceId());
        self::assertNull($entry->getConceptId());
        self::assertSame(static::testEntryId, $entry->getEntryId());
        self::assertSame(static::testToken, $entry->getToken());
        $diff = $this->getDiffInSeconds($entry->getCreated(), $created);
        self::assertSame(0, $diff);
        $diff = $this->getDiffInSeconds($entry->getUpdated(), $updated);
        self::assertNotSame(0, $diff);
        // Checks that the timezone is correct
        $diff = $this->getDiffInSeconds($entry->getUpdated(), $now);
        self::assertEqualsWithDelta(0, $diff, 25);

        return $source;
    }

    /**
     * Tests deleting an acumulus entry.
     *
     * @depends testCreate
     */
    public function testDelete(): void
    {
        $acumulusEntryManager = $this->getAcumulusEntryManager();
        $entry = $acumulusEntryManager->getByEntryId(static::testEntryId);
        self::assertTrue($acumulusEntryManager->delete($entry));
        self::assertNull($acumulusEntryManager->getByEntryId(static::testEntryId));
    }
}
