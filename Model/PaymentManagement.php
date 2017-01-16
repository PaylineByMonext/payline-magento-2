<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\OrderFactory as OrderFactory;
use Magento\Sales\Model\Order\Payment\Transaction;
//use Magento\Sales\Api\TransactionRepositoryInterface; Cannot use TransactionRepositoryInterface because needed methods are not exposed in
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Monext\Payline\Api\PaymentManagementInterface as PaylinePaymentManagementInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Request\DoCaptureFactory as RequestDoCaptureFactory;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetailsFactory as RequestGetWebPaymentDetailsFactory;

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
     * @var RequestGetWebPaymentDetailsFactory
     */
    protected $requestGetWebPaymentDetailsFactory;
    
    /**
     * @var RequestDoCaptureFactory
     */
    protected $requestDoCaptureFactory;
    
    /**
     * @var PaylineApiClient 
     */
    protected $paylineApiClient;
    
    /**
     * @var PaylineCartManagement 
     */
    protected $paylineCartManagement;
    
    /**
     * @var OrderIncrementIdTokenFactory 
     */
    protected $orderIncrementIdTokenFactory;
    
    /**
     * @var OrderFactory 
     */
    protected $orderFactory;
    
    /**
     * @var TransactionRepository 
     */
    protected $transactionRepository;
    
    public function __construct(
        CartRepositoryInterface $cartRepository, 
        CartTotalRepositoryInterface $cartTotalRepository,
        PaymentInformationManagementInterface $paymentInformationManagement,
        PaymentMethodManagementInterface $paymentMethodManagement,
        RequestDoWebPaymentFactory $requestDoWebPaymentFactory,
        PaylineApiClient $paylineApiClient,
        PaylineCartManagement $paylineCartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        RequestGetWebPaymentDetailsFactory $requestGetWebPaymentDetailsFactory,
        OrderFactory $orderFactory,
        TransactionRepository $transactionRepository,
        RequestDoCaptureFactory $requestDoCaptureFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->requestDoWebPaymentFactory = $requestDoWebPaymentFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->paylineCartManagement = $paylineCartManagement;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->requestGetWebPaymentDetailsFactory = $requestGetWebPaymentDetailsFactory;
        $this->orderFactory = $orderFactory;
        $this->transactionRepository = $transactionRepository;
        $this->requestDoCaptureFactory = $requestDoCaptureFactory;
    }
    
    public function saveCheckoutPaymentInformationFacade(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->paymentInformationManagement->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        $this->paylineCartManagement->reserveCartOrderId($cartId);
        $result = $this->wrapCallPaylineApiDoWebPaymentFacade($cartId);
        
        return $result;
    }
    
    public function wrapCallPaylineApiDoWebPaymentFacade($cartId)
    {
        return $this->callPaylineApiDoWebPaymentFacade(
            $this->cartRepository->getActive($cartId), 
            $this->cartTotalRepository->get($cartId),
            $this->paymentMethodManagement->get($cartId)
        );
    }
    
    public function callPaylineApiDoWebPaymentFacade(
        CartInterface $cart,
        TotalsInterface $totals,
        PaymentInterface $payment
    )
    {
        $result = $this->callPaylineApiDoWebPayment($cart, $totals, $payment);
        $this->orderIncrementIdTokenFactory->create()->associateTokenToOrderIncrementId(
            $cart->getReservedOrderId(), 
            $result['token']
        );
        
        return $result;
    }
    
    protected function callPaylineApiDoWebPayment(
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
            'redirect_url' => $response->getRedirectUrl(),
        );
        
        return $result;
    }
    
    public function callPaylineApiGetWebPaymentDetails($token)
    {
        $request = $this->requestGetWebPaymentDetailsFactory->create();
        $request
            ->setToken($token);
        
        $response = $this->paylineApiClient->callGetWebPaymentDetails($request);
        
        $result = array(
            'transaction' => $response->getTransaction(),
            'payment' => $response->getPayment(),
        );
        
        return $result;
    }
    
    public function callPaylineApiDoCapture(OrderPaymentInterface $payment)
    {
        $authorizationTransaction = $this->transactionRepository->getByTransactionType(
            Transaction::TYPE_AUTH,
            $payment->getId(),
            $payment->getParentId()
        );
        
        $request = $this->requestDoCaptureFactory->create();
        $request
            ->setAuthorizationTransaction($authorizationTransaction);
        
        $response = $this->paylineApiClient->callDoCapture($request);
        
        $result = array(
            'transaction' => $response->getTransaction(),
            'payment' => $response->getPayment(),
        );
        
        return $result;
    }
    
    public function handlePaymentGatewayNotifyByToken($token)
    {
        $result = $this->callPaylineApiGetWebPaymentDetails($token);
        
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        $order = $this->orderFactory->create()->load($orderIncrementId, 'increment_id');
        $orderPayment = $order->getPayment();
        $orderPayment->setTransactionId($result['transaction']['id']);
        
        // TODO Add controls to avoid double authorization/capture
        if($result['payment']['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION) {
            $orderPayment->authorize(false, $order->getGrandTotal());
            $order->setStatus(HelperConstants::ORDER_STATUS_PAYLINE_WAITING_CAPTURE);
        } elseif($result['payment']['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE) {
            $orderPayment->capture();
            $order->setStatus(HelperConstants::ORDER_STATUS_PAYLINE_CAPTURED);
        }
        
        $order->save();
        
        return $this;
    }
    
    public function handlePaymentGatewayCancelByToken($token)
    {
        $orderIncrementId = $this->orderIncrementIdTokenFactory->create()->getOrderIncrementIdByToken($token);
        $order = $this->orderFactory->create()->load($orderIncrementId, 'increment_id');
        
        if($order->canCancel()) {
            $order->cancel();
            $order->setStatus(HelperConstants::ORDER_STATUS_PAYLINE_CANCELED);
        }
        
        $order->save();
        $this->paylineCartManagement->restoreCartFromOrder($order);
        
        return $this;
    }
}
