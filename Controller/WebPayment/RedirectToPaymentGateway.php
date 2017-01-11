<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;

class RedirectToPaymentGateway extends Action
{
    /**
     * @var OrderPaymentRepositoryInterface
     */
    protected $orderPaymentRepository;
    
    /**
     * @var ResultRedirectFactory 
     */
    protected $resultRedirectFactory;
    
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    
    public function __construct(
        Context $context,
        ResultRedirectFactory $resultRedirectFactory,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        CheckoutSession $checkoutSession
    )
    {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function execute() 
    {
        $orderPayment = $this->orderPaymentRepository->get($this->checkoutSession->getLastOrderId());
        $additionalInformation = $orderPayment->getAdditionalInformation();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        // TODO Handle case if data is not present
        $resultRedirect->setUrl($additionalInformation['do_web_payment_response_data']['redirect_url']);
        return $resultRedirect;
    }
}

