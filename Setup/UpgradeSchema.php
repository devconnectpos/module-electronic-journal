<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SM\ElectronicJournal\Setup;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Sales\Model\OrderFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.1', '<')) {
            $this->addElectronicJournal($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param OutputInterface      $output
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup, OutputInterface $output)
    {
        $output->writeln('  |__ Add electronic journal table');
        $this->addElectronicJournal($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function addElectronicJournal(SchemaSetupInterface $setup)
    {
        $setup->startSetup();

        if ($setup->getConnection()->isTableExists($setup->getTable('sm_electronic_journal'))) {
            $setup->endSetup();

            return;
        }

        $table = $setup->getConnection()->newTable(
            $setup->getTable('sm_electronic_journal')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID'
        )->addColumn(
            'outlet_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Outlet Id'
        )->addColumn(
            'register_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false,],
            'Register Id'
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Message'
        )->addColumn(
            'event_type',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Type'
        )->addColumn(
            'employee_id',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Employee Id'
        )->addColumn(
            'employee_username',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false,],
            'Employee username'
        )->addColumn(
            'amount',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => true,],
            'Amount'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        );

        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }

}
