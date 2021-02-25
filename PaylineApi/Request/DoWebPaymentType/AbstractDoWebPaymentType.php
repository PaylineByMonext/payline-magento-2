<?php

namespace Monext\Payline\PaylineApi\Request\DoWebPaymentType;

use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Monext\Payline\Helper\Data as PaylineHelper;
use Monext\Payline\Model\ContractManagement;

abstract class AbstractDoWebPaymentType
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CheckoutSession
     */
    protected $cart;

    /**
     * @var ContractManagement
     */
    protected $contractManagement;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var PaylineHelper
     */
    protected $paylineHelper;

    /**
     * @var PaymentInterface|null
     */
    private $payment;


    /**
     * AbstractDoWebPaymentType constructor.
     * @param UrlInterface $urlBuilder
     * @param CheckoutSession $cart
     * @param ScopeConfigInterface $scopeConfig
     * @param ContractManagement $contractManagement
     * @param PaylineHelper $paylineHelper
     * @param PaymentInterface|null $payment
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CheckoutSession $cart,
        ScopeConfigInterface $scopeConfig,
        ContractManagement $contractManagement,
        PaylineHelper $paylineHelper,
        PaymentInterface $payment = null
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->cart = $cart;
        $this->contractManagement = $contractManagement;
        $this->scopeConfig = $scopeConfig;
        $this->payment = $payment;
        $this->paylineHelper = $paylineHelper;
    }

    /**
     * @param $data array
     * @return array
     * @throws \Exception
     */
    abstract public function getData(&$data);

    /**
     * @return PaymentInterface
     * @throws \Exception
     */
    public function getPayment()
    {
        if(is_null($this->payment)) {
            throw new \Exception('Payment not set');
        }
        return $this->payment;
    }

    public function setPayment(PaymentInterface $payment)
    {
        $this->payment = $payment;
        return $this;
    }
}
