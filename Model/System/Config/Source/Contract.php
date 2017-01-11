<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Monext\Payline\Model\ResourceModel\Contract\CollectionFactory as ContractCollectionFactory;

class Environment implements ArrayInterface
{
    protected $contractCollectionFactory;
    
    public function __construct(
        ContractCollectionFactory $contractCollectionFactory
    )
    {
        $this->contractCollectionFactory = $contractCollectionFactory;
    }
    
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = array();
        $contractCollection = $this->contractCollectionFactory->create();

        foreach($contractCollection as $contract) {
            $result[] = [
                'value' => $contract->getNumber(),
                'label' => $contract->getLabel(),
            ];
        }

        return $result;
    }
}
