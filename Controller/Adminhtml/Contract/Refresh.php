<?php

namespace Monext\Payline\Controller\Adminhtml\Contract;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory as ResultRedirectFactory;
use Monext\Payline\Model\ContractManagement;

class Refresh extends AbstractAction
{
    /**
     * @var ContractManagement
     */
    protected $contractManagement;
    
    /**
     * @var ResultRedirectFactory
     */
    protected $resultRedirectFactory;
    
    public function __construct(
        Context $context,
        ContractManagement $contractManagement
    ) {
        parent::__construct($context);
        $this->contractManagement = $contractManagement;
    }

    public function execute()
    {
        $this->contractManagement->refreshContracts();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
}
