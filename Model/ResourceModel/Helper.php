<?php

namespace Monext\Payline\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;

class Helper
{
    /**
     * @var ResourceCustomer 
     */
    protected $resourceCustomer;

    public function __construct(ResourceCustomer $resourceCustomer)
    {
        $this->resourceCustomer = $resourceCustomer;
    }

    public function saveCustomerWalletId($customerId, $walletId)
    {
        $connection = $this->resourceCustomer->getConnection();

        $data = array(
            'updated_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'wallet_id' => $walletId
        );

        $where['entity_id = ?'] = $customerId;
        $connection->update($this->resourceCustomer->getEntityTable(), $data, $where);

        return $this;
    }

    public function getCustomerWalletId($customerId)
    {
        $connection = $this->resourceCustomer->getConnection();
        $select = $connection->select()->from(
            $this->resourceCustomer->getEntityTable(),
            ['wallet_id']
        )->where(
            'entity_id = ?', $customerId
        );

        return $connection->fetchOne($select);
    }

    public function hasCustomerWalletId($customerId)
    {
        $walletId =$this->getCustomerWalletId($customerId);
        return !empty($walletId);
    }
}

