<?php

namespace Monext\Payline\Plugin\Observer\Quote;

class PreventAutoSendOrderEmailPlugin
{
    protected $helperData;

    public function __construct(
        \Monext\Payline\Helper\Data $helperData
    )
    {
        $this->helperData = $helperData;
    }

    public function aroundExecute(
        \Magento\Quote\Observer\Webapi\SubmitObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    )
    {
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($this->helperData->isPaymentFromPayline($order->getPayment())) {
            $order->setCanSendNewEmailFlag(false);
        }

        return $proceed($observer);
    }
}