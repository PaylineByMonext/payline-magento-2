<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Monext\Payline\Controller\Action;

class RedirectToPaymentGateway extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession
    )
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function execute() 
    {
        $order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        // TODO Handle case if data is not present
        $resultRedirect->setUrl($additionalInformation['do_web_payment_response_data']['redirect_url']);
        return $resultRedirect;
    }
}

