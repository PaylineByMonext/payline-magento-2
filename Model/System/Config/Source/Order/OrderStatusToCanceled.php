<?php

namespace Monext\Payline\Model\System\Config\Source\Order;

use Magento\Sales\Model\Config\Source\Order\Status;
use Magento\Sales\Model\Order;

class OrderStatusToCanceled extends Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = Order::STATE_CANCELED;
}
