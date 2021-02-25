<?php
namespace Monext\Payline\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Monext\Payline\Helper\Constants as HelperConstants;

/**
 */
class AddOrderStatusCyclePaymentToOrderStateComplete implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status'),
            ['status', 'label'],
            [['status' => HelperConstants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE, 'label' => __('Payment NX/REC in progress')]]
        );

        $this->moduleDataSetup->getConnection()->insertArray(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            [['status' => HelperConstants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE, 'state' => Order::STATE_COMPLETE, 'default' => 0, 'visible_on_front' => 1]]
        );
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
