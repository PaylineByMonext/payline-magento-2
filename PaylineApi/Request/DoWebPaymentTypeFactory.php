<?php

namespace Monext\Payline\PaylineApi\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Monext\Payline\PaylineApi\Request\DoWebPaymentType\AbstractDoWebPaymentType;

class DoWebPaymentTypeFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AbstractDoWebPaymentType[]
     */
    protected $availableDoWebPaymentTypeClass;


    /**
     * DoWebPaymentTypeFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param array $availableDoWebPaymentTypeClass
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $availableDoWebPaymentTypeClass = array()
    )
    {
        $this->objectManager = $objectManager;
        $this->availableDoWebPaymentTypeClass = $availableDoWebPaymentTypeClass;
    }

    /**
     * @param PaymentInterface $payment
     * @return AbstractDoWebPaymentType
     * @throws \Exception
     */
    public function create(PaymentInterface $payment)
    {
        return $this->objectManager->create($this->getDoWebPaymentTypeClass($payment->getMethod()), ['payment' => $payment]);
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
