<?php

namespace Monext\Payline\Model;

use Magento\Framework\Model\AbstractModel;

class OrderIncrementIdToken extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Monext\Payline\Model\ResourceModel\OrderIncrementIdToken');
    }
    
    // TODO Put this in a dedicated repository
    public function associateTokenToOrderIncrementId($orderIncrementId, $token)
    {
        $itemCandidate = $this->getCollection()
            ->addFieldToFilter('order_increment_id', $orderIncrementId)
            ->getFirstItem();
        
        if(empty($itemCandidate) || !$itemCandidate->getId()) {
            $item = $this->getCollection()->getNewEmptyItem();
            $item
                ->setOrderIncrementId($orderIncrementId);
        } else {
            $item = $itemCandidate;
        }
        
        $item
            ->setToken($token)
            ->save();
        
        return $this;
    }
    
    // TODO Put this in a dedicated repository
    public function getOrderIncrementIdByToken($token)
    {
        $itemCandidate = $this->getCollection()
            ->addFieldToFilter('token', $token)
            ->getFirstItem();
        
        if(empty($itemCandidate) || !$itemCandidate->getId()) {
            // TODO Throw Exception
        }
        
        return $itemCandidate->getOrderIncrementId();
    }
}

