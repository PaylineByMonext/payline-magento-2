<?php

namespace Monext\Payline\Model;

use Magento\Checkout\Api\PaymentInformationManagementInterface as CheckoutPaymentInformationManagementInterface;
use Magento\Quote\Api\BillingAddressManagementInterface as QuoteBillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface as QuotePaymentMethodManagementInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface as QuoteShippingAddressManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order as Order;
use Magento\Sales\Model\Order\Payment\Transaction;
//use Magento\Sales\Api\TransactionRepositoryInterface; Cannot use TransactionRepositoryInterface because needed methods are not exposed in
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Monext\Payline\Api\PaymentManagementInterface as PaylinePaymentManagementInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;
use Monext\Payline\Model\OrderManagement as PaylineOrderManagement;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Request\DoCaptureFactory as RequestDoCaptureFactory;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetailsFactory as RequestGetWebPaymentDetailsFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetailsFactory;

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
     * @var CheckoutPaymentInformationManagementInterface 
     */
    protected $checkoutPaymentInformationManagement;
    
    /**
     * @var QuotePaymentMethodManagementInterface
     */
    protected $quotePaymentMethodManagement;
    
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
     * @var TransactionRepository 
     */
    protected $transactionRepository;
    
    /**
     * @var QuoteBillingAddressManagementInterface 
     */
    protected $quoteBillingAddressManagement;
    
    /**
     * @var QuoteShippingAddressManagementInterface 
     */
    protected $quoteShippingAddressManagement;
    
    /**
     * @var PaylineOrderManagement 
     */
    protected $paylineOrderManagement;
    
    public function __construct(
        CartRepositoryInterface $cartRepository, 
        CartTotalRepositoryInterface $cartTotalRepository,
        CheckoutPaymentInformationManagementInterface $checkoutPaymentInformationManagement,
        QuotePaymentMethodManagementInterface $quotePaymentMethodManagement,
        RequestDoWebPaymentFactory $requestDoWebPaymentFactory,
        PaylineApiClient $paylineApiClient,
        PaylineCartManagement $paylineCartManagement,
        OrderIncrementIdTokenFactory $orderIncrementIdTokenFactory,
        RequestGetWebPaymentDetailsFactory $requestGetWebPaymentDetailsFactory,
        TransactionRepository $transactionRepository,
        RequestDoCaptureFactory $requestDoCaptureFactory,
        QuoteBillingAddressManagementInterface $quoteBillingAddressManagement,
        QuoteShippingAddressManagementInterface $quoteShippingAddressManagement,
        PaylineOrderManagement $paylineOrderManagement
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->checkoutPaymentInformationManagement = $checkoutPaymentInformationManagement;
        $this->quotePaymentMethodManagement = $quotePaymentMethodManagement;
        $this->requestDoWebPaymentFactory = $requestDoWebPaymentFactory;
        $this->paylineApiClient = $paylineApiClient;
        $this->paylineCartManagement = $paylineCartManagement;
        $this->orderIncrementIdTokenFactory = $orderIncrementIdTokenFactory;
        $this->requestGetWebPaymentDetailsFactory = $requestGetWebPaymentDetailsFactory;
        $this->transactionRepository = $transactionRepository;
        $this->requestDoCaptureFactory = $requestDoCaptureFactory;
        $this->quoteBillingAddressManagement = $quoteBillingAddressManagement;
        $this->quoteShippingAddressManagement = $quoteShippingAddressManagement;
        $this->paylineOrderManagement = $paylineOrderManagement;
    }
    
    public function saveCheckoutPaymentInformationFacade(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->checkoutPaymentInformationManagement->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        $result = $this->wrapCallPaylineApiDoWebPaymentFacade($cartId);

        return $result;
    }
    
    public function wrapCallPaylineApiDoWebPaymentFacade($cartId)
    {
        $response = $this->callPaylineApiDoWebPaymentFacade(
            $this->cartRepository->getActive($cartId), 
            $this->cartTotalRepository->get($cartId),
            $this->quotePaymentMethodManagement->get($cartId),
            $this->quoteBillingAddressManagement->get($cartId),
            $this->quoteShippingAddressManagement->get($cartId)
        );

        return [
            'token' => $response->getToken(), 
            'redirect_url' => $response->getRedirectUrl(),
        ];
    }
    
    protected function callPaylineApiDoWebPaymentFacade(
        CartInterface $cart,
        TotalsInterface $totals,
        PaymentInterface $payment,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    )
    {
        $this->paylineCartManagement->handleReserveCartOrderId($cart->getId());

        if($cart->getIsVirtual()) {
            $shippingAddress = null;
        }

        $response = $this->callPaylineApiDoWebPayment($cart, $totals, $payment, $billingAddress, $shippingAddress);

        if(!$response->isSuccess()) {
            // TODO log
            throw new \Exception($response->getShortErrorMessage());
        }

        $this->orderIncrementIdTokenFactory->create()->associateTokenToOrderIncrementId(
            $cart->getReservedOrderId(), 
            $response->getToken()
        );
        
        return $response;
    }
    
    protected function callPaylineApiDoWebPayment(
        CartInterface $cart,
        TotalsInterface $totals,
        PaymentInterface $payment,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    )
    {
        $request = $this->requestDoWebPaymentFactory->create();
        $request
            ->setCart($cart)
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setTotals($totals)
            ->setPayment($payment);

        return $this->paylineApiClient->callDoWebPayment($request);
    }

    public function wrapCallPaylineApiGetWebPaymentDetails($token)
    {
        $response = $this->callPaylineApiGetWebPaymentDetails($token);

        return [
            'is_success' => $response->isSuccess(), 
            'payment_data' => $response->isSuccess() ? $response->getPaymentData() : false,
            'transaction_data' => $response->isSuccess() ? $response->getTransactionData() : false,
        ];
    }

    protected function callPaylineApiGetWebPaymentDetails($token)
    {
        $request = $this->requestGetWebPaymentDetailsFactory->create();
        $request
            ->setToken($token);

        return $this->paylineApiClient->callGetWebPaymentDetails($request);
    }
    
    protected function callPaylineApiDoCapture(
        TransactionInterface $authorizationTransaction,
        array $paymentData
    )
    {
        $request = $this->requestDoCaptureFactory->create();
        $request
            ->setAuthorizationTransaction($authorizationTransaction)
            ->setPaymentData($paymentData);
        
        return $this->paylineApiClient->callDoCapture($request);
    }
    
    public function handlePaymentGatewayNotifyByToken($token)
    {
        $response = $this->callPaylineApiGetWebPaymentDetails($token);
        
        $order = $this->paylineOrderManagement->getOrderByToken($token);
        
        if($response->isSuccess()) {
            $this->handlePaymentGatewayNotifySuccess($response, $order);
        } else {
            $message = $response->getResultCode() . ' : ' . $response->getShortErrorMessage();
            
            if($response->isWaitingAcceptance()) {
                $this->handlePaymentGatewayNotifyWaitingAcceptance($order, $message);
            } elseif($response->isCanceled()) {
                $this->handlePaymentGatewayNotifyCanceled($order, $message);
            } elseif($response->isAbandoned()) {
                $this->handlePaymentGatewayNotifyAbandoned($order, $message);
            } elseif($response->isFraud()) {
                $this->handlePaymentGatewayNotifyFraud($order, $message);
            } else {
                $this->handlePaymentGatewayNotifyRefused($order, $message);
            }
        }
        
        $order->save();
        
        return $this;
    }
    
    protected function handlePaymentGatewayNotifySuccess(
        ResponseGetWebPaymentDetailsFactory $response, 
        Order $order
    )
    {
        $transactionData = $response->getTransactionData();
        $paymentData = $response->getPaymentData();
        
        $orderPayment = $order->getPayment();
        $orderPayment->setTransactionId($transactionData['id']);
        
        // TODO Add controls to avoid double authorization/capture
        if($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION) {
            $orderPayment->setIsTransactionClosed(false);
            $orderPayment->authorize(false, $paymentData['amount'] / 100);
            $this->paylineOrderManagement->handleSetOrderStateStatus(
                $order, null, HelperConstants::ORDER_STATUS_PAYLINE_WAITING_CAPTURE
            );
        } elseif($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE) {
            $orderPayment->getMethodInstance()->setSkipCapture(true);
            $orderPayment->capture();
            $this->paylineOrderManagement->handleSetOrderStateStatus(
                $order, null, HelperConstants::ORDER_STATUS_PAYLINE_CAPTURED
            );
        }
    }
    
    protected function handlePaymentGatewayNotifyFraud(Order $order, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $order, Order::STATE_PROCESSING, HelperConstants::ORDER_STATUS_PAYLINE_FRAUD, $message
        );
    }
    
    protected function handlePaymentGatewayNotifyWaitingAcceptance(Order $order, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $order, Order::STATE_PROCESSING, HelperConstants::ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE, $message
        );
    }
    
    protected function handlePaymentGatewayNotifyAbandoned(Order $order, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $order, Order::STATE_CANCELED, HelperConstants::ORDER_STATUS_PAYLINE_ABANDONED, $message
        );
    }
    
    protected function handlePaymentGatewayNotifyRefused(Order $order, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $order, Order::STATE_CANCELED, HelperConstants::ORDER_STATUS_PAYLINE_REFUSED, $message
        );
    }
    
    protected function handlePaymentGatewayNotifyCanceled(Order $order, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $order, Order::STATE_CANCELED, HelperConstants::ORDER_STATUS_PAYLINE_CANCELED, $message
        );
    }
    
    public function handlePaymentGatewayCancelByToken($token)
    {
        $order = $this->paylineOrderManagement->getOrderByToken($token);
        
        $this->handlePaymentGatewayNotifyCanceled($order);
        
        $order->save();
        $this->paylineCartManagement->restoreCartFromOrder($order);
        
        return $this;
    }
    
    public function callPaylineApiDoCaptureFacade(
        OrderInterface $order,
        OrderPaymentInterface $payment, 
        $amount
    )
    {
        $token = $this->orderIncrementIdTokenFactory->create()->getTokenByOrderIncrementId($order->getIncrementId());
        $response1 = $this->callPaylineApiGetWebPaymentDetails($token);

        if(!$response1->isSuccess()) {
            // TODO log
            throw new \Exception($response1->getShortErrorMessage());
        }

        $paymentData = $response1->getPaymentData();
        $paymentData['amount'] = round($amount * 100, 0);

        $authorizationTransaction = $this->transactionRepository->getByTransactionType(
            Transaction::TYPE_AUTH,
            $payment->getId(),
            $payment->getParentId()
        );

        $response2 = $this->callPaylineApiDoCapture($authorizationTransaction, $paymentData);

        if(!$response2->isSuccess()) {
            // TODO log
            throw new \Exception($response2->getShortErrorMessage());
        }

        $payment->setTransactionId($response2->getTransactionId());

        return $this;
    }

    public function applyPaymentReturnStrategyFromToken($token)
    {
        $response = $this->callPaylineApiGetWebPaymentDetails($token);

        if($response->isSuccess()) {
            $this->paylineCartManagement->placeOrderByToken($token);
        } else {
            $this->paylineCartManagement->handleReserveCartOrderIdFacade(
                $this->paylineCartManagement->getCartByToken($token)->getId(),
                $token,
                true
            );
            throw new \Exception('Payment has been in error.');
        }

        return $this;
    }
}
