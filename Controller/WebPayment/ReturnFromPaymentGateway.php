<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Monext\Payline\Controller\Action;

class ReturnFromPaymentGateway extends Action
{
    /**
     * @var ResultRedirectFactory 
     */
    protected $resultRedirectFactory;
    
    public function __construct(
        Context $context,
        ResultRedirectFactory $resultRedirectFactory
    )
    {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
    }
    
    public function execute() 
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/onepage/success');
        return $resultRedirect;
    }
}

