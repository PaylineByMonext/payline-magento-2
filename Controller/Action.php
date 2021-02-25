<?php

namespace Monext\Payline\Controller;

use Magento\Framework\App\Action\Action as BaseAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Element\Template;

abstract class Action extends BaseAction
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $loggerPayline;

    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $loggerPayline
    )
    {
        parent::__construct($context);
        $this->loggerPayline = $loggerPayline;
    }

    protected function getToken()
    {
        $token = $this->getRequest()->getParam('paylinetoken');

        if (empty($token)) {
            $token = $this->getRequest()->getParam('token');
        }

        return $token;
    }

    protected function getRedirect($success)
    {
        if ($success) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/onepage/success');
        } else {
            $layout = $this->_view->getLayout();
            $pageErrorHtml = $layout->createBlock(Template::class, 'gateway.error')->setTemplate('Monext_Payline::gateway/error.phtml')->toHtml();
            /** @var \Magento\Framework\Controller\Result\Raw $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $resultRedirect->setHttpResponseCode(200);
            $resultRedirect->setContents($pageErrorHtml);
        }
        return $resultRedirect;
    }

}
