<?php

namespace Monext\Payline\Controller;

use Magento\Framework\App\Action\Action as BaseAction;

abstract class Action extends BaseAction
{
    protected function getToken()
    {
        $token = $this->getRequest()->getParam('paylinetoken');
        
        if(empty($token)) {
            $token = $this->getRequest()->getParam('token');
        }
        
        return $token;
    }
}

