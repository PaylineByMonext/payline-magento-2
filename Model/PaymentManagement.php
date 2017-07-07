<?php

namespace Monext\Payline\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;

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
use Monext\Payline\Model\WalletManagement;
use Monext\Payline\PaylineApi\Client as PaylineApiClient;
use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;
use Monext\Payline\PaylineApi\Request\DoCaptureFactory as RequestDoCaptureFactory;
use Monext\Payline\PaylineApi\Request\DoVoidFactory as RequestDoVoidFactory;
use Monext\Payline\PaylineApi\Request\DoRefundFactory as RequestDoRefundFactory;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetailsFactory as RequestGetWebPaymentDetailsFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;
use Monolog\Logger as LoggerConstants;
use Psr\Log\LoggerInterface as Logger;

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
     * @var RequestDoVoidFactory
     */
    protected $requestDoVoidFactory;
    
    /**
     * @var RequestDoRefundFactory
     */
    protected $requestDoRefundFactory;
    
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
     * @var Logger
     */
    public $logger;
    
    /**
     * @var WalletManagement
     */
    protected $walletManagement;

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
        RequestDoVoidFactory $requestDoVoidFactory,
        RequestDoRefundFactory $requestDoRefundFactory,
        QuoteBillingAddressManagementInterface $quoteBillingAddressManagement,
        QuoteShippingAddressManagementInterface $quoteShippingAddressManagement,
        PaylineOrderManagement $paylineOrderManagement,
        Logger $logger,
        WalletManagement $walletManagement,
        HelperData $helperData,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder      
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
        $this->requestDoVoidFactory = $requestDoVoidFactory;
        $this->requestDoRefundFactory = $requestDoRefundFactory;
        $this->quoteBillingAddressManagement = $quoteBillingAddressManagement;
        $this->quoteShippingAddressManagement = $quoteShippingAddressManagement;
        $this->paylineOrderManagement = $paylineOrderManagement;
        $this->logger = $logger;
        $this->walletManagement = $walletManagement;
        $this->helperData = $helperData;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
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

    protected function callPaylineApiDoVoid(
        array $paymentData        
    )
    {
        $request = $this->requestDoVoidFactory->create();
        $request->setPaymentData($paymentData);
        
        return $this->paylineApiClient->callDoVoid($request);
    }
    
    protected function callPaylineApiDoRefund(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        array $paymentData        
    )
    {
        $request = $this->requestDoRefundFactory->create();
        $request
            ->setOrder($order)
            ->setPayment($payment)
            ->setPaymentData($paymentData);
        
        return $this->paylineApiClient->callDoRefund($request);
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

        if(!$this->isPaymentGatewayAmountSameAsOrderAmount($response, $payment)) {
            $message = __(
                'ERROR for order ; payment gateway amount %1 does not match order amount %2.',
                $response->getAmount(),
                $this->helperData->mapMagentoAmountToPaylineAmount($payment->getOrder()->getGrandTotal())
            );
            $payment->setAmountToCancel($response->getAmount());
            $this->handlePaymentCanceled($payment->setData('is_in_error', true), $message);
        } elseif($response->isSuccess()) {
            $this->handlePaymentSuccess($response, $payment);
            $this->walletManagement->handleWalletReturnFromPaymentGateway($response, $payment);
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
            $payment->authorize(false, $this->helperData->mapPaylineAmountToMagentoAmount($paymentData['amount']));
        } elseif($paymentData['action'] == PaylineApiConstants::PAYMENT_ACTION_AUTHORIZATION_CAPTURE) {
            $payment->getMethodInstance()->setSkipCapture(true);
            $payment->capture();
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
        $paymentData['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($amount);

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

    public function callPaylineApiDoVoidFacade(
        OrderInterface $order,
        $payment
    )
    {        
        // Check existing transaction - else void impossible
        if(!$payment->getTransactionId()) {
            $this->logger->log(LoggerConstants::DEBUG, 'No transaction found for this order : '.$order->getId());
            throw new \Exception(__('No transaction found for this order.'));
        }

        // Get API token
        $token = $this->orderIncrementIdTokenFactory->create()->getTokenByOrderIncrementId($order->getIncrementId());
        $response1 = $this->callPaylineApiGetWebPaymentDetails($token);

        if(!$response1->isSuccess()) {
            $this->logger->log(LoggerConstants::DEBUG, 'No payment details found : '.$response1->getLongErrorMessage());
            throw new \Exception($response1->getShortErrorMessage());
        }

        $paymentData = $response1->getPaymentData();
        $paymentData['transactionID'] = $payment->getTransactionId();
        $paymentData['comment'] = __('Transaction %s canceled for order %s from Magento Back-Office',
            $payment->getTransactionId(),
            $order->getRealOrderId())->render();

        // Call API
        $response2 = $this->callPaylineApiDoVoid($paymentData);

        if(!$response2->isSuccess()) {
            $this->logger->log(LoggerConstants::DEBUG, 'DoVoid error : '.$response2->getLongErrorMessage());
            throw new \Exception($response2->getShortErrorMessage());
        }

        return $this;    
    }
    
    public function callPaylineApiDoRefundFacade(
        OrderInterface $order,
        $payment, 
        $amount
    )
    {
        // Get first transaction used - Always use it for refund
       $filters[] = $this->filterBuilder
            ->setField(TransactionInterface::ORDER_ID)
            ->setValue($order->getId())
            ->create();
        $createdAtSort = $this->sortOrderBuilder
            ->setField('created_at')
            ->setDirection(Collection::SORT_ORDER_ASC)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->addSortOrder($createdAtSort)    
            ->create();
            
        $transaction = $this->transactionRepository->getList($searchCriteria)->getFirstItem();//$this->transactionRepository->getList($searchCriteria)->getItems();
        
        // Check existing transaction - else refund impossible
        if(!$transaction || ($transaction && !trim($transaction->getTxnId()))) {
            $this->logger->log(LoggerConstants::DEBUG, 'No transaction found for this order : '.$order->getId());
            throw new \Exception(__('No transaction found for this order.'));
        }

        // Get API token
        $token = $this->orderIncrementIdTokenFactory->create()->getTokenByOrderIncrementId($order->getIncrementId());
        $response1 = $this->callPaylineApiGetWebPaymentDetails($token);

        if(!$response1->isSuccess()) {
            $this->logger->log(LoggerConstants::DEBUG, 'No payment details found : '.$response1->getLongErrorMessage());
            throw new \Exception($response1->getShortErrorMessage());
        }

        $paymentData = $response1->getPaymentData();
        $paymentData['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($amount);
        $paymentData['transactionID'] = $transaction->getTxnId();
        $paymentData['comment'] = __('Transaction %s refunded for order %s from Magento Back-Office',
            $payment->getTransactionId(),
            $order->getRealOrderId())->render();

        // Call API
        $response2 = $this->callPaylineApiDoRefund($order, $payment, $paymentData);

        if(!$response2->isSuccess()) {
            $this->logger->log(LoggerConstants::DEBUG, 'DoRefund error : '.$response2->getLongErrorMessage());
            throw new \Exception($response2->getShortErrorMessage());
        }

        $payment->setTransactionId($response2->getTransactionId());
        $payment->setParentTransactionId($transaction->getTxnId());
        
        return $this;
    }

    protected function isPaymentGatewayAmountSameAsOrderAmount(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    )
    {
        $orderAmount = $this->helperData->mapMagentoAmountToPaylineAmount($payment->getOrder()->getGrandTotal());
        $responseAmount = $response->getAmount();

        return $responseAmount == $orderAmount;
    }
}
