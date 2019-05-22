<?php

namespace Monext\Payline\PaylineApi;

use Monext\Payline\PaylineApi\Constants as PaylineApiConstants;

abstract class AbstractRequest
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $privateData;

    /**
     * @return array
     */
    public function getData()
    {
        return array('version' => PaylineApiConstants::LASTEST_API_VERSION);
    }

    /**
     * @return array
     */
    public function getPrivateData()
    {
        return array();
    }

    /**
     * @param string $dateTime
     * @return string
     */
    protected function formatDateTime($dateTime, $format = 'd/m/Y H:i')
    {
        $date = new \DateTime($dateTime);
        return $date->format($format);
    }
}
