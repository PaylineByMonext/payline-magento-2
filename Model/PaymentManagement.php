<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Monext\Payline\Api\PaymentManagementInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;

class PaymentManagement implements PaymentManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
    
    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;
    
    /**
     * @var PaymentInformationManagementInterface 
     */
    protected $paymentInformationManagement;
    
    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;
    
    /**
     * @var RequestDoWebPaymentFactory
     */
    protected $requestDoWebPaymentFactory;
    
    /**
     * @var PaylineApiClient 
     */
    protected $paylineApiClient;
    
    /**
     * @var Registry 
     */
    protected $registry;
    
    public function __construct(
        CartRepositoryInterface $cartRepository, 
        CartTotalRepositoryInterface $cartTotalRepository,
        PaymentInformationManagementInterface $paymentInformationManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        RequestDoWebPaymentFactory $requestDoWebPaymentFactory,
        PaylineApiClient $paylineApiClient,
        Registry $registry
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->requestDoWebPaymentFactory = $requestDoWebPaymentFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->registry = $registry;
    }
    
    public function savePaymentInformationAndCallPaylineApiDoWebPayment(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->paymentInformationManagement->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        $this->wrapCallPaylineApiDoWebPayment($cartId);
        return $this;
    }
    
    public function wrapCallPaylineApiDoWebPayment($cartId)
    {
        return $this->callPaylineApiDoWebPayment(
            $this->cartRepository->getActive($cartId), 
            $this->cartTotalRepository->get($cartId),
            $this->paymentMethodManagement->get($cartId)
        );
    }
    
    public function callPaylineApiDoWebPayment(
        CartInterface $cart,
        TotalsInterface $totals,
        PaymentInterface $payment
    )
    {
        $request = $this->requestDoWebPaymentFactory->create();
        $request
            ->setCart($cart)
            ->setTotals($totals)
            ->setPayment($payment);
        
        $response = $this->paylineApiClient->callDoWebPayment($request);
        
        $result = array(
            'token' => $response->getToken(), 
            'redirect_url' => $response->getRedirectUrl()
        );
        
        $this->registry->register(HelperConstants::REGISTRY_KEY_LAST_RESPONSE_DO_WEB_PAYMENT_DATA, $result);
        
        return $result;
    }
}
