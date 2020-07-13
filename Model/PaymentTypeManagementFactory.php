<?php

namespace Monext\Payline\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Monext\Payline\Model\PaymentTypeManagement\AbstractPaymentTypeManagement;

class PaymentTypeManagementFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AbstractPaymentTypeManagement[]
     */
    protected $availablePaymentTypeManagementClass;


    /**
     * DoWebPaymentTypeFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param array $availablePaymentTypeManagementClass
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $availablePaymentTypeManagementClass = array()
    )
    {
        $this->objectManager = $objectManager;
        $this->availablePaymentTypeManagementClass = $availablePaymentTypeManagementClass;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return AbstractPaymentTypeManagement
     * @throws \Exception
     */
    public function create(OrderPaymentInterface $payment)
    {
        return $this->objectManager->create($this->getPaymentTypeManagementClass($payment->getMethod()));
    }

    /**
     * @param $paymentMethod
     * @return mixed
     * @throws \Exception
     */
    protected function getPaymentTypeManagementClass($paymentMethod)
    {
        if(!isset($this->availablePaymentTypeManagementClass[$paymentMethod])) {
            throw new \Exception(__(__METHOD__ . ' : Payment method %1 not available in %2', $paymentMethod, implode(', ', array_keys($this->availablePaymentTypeManagementClass))));
        }
        return $this->availablePaymentTypeManagementClass[$paymentMethod];
    }
}
