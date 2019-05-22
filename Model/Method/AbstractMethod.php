<?php

namespace Monext\Payline\Model\Method;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as CollectionAbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelperData;
use Magento\Payment\Model\Method\AbstractMethod as BaseAbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;
use Monext\Payline\Helper\Data as HelperData;

class AbstractMethod extends BaseAbstractMethod
{
    /**
     * @var PaylinePaymentManagement
     */
    protected $paylinePaymentManagement;
    
    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var HelperData
     */
    protected $helperData;
    
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentHelperData $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        PaylinePaymentManagement $paylinePaymentManagement,
        ContractManagement $contractManagement,
        HelperData $helperData,
        AbstractResource $resource = null,
        CollectionAbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->paylinePaymentManagement = $paylinePaymentManagement;
        $this->contractManagement = $contractManagement;
        $this->helperData = $helperData;
    }
    
    public function isAvailable(CartInterface $quote = null)
    {
        $parentResult = parent::isAvailable($quote);
        $currentResult = count($this->contractManagement->getUsedContracts()) > 0;
        return $parentResult && $currentResult;
    }
    
    public function assignData(DataObject $data)
    {
        parent::assignData($data);
        
        if (isset($data[PaymentInterface::KEY_ADDITIONAL_DATA]['payment_mode'])) {
            $this->getInfoInstance()
                ->setAdditionalInformation('payment_mode', $data[PaymentInterface::KEY_ADDITIONAL_DATA]['payment_mode']);
        }
        
        if (isset($data[PaymentInterface::KEY_ADDITIONAL_DATA]['contract_id']) && $data[PaymentInterface::KEY_ADDITIONAL_DATA]['contract_id'] != -1) {
            $contract = $this->contractManagement->getUsedContracts()->getItemById($data[PaymentInterface::KEY_ADDITIONAL_DATA]['contract_id']);
            
            if (!$contract || !$contract->getId()) {
                throw new \Exception(__('Invalid contract'));
            }
            
            $this->getInfoInstance()
                ->setAdditionalInformation('contract_number', $contract->getNumber());
        }
        
        return $this;
    }
}
