<?php

namespace Monext\Payline\Model;

use Magento\Framework\Model\AbstractModel;

class Contract extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Monext\Payline\Model\ResourceModel\Contract');
    }
}
