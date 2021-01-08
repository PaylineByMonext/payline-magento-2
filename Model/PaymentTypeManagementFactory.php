<?php

namespace Monext\Payline\Model;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Monext\Payline\Model\PaymentTypeManagement\AbstractPaymentTypeManagement;

class PaymentTypeManagementFactory
{
    /**
     * @var AbstractPaymentTypeManagement[]
     */
    protected $availablePaymentTypeManagementClass;

    /**
     * DoWebPaymentTypeFactory constructor.
     * @param array $availablePaymentTypeManagementClass
     */
    public function __construct(
        $availablePaymentTypeManagementClass = array()
    )
    {
        $this->availablePaymentTypeManagementClass = $availablePaymentTypeManagementClass;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return AbstractPaymentTypeManagement
     * @throws \Exception
     */
    public function create(OrderPaymentInterface $payment)
    {
        return $this->getPaymentTypeManagementClass($payment->getMethod());
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
