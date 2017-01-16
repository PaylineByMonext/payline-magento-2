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
use Magento\Quote\Api\Data\PaymentInterface;
use Monext\Payline\Model\PaymentManagement as PaylinePaymentManagement;

class AbstractMethod extends BaseAbstractMethod
{
    /**
     * @var PaylinePaymentManagement 
     */
    protected $paylinePaymentManagement;
    
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentHelperData $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        PaylinePaymentManagement $paylinePaymentManagement,
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
    }
    
    public function assignData(DataObject $data)
    {
        parent::assignData($data);
        
        if(isset($data[PaymentInterface::KEY_ADDITIONAL_DATA]['payment_mode'])) {
            $this->getInfoInstance()
                ->setAdditionalInformation('payment_mode', $data[PaymentInterface::KEY_ADDITIONAL_DATA]['payment_mode']);
        }
        
        return $this;
    }
}

