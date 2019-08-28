<?php

namespace Monext\Payline\PaylineApi;

use Payline\PaylineSDK;

/**
 * Factory class for @see \Payline\PaylineSDK
 */
class PaylineSDKFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Payline\\PaylineSDK')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Payline\PaylineSDK
     */
    public function create(array $data = array())
    {
        return new PaylineSDK(
            $data['merchant_id'],
            $data['access_key'],
            $data['proxy_host'],
            $data['proxy_port'],
            $data['proxy_login'],
            $data['proxy_password'],
            $data['environment'],
            $data['pathLog'],
            $data['logLevel']
        );
    }
}
