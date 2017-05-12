<?php

namespace Monext\Payline\PaylineApi;

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
    abstract public function getData();

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