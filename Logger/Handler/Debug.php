<?php

namespace Monext\Payline\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Debug extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/payline.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
