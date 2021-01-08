<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Quote\Api\Data\PaymentInterface;
use Monext\Payline\PaylineApi\Request\DoWebPaymentType\AbstractDoWebPaymentType;

class DoWebPaymentTypeFactory
{
    /**
     * @var AbstractDoWebPaymentType[]
     */
    protected $availableDoWebPaymentTypeClass;

    /**
     * DoWebPaymentTypeFactory constructor.
     * @param array $availableDoWebPaymentTypeClass
     */
    public function __construct(
        $availableDoWebPaymentTypeClass = array()
    )
    {
        $this->availableDoWebPaymentTypeClass = $availableDoWebPaymentTypeClass;
    }

    /**
     * @param PaymentInterface $payment
     * @return AbstractDoWebPaymentType
     * @throws \Exception
     */
    public function create(PaymentInterface $payment)
    {
        return $this->getDoWebPaymentTypeClass($payment->getMethod())->setPayment($payment);
    }

    /**
     * @param $paymentMethod
     * @return mixed
     * @throws \Exception
     */
    protected function getDoWebPaymentTypeClass($paymentMethod)
    {
        if(!isset($this->availableDoWebPaymentTypeClass[$paymentMethod])) {
            throw new \Exception(__('Payment method %1 not available in %2', $paymentMethod, implode(', ', array_keys($this->availableDoWebPaymentTypeClass))));
        }
        return $this->availableDoWebPaymentTypeClass[$paymentMethod];
    }
}
