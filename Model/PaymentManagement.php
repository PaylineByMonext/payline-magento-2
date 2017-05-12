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
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Payment\Transaction;
//use Magento\Sales\Api\TransactionRepositoryInterface; Cannot use TransactionRepositoryInterface because needed methods are not exposed in
use Magento\Sales\Model\Order\Payment\Transaction\Repository as TransactionRepository;
use Monext\Payline\Api\PaymentManagementInterface as PaylinePaymentManagementInterface;
use Monext\Payline\Helper\Constants as HelperConstants;
use Monext\Payline\Helper\Data as HelperData;
use Monext\Payline\Model\CartManagement as PaylineCartManagement;
use Monext\Payline\Model\OrderIncrementIdTokenFactory as OrderIncrementIdTokenFactory;
use Monext\Payline\Model\OrderManagement as PaylineOrderManagement;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Request\DoCaptureFactory as RequestDoCaptureFactory;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetailsFactory as RequestGetWebPaymentDetailsFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;

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

    /**
     * @var HelperData
     */
    protected $helperData;

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
        PaylineOrderManagement $paylineOrderManagement,
        HelperData $helperData
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
        $this->helperData = $helperData;
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

    public function synchronizePaymentWithPaymentGatewayFacade($token, $restoreCartOnError = false)
    {
        $order = $this->paylineOrderManagement->getOrderByToken($token);

        if(!$order->getId()) {
            $this->paylineCartManagement->placeOrderByToken($token);
            $order = $this->paylineOrderManagement->getOrderByToken($token);
        }

        $this->synchronizePaymentWithPaymentGateway($order->getPayment(), $token);

        if($order->getPayment()->getData('is_in_error')) {
            if($restoreCartOnError) {
                $this->paylineCartManagement->restoreCartFromOrder($order);
            }

            throw new \Exception(__('Payment is in error.'));
        }

        return $this;
    }

    protected function synchronizePaymentWithPaymentGateway(OrderPayment $payment, $token)
    {
        $response = $this->callPaylineApiGetWebPaymentDetails($token);

        if($response->isSuccess()) {
            $this->handlePaymentSuccess($response, $payment);
            $this->handleWalletReturn($response, $payment);
        } else {
            $message = $response->getResultCode() . ' : ' . $response->getShortErrorMessage();

            if($response->isWaitingAcceptance()) {
                $this->handlePaymentWaitingAcceptance($payment, $message);
            } elseif($response->isCanceled()) {
                $this->handlePaymentCanceled($payment->setData('is_in_error', true), $message);
            } elseif($response->isAbandoned()) {
                $this->handlePaymentAbandoned($payment->setData('is_in_error', true), $message);
            } elseif($response->isFraud()) {
                $this->handlePaymentFraud($payment->setData('is_in_error', true), $message);
            } else {
                $this->handlePaymentRefused($payment->setData('is_in_error', true), $message);
            }
        }

        $payment->getOrder()->save();

        return $this;
    }

    protected function handlePaymentSuccess(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    )
    {
        $transactionData = $response->getTransactionData();
        $paymentData = $response->getPaymentData();

        $payment->setTransactionId($transactionData['id']);

        // TODO Add controls to avoid double authorization/capture
        if($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION) {
            $payment->setIsTransactionClosed(false);
            $payment->authorize(false, $paymentData['amount'] / 100);
        } elseif($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE) {
            $payment->getMethodInstance()->setSkipCapture(true);
            $payment->capture();
        }
    }

    protected function handleWalletReturn(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    )
    {
        $paymentMethod = $payment->getMethod();
        $walletData = $response->getWalletData();

        if($this->helperData->isWalletEnabled($paymentMethod) && $walletData && isset($walletData['walletId'])) {
            $payment->setAdditionalInformation('wallet_id', $walletData['walletId']);
        }
    }
    
    protected function handlePaymentFraud(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(), Order::STATE_PROCESSING, HelperConstants::ORDER_STATUS_PAYLINE_FRAUD, $message
        );
    }
    
    protected function handlePaymentWaitingAcceptance(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(), Order::STATE_PROCESSING, HelperConstants::ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE, $message
        );
    }
    
    protected function handlePaymentAbandoned(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(), Order::STATE_CANCELED, HelperConstants::ORDER_STATUS_PAYLINE_ABANDONED, $message
        );
    }
    
    protected function handlePaymentRefused(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(), Order::STATE_CANCELED, HelperConstants::ORDER_STATUS_PAYLINE_REFUSED, $message
        );
    }
    
    protected function handlePaymentCanceled(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(), Order::STATE_CANCELED, HelperConstants::ORDER_STATUS_PAYLINE_CANCELED, $message
        );
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

        if(!$authorizationTransaction) {
            // TODO log
            throw new \Exception(__('No authorization transaction found for this order.'));
        }

        $response2 = $this->callPaylineApiDoCapture($authorizationTransaction, $paymentData);

        if(!$response2->isSuccess()) {
            // TODO log
            throw new \Exception($response2->getShortErrorMessage());
        }

        $payment->setTransactionId($response2->getTransactionId());

        return $this;
    }
}
