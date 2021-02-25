<?php

namespace Monext\Payline\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Data\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Checkout\Api\PaymentInformationManagementInterface as CheckoutPaymentInformationManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
use Monext\Payline\PaylineApi\Request\DoVoidFactory as RequestDoVoidFactory;
use Monext\Payline\PaylineApi\Request\DoRefundFactory as RequestDoRefundFactory;
use Monext\Payline\PaylineApi\Request\DoWebPaymentFactory as RequestDoWebPaymentFactory;
use Monext\Payline\PaylineApi\Request\GetWebPaymentDetailsFactory as RequestGetWebPaymentDetailsFactory;
use Monext\Payline\PaylineApi\Response\GetWebPaymentDetails as ResponseGetWebPaymentDetails;
use Monext\Payline\PaylineApi\Request\GetPaymentRecordFactory as PaymentRecordRequestFactory;
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

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Logger
     */
    public $paylineLogger;

    /**
     * @var PaymentTypeManagementFactory
     */
    protected $paymentTypeManagementFactory;

    /**
     * @var PaymentRecordRequestFactory
     */
    protected $paymentRecordRequestFactory;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface
     */
    protected $transactionManager;

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
        \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface $transactionManager,
        PaylineOrderManagement $paylineOrderManagement,
        Logger $logger,
        WalletManagement $walletManagement,
        HelperData $helperData,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        ScopeConfigInterface $scopeConfig,
        PaymentTypeManagementFactory $paymentTypeManagementFactory,
        PaymentRecordRequestFactory $paymentRecordRequestFactory,
        Logger $paylineLogger
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
        $this->scopeConfig = $scopeConfig;
        $this->paylineLogger = $paylineLogger;
        $this->paymentTypeManagementFactory = $paymentTypeManagementFactory;
        $this->paymentRecordRequestFactory = $paymentRecordRequestFactory;
        $this->transactionManager = $transactionManager;
    }

    public function saveCheckoutPaymentInformationFacade(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    )
    {
        $this->checkoutPaymentInformationManagement->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        return $this->wrapCallPaylineApiDoWebPaymentFacade($cartId);
    }

    public function wrapCallPaylineApiDoWebPaymentFacade($cartId)
    {
        $cart = $this->cartRepository->getActive($cartId);
        $response = $this->callPaylineApiDoWebPaymentFacade(
            $cart,
            $this->paylineCartManagement->getProductCollectionFromCart($cartId),
            $this->cartTotalRepository->get($cartId),
            $this->quotePaymentMethodManagement->get($cartId),
            $this->quoteBillingAddressManagement->get($cartId),
            $cart->getIsVirtual() ? null : $this->quoteShippingAddressManagement->get($cartId)
        );

        return [
            'token' => $response->getToken(),
            'redirect_url' => $response->getRedirectUrl(),
        ];
    }

    protected function callPaylineApiDoWebPaymentFacade(
        CartInterface $cart,
        ProductCollection $productCollection,
        TotalsInterface $totals,
        PaymentInterface $payment,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    )
    {
        $logData = [
            'cart_id' => $cart->getId(),
            'grand_total' => $totals->getGrandTotal(),
            'shipping_amount' => $totals->getShippingInclTax(),
            'discount_amount' => $totals->getDiscountAmount(),
        ];
        $this->paylineLogger->debug(__METHOD__, $logData);

        $this->paylineCartManagement->handleReserveCartOrderId($cart->getId());

        $this->paylineLogger->debug(__METHOD__, ['reserved_order_id' => $cart->getReservedOrderId()]);

        if ($cart->getIsVirtual()) {
            $shippingAddress = null;
        }

        $response = $this->callPaylineApiDoWebPayment($cart, $productCollection, $totals, $payment, $billingAddress, $shippingAddress);

        if (!$response->isSuccess()) {
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
        ProductCollection $productCollection,
        TotalsInterface $totals,
        PaymentInterface $payment,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    )
    {
        $request = $this->requestDoWebPaymentFactory->create();
        $request
            ->setCart($cart)
            ->setProductCollection($productCollection)
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

    protected function callPaylinePaymentRecord($contractNumber, $paymentRecordId)
    {
        $request = $this->paymentRecordRequestFactory->create();
        $request->setContractNumber($contractNumber)
            ->setPaymentRecordId($paymentRecordId);

        return $this->paylineApiClient->callGetPaymentRecord($request);
    }

    public function synchronizePaymentWithPaymentGatewayFacade($token, $restoreCartOnError = false)
    {
        $order = $this->paylineOrderManagement->getOrderByToken($token);

        if (!$order->getId()) {
            $this->paylineCartManagement->placeOrderByToken($token);
            $order = $this->paylineOrderManagement->getOrderByToken($token);
        }
        // IN CASE PAYMENT METHOD IS NOT PAYLINE WE EXIT
        $this->paylineOrderManagement->checkOrderPaymentFromPayline($order);

        $logData = [
            'token' => $token,
            'order_id' => $order->getId(),
            'grand_total' => $order->getGrandTotal(),
            'shipping_amount' => $order->getShippingInclTax(),
            'discount_amount' => $order->getDiscountAmount(),
        ];
        $this->paylineLogger->debug(__METHOD__, $logData);

        $this->synchronizePaymentWithPaymentGateway($order->getPayment(), $token);

        if ($order->getPayment()->getData('payline_in_error')) {
            if ($restoreCartOnError) {
                $this->paylineCartManagement->restoreCartFromOrder($order);
            }

            throw new \Exception(__($order->getPayment()->getData('payline_response')->getLongErrorMessage() ?? $order->getPayment()->getData('payline_error_message') ?: 'Payment is in error.'));
        }

        return $this;
    }

    protected function synchronizePaymentWithPaymentGateway(OrderPayment $payment, $token)
    {
        $response = $this->callPaylineApiGetWebPaymentDetails($token);
        $payment->setData('payline_response', $response);
        $paymentTypeManagement = $this->paymentTypeManagementFactory->create($payment);
        if ($response->isSuccess()) {
            if ($paymentTypeManagement->validate($response, $payment)) {
                $this->handlePaymentSuccessFacade($response, $payment);
            }
        } else {
            $message = $response->getResultCode() . ' : ' . $response->getShortErrorMessage();

            if ($response->isWaitingAcceptance()) {
                if ($paymentTypeManagement->validate($response, $payment)) {
                    $this->handlePaymentWaitingAcceptance($payment, $message);
                }
            } elseif ($response->isCanceled()) {
                $this->flagPaymentAsInError($payment, $message);
                $paymentTypeManagement->handlePaymentCanceled($payment);
            } elseif ($response->isAbandoned()) {
                $this->flagPaymentAsInError($payment, $message);
                $this->handlePaymentAbandoned($payment);
            } elseif ($response->isFraud()) {
                $this->flagPaymentAsInError($payment, $message);
                $this->handlePaymentFraud($payment);
            } else {
                $this->flagPaymentAsInError($payment, $message);
                $this->handlePaymentRefused($payment);
            }
        }

        $payment->getOrder()->save();

        return $this;
    }

    protected function handlePaymentSuccessFacade(
        ResponseGetWebPaymentDetails $response,
        OrderPayment $payment
    )
    {
        $paymentTypeManagement = $this->paymentTypeManagementFactory->create($payment);
        $paymentTypeManagement->handlePaymentSuccess($response, $payment);
        $this->walletManagement->handleWalletReturnFromPaymentGateway($response, $payment);
        $this->paylineOrderManagement->sendNewOrderEmail($payment->getOrder());

        return $this;
    }

    protected function handlePaymentFraud(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(),
            Order::STATE_PROCESSING,
            HelperConstants::ORDER_STATUS_PAYLINE_FRAUD,
            $message ?? $payment->getData('payline_error_message')
        );
    }

    protected function handlePaymentWaitingAcceptance(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(),
            Order::STATE_PROCESSING,
            HelperConstants::ORDER_STATUS_PAYLINE_WAITING_ACCEPTANCE,
            $message ?? $payment->getData('payline_error_message')
        );
    }

    protected function handlePaymentAbandoned(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(),
            Order::STATE_CANCELED,
            HelperConstants::ORDER_STATUS_PAYLINE_ABANDONED,
            $message ?? $payment->getData('payline_error_message')
        );
    }

    protected function handlePaymentRefused(OrderPayment $payment, $message = null)
    {
        $this->paylineOrderManagement->handleSetOrderStateStatus(
            $payment->getOrder(),
            Order::STATE_CANCELED,
            HelperConstants::ORDER_STATUS_PAYLINE_REFUSED,
            $message ?? $payment->getData('payline_error_message')
        );
    }

    /**
     * @param OrderInterface $order
     * @param OrderPaymentInterface $payment
     * @param $amount
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    public function callPaylineApiDoCaptureFacade(
        OrderInterface $order,
        OrderPaymentInterface $payment,
        $amount
    )
    {
        $token = $this->orderIncrementIdTokenFactory->create()->getTokenByOrderIncrementId($order->getIncrementId());
        $response1 = $this->callPaylineApiGetWebPaymentDetails($token);

        if (!$response1->isSuccess()) {
            // TODO log
            throw new \Exception($response1->getShortErrorMessage());
        }

        $paymentData = $response1->getPaymentData();
        $paymentData['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($amount);

        $authorizationTransaction = $this->transactionRepository->getByTransactionType(
            Transaction::TYPE_AUTH,
            $payment->getId()
        );

        if (!$authorizationTransaction) {
            // TODO log
            throw new \Exception(__('No authorization transaction found for this order.'));
        }

        $response2 = $this->callPaylineApiDoCapture($authorizationTransaction, $paymentData);

        if (!$response2->isSuccess()) {
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
        if (!$payment->getTransactionId()) {
            $this->logger->log(LoggerConstants::ERROR, 'No transaction found for this order : ' . $order->getId());
            throw new \Exception(__('No transaction found for this order.'));
        }

        // Get API token
        $token = $this->orderIncrementIdTokenFactory->create()->getTokenByOrderIncrementId($order->getIncrementId());
        $response1 = $this->callPaylineApiGetWebPaymentDetails($token);

        if (!$response1->isSuccess()) {
            $this->logger->log(LoggerConstants::ERROR, 'No payment details found : ' . $response1->getLongErrorMessage());
            throw new \Exception($response1->getShortErrorMessage());
        }

        $paymentData = $response1->getPaymentData();
        $paymentData['transactionID'] = $payment->getTransactionId();
        $paymentData['comment'] = __(
            'Transaction %s canceled for order %s from Magento Back-Office',
            $payment->getTransactionId(),
            $order->getRealOrderId()
        )->render();

        // Call API
        $response2 = $this->callPaylineApiDoVoid($paymentData);

        if (!$response2->isSuccess()) {
            $this->logger->log(LoggerConstants::ERROR, 'DoVoid error : ' . $response2->getLongErrorMessage());
            throw new \Exception($response2->getShortErrorMessage());
        }

        return $this;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @param $paymentRecordId
     * @return $this
     * @throws \Exception
     */
    public function callPaylinePaymentRecordFacade(OrderPaymentInterface $payment, $paymentRecordId)
    {
        if ($payment->getOrder()->hasData('save_mode')) {
            $this->paylineLogger->debug('BO Ship Mode');
        } else {
            $this->paylineLogger->debug('NotifyCyclingPaymentFromPaymentGateway Mode');
        }

        $response = $this->callPaylinePaymentRecord($payment->getAdditionalInformation('contract_number'), $paymentRecordId);
        $orderIsUpdated = false;
        if (!$response->isSuccess()) {
            $this->logger->log(LoggerConstants::ERROR, 'PaymentRecord error : ' . $response->getLongErrorMessage());
            throw new \Exception($response->getShortErrorMessage());
        }

        $nbTxnSuccess = 0;
        foreach ($response->getBillingRecords() as $record) {
            if (in_array($record['status'], PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_CYCLING_SUCCESS)) {
                ++$nbTxnSuccess;
                if ($this->checkIfTransactionExists($record['transaction']['id'], $payment) === false) {
                    $payment->setTransactionId($record['transaction']['id']);
                    $payment->setParentTransactionId($payment->getLastTransId());
                    $payment->setTransactionAdditionalInfo('payline_record', $record['transaction']);
                    $payment->registerCaptureNotification($this->helperData->mapPaylineAmountToMagentoAmount($record['amount']), true);
                    $orderIsUpdated = true;
                }
            } elseif (in_array($record['status'], PaylineApiConstants::PAYMENT_BACK_CODES_RETURN_CYCLING_ERROR)) {
                $payment->getOrder()->addStatusHistoryComment(__('Error code %1 => %2', $record['result']['code'], $record['result']['longMessage']), false);
                $orderIsUpdated = true;
            }
        }

        $this->paylineLogger->debug('Count billing records : ' . count($response->getBillingRecords()));
        $this->paylineLogger->debug('Nb records Sucess : ' . $nbTxnSuccess);
        if (count($response->getBillingRecords()) === $nbTxnSuccess) {
            $switchStatus = false;
            if (
                $payment->getOrder()->getState() === Order::STATE_COMPLETE
                && $payment->getOrder()->getStatus() === HelperConstants::ORDER_STATUS_PAYLINE_CYCLE_PAYMENT_CAPTURE
            ) {
                $switchStatus = true;
            }
            $this->paylineLogger->debug('Switch Status : ' . $switchStatus);
            $payment->getOrder()->addStatusHistoryComment(__('All payment cycle received'), $switchStatus);
            $payment->getOrder()->setPaiementCompleted(true);
            $orderIsUpdated = true;
        } else {
            $payment->getOrder()->setPaiementCompleted(false);
        }

        $isPaymentCyclingCompleted = ($payment->getOrder()->getPaiementCompleted()) ? '1' : '0';
        $this->paylineLogger->debug('Paiement Cycling Completed : ' . $isPaymentCyclingCompleted);
        $this->paylineLogger->debug('Order status : ' . $payment->getOrder()->getStatus());
        $this->paylineLogger->debug('Order state : ' . $payment->getOrder()->getState());

        //save_mode => flag pour la livraison après ou pendant les échéances
        if ($orderIsUpdated === true && !$payment->getOrder()->hasData('save_mode')) {
            $payment->getOrder()->save();
        }

        return $this;
    }

    /**
     * @param $transactionId
     * @param OrderPayment $payment
     * @return bool
     */
    protected function checkIfTransactionExists($transactionId, OrderPayment $payment)
    {
        return $this->transactionManager->isTransactionExists(
            $transactionId,
            $payment->getId(),
            $payment->getOrder()->getId()
        );
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
        if (!$transaction || ($transaction && !trim($transaction->getTxnId()))) {
            $this->logger->log(LoggerConstants::ERROR, 'No transaction found for this order : ' . $order->getId());
            throw new \Exception(__('No transaction found for this order.'));
        }

        // Get API token
        $token = $this->orderIncrementIdTokenFactory->create()->getTokenByOrderIncrementId($order->getIncrementId());
        $response1 = $this->callPaylineApiGetWebPaymentDetails($token);

        if (!$response1->isSuccess()) {
            $this->logger->log(LoggerConstants::ERROR, 'No payment details found : ' . $response1->getLongErrorMessage());
            throw new \Exception($response1->getShortErrorMessage());
        }

        $paymentData = $response1->getPaymentData();
        $paymentData['amount'] = $this->helperData->mapMagentoAmountToPaylineAmount($amount);
        $paymentData['transactionID'] = $transaction->getTxnId();
        $paymentData['comment'] = __(
            'Transaction %s refunded for order %s from Magento Back-Office',
            $payment->getTransactionId(),
            $order->getRealOrderId()
        )->render();

        // Call API
        $response2 = $this->callPaylineApiDoRefund($order, $payment, $paymentData);

        if (!$response2->isSuccess()) {
            $this->logger->log(LoggerConstants::ERROR, 'DoRefund error : ' . $response2->getLongErrorMessage());
            throw new \Exception($response2->getShortErrorMessage());
        }

        $payment->setTransactionId($response2->getTransactionId());
        $payment->setParentTransactionId($transaction->getTxnId());

        return $this;
    }

    protected function flagPaymentAsInError(OrderPayment $payment, $message = null)
    {

        $payment->setData('payline_in_error', true);
        $payment->setData('payline_error_message', $message);
        return $payment;
    }
}
