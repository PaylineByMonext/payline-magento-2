<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;

class GetMerchantSettings extends AbstractResponse
{
    public function getContractsData()
    {
        $result = array();
        
        foreach($this->data['listPointOfSell']['pointOfSell'] as $pointOfSell) {
            if (is_object($pointOfSell)) {
                $contractsList    = $pointOfSell->contracts->contract;
                $pointOfSellLabel = $pointOfSell->label;
            } else { //if only one point of sell, we parse an array
                if(!empty($pointOfSell['contracts'])) {
                    $contractsList = !empty($pointOfSell['contracts']['contract']) ? $pointOfSell['contracts']['contract'] : [];
                }
                $pointOfSellLabel = (!empty($pointOfSell['label'])) ? $pointOfSell['label']: '';
            }

            if (!is_array($contractsList)) {
                $contractsList = [$contractsList];
            }

            foreach ($contractsList as $contract) {
                $result[] = [
                    'label' => $contract['label'],
                    'number' => $contract['contractNumber'],
                    'card_type' => $contract['cardType'],
                    'currency' => $contract['currency'],
                    'point_of_sell_label' => $pointOfSellLabel,
                ];
            }
        }
        
        return $result;
    }
}