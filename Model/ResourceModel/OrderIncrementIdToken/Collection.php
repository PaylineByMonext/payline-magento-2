<?php

namespace Monext\Payline\Model\ResourceModel\OrderIncrementIdToken;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Monext\Payline\Model\OrderIncrementIdToken', 'Monext\Payline\Model\ResourceModel\OrderIncrementIdToken');
    }
}