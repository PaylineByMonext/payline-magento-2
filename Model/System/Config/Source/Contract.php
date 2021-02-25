<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\Model\ResourceModel\Contract\CollectionFactory as ContractCollectionFactory;

class Contract implements OptionSourceInterface
{
    /**
     * @var ContractCollectionFactory
     */
    protected $contractCollectionFactory;

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    public function __construct(
        ContractCollectionFactory $contractCollectionFactory,
        ContractManagement $contractManagement
    ) {
        $this->contractCollectionFactory = $contractCollectionFactory;
        $this->contractManagement = $contractManagement;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $this->contractManagement->importContracts();

        $result = array();
        // TODO Use a contract repository for this
        $contractCollection = $this->contractCollectionFactory->create();

        foreach ($contractCollection as $contract) {
            $result[] = [
                'value' => $contract->getId(),
                'label' => $contract->getPointOfSellLabel() . ' : ' . $contract->getLabel() . ' (' . $contract->getCardType() . ')',
            ];
        }

        if (empty($result)) {
            $result[] = [
                'value' => '',
                'label' => __('No contracts available.'),
            ];
        }

        return $result;
    }
}
