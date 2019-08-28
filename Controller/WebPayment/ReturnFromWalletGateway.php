<?php

namespace Monext\Payline\Controller\WebPayment;

use Magento\Framework\App\Action\Context;
use Monext\Payline\Controller\Action;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;

class ReturnFromWalletGateway extends Action
{
    /**
     * @var ResultRawFactory
     */
    protected $resultRawFactory;
    
    public function __construct(
        Context $context,
        ResultRawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
    }
    
    public function execute()
    {
        // TODO This is not supposed to be called because wallet is handled by widget
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents('OK');
        return $resultRaw;
    }
}
