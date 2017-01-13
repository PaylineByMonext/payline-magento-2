<?php

namespace Monext\Payline\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Monext\Payline\Helper\Constants as HelperConstants;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $data = [];
            $statuses = [
                HelperConstants::ORDER_STATUS_PAYLINE_WAITING_CAPTURE => __('Payline Waiting Capture'),
                HelperConstants::ORDER_STATUS_PAYLINE_CAPTURED => __('Payline Captured'),
                HelperConstants::ORDER_STATUS_PAYLINE_CANCELED  => __('Payline Canceled'),
            ];
            foreach ($statuses as $code => $info) {
                $data[] = ['status' => $code, 'label' => $info];
            }
            
            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status'), 
                ['status', 'label'], 
                $data
            );
            
            $data = [];
            foreach ($statuses as $code => $info) {
                $data[] = ['status' => $code, 'state' => Order::STATE_PROCESSING, 'default' => 0, 'visible_on_front' => 1];
            }
            
            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default', 'visible_on_front'],
                $data
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $data = [];
            $statuses = [
                HelperConstants::ORDER_STATUS_PAYLINE_PENDING => __('Payline Pending'),
            ];
            foreach ($statuses as $code => $info) {
                $data[] = ['status' => $code, 'label' => $info];
            }
            
            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status'), 
                ['status', 'label'], 
                $data
            );
            
            $data = [];
            foreach ($statuses as $code => $info) {
                $data[] = ['status' => $code, 'state' => Order::STATE_NEW, 'default' => 0, 'visible_on_front' => 1];
            }
            
            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default', 'visible_on_front'],
                $data
            );
        }
        
        $setup->endSetup();
    }
}

