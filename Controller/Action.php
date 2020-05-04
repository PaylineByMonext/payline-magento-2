<?php

namespace Monext\Payline\Controller;

use Magento\Framework\App\Action\Action as BaseAction;
use Magento\Framework\App\Action\Context;

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
}
