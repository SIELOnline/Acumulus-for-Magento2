<?php

namespace Siel\AcumulusMa2\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Installs the schema for this extension.
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'acumulus_entry'
         */
        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('acumulus_entry'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true,'identity' => true],
                'Technical key'
            )->addColumn(
                'entry_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null],
                'Acumulus entry id'
            )->addColumn(
                'token',
                Table::TYPE_TEXT,
                32,
                ['nullable' => true, 'default' => null],
                'Acumulus invoice token'
            )->addColumn(
                'source_type',
                Table::TYPE_TEXT,
                20,
                ['nullable' => false],
                'Invoice source type'
            )->addColumn(
                'source_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Magento invoice source id'
            )->addColumn(
                'created',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Timestamp created'
            )->addColumn(
                'updated',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false],
                'Timestamp updated'
            )->addIndex(
                'siel_acumulus_entry_id',
                'entry_id',
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            )->addIndex(
                'siel_acumulus_source',
                ['source_type', 'source_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Acumulus entry table');
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
