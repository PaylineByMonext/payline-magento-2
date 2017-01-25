<?php

namespace Monext\Payline\PaylineApi;

abstract class AbstractRequest
{
    /**
     * @return array
     */
    abstract public function getData();
    
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