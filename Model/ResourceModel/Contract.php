<?php

namespace Monext\Payline\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Contract extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('payline_contract', 'id');
    }
}