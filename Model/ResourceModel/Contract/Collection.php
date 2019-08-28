<?php

namespace Monext\Payline\Model\ResourceModel\Contract;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Monext\Payline\Model\Contract', 'Monext\Payline\Model\ResourceModel\Contract');
    }
}
