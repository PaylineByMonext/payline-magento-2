<?php

namespace Monext\Payline\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('payline_contract'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'number',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Number'
                )
                ->addColumn(
                    'card_type',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Card Type'
                )
                ->addColumn(
                    'currency',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Currency'
                )
                ->setComment('Payline Contract');
            $setup->getConnection()->createTable($table);
        }
        
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('payline_order_increment_id_token'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )
                ->addColumn(
                    'order_increment_id',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false],
                    'Order Increment Id'
                )
                ->addColumn(
                    'token',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Token'
                )
                ->setComment('Payline Order Increment Id Token');
            $setup->getConnection()->createTable($table);
        }
        
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $table = $setup->getConnection()->addColumn(
                $setup->getTable('payline_contract'), 
                'label', 
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Label',
                    'nullable' => false,
                ]
            );
        }
        
        $setup->endSetup();
    }
}
