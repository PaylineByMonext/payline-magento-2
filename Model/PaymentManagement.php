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
use Monext\Payline\Api\PaymentManagementInterface as PaylinePaymentManagementInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;

class PaymentManagement implements PaylinePaymentManagementInterface
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
    
    /**
     * @var PaylineCartManagement 
     */
    protected $paylineCartManagement;
    
    /**
     * @var OrderIncrementIdTokenFactory 
     */
    protected $orderIncrementIdTokenFactory;
    
    public function __construct(
        CartRepositoryInterface $cartRepository, 
        CartTotalRepositoryInterface $cartTotalRepository,
        PaymentInformationManagementInterface $paymentInformationManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        RequestDoWebPaymentFactory $requestDoWebPaymentFactory,
        PaylineApiClient $paylineApiClient,
        Registry $registry,
        PaylineCartManagement $paylineCartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->requestDoWebPaymentFactory = $requestDoWebPaymentFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->registry = $registry;
        $this->paylineCartManagement = $paylineCartManagement;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
    }
    
    public function savePaymentInformationFacade(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->paymentInformationManagement->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        $this->paylineCartManagement->reserveCartOrderId($cartId);
        $result = $this->wrapCallPaylineApiDoWebPayment($cartId);
        $this->orderIncrementIdTokenFactory->create()->associateTokenToOrderIncrementId(
            $this->cartRepository->getActive($cartId)->getReservedOrderId(), 
            $result['token']
        );
        return $result;
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
