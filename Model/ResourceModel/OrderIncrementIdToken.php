<?php

namespace Monext\Payline\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderIncrementIdToken extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('payline_order_increment_id_token', 'id');
    }
}
