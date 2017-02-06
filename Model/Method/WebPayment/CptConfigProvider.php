<?php

namespace Monext\Payline\Model\Method\WebPayment;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\ContractManagement;
use Monext\Payline\Model\Method\AbstractMethodConfigProvider;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

class CptConfigProvider extends AbstractMethodConfigProvider
{
    /**
     * @var ContractManagement 
     */
    protected $contractManagement;
    
    /**
     * @var MethodInterface 
     */
    protected $method;

    public function __construct(
        PaymentHelper $paymentHelper,
        AssetRepository $assetRepository,
        ContractManagement $contractManagement,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($paymentHelper, $assetRepository, $contractManagement, $urlBuilder);
        $this->method = $this->paymentHelper->getMethodInstance(HelperConstants::WEB_PAYMENT_CPT);
    }

    public function getConfig()
    {
        $config = parent::getConfig();
        
        $config['payment']['paylineWebPaymentCpt']['integrationType'] = $this->getMethodConfigData('integration_type');

        return $config;
    }
    
    public function getCardTypeImageFileNames()
    {
        return [
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_CB => 'cb.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_CB_3DS => 'cb.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_PAYPAL => 'paypal.png',
            PaylineApiConstants::PAYMENT_CONTRACT_CARD_TYPE_AMEX => 'amex.gif',
        ];
    }
}