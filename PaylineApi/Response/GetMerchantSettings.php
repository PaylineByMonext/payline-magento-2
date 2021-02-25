<?php

namespace Monext\Payline\PaylineApi\Response;

use Monext\Payline\PaylineApi\AbstractResponse;

class GetMerchantSettings extends AbstractResponse
{
    public function getContractsData()
    {
        $result = [];
        if (empty($this->data['listPointOfSell']['pointOfSell']) || !is_array($this->data['listPointOfSell']['pointOfSell'])) {
            return $result;
        }

        $allPointOfSell =  $this->data['listPointOfSell']['pointOfSell'];
        if (!empty($allPointOfSell['contracts']) && !empty($allPointOfSell['label'])) {
            $contractsList = !empty($allPointOfSell['contracts']['contract']) ? $allPointOfSell['contracts']['contract'] : [];
            $pointOfSellLabel = $allPointOfSell['label'];

            return $this->formatContractsList($contractsList, $pointOfSellLabel);
        } else {
            foreach ($this->data['listPointOfSell']['pointOfSell'] as $pointOfSell) {
                $contractsList = [];
                if (is_object($pointOfSell)) {
                    $contractsList    = $pointOfSell->contracts->contract;
                    $pointOfSellLabel = $pointOfSell->label;
                } else { //if only one point of sell, we parse an array
                    if (!empty($pointOfSell['contracts'])) {
                        $contractsList = !empty($pointOfSell['contracts']['contract']) ? $pointOfSell['contracts']['contract'] : [];
                    }
                    $pointOfSellLabel = (!empty($pointOfSell['label'])) ? $pointOfSell['label']: '';
                }


                

                $result = array_merge($result, $this->formatContractsList($contractsList, $pointOfSellLabel));
            }
        }

        return $result;
    }



    protected function formatContractsList(array $contractsList, $pointOfSellLabel)
    {
        $result = [];
        $contractsList = array_filter($contractsList);
        if(!empty($contractsList)) {
            $firstKey = key($contractsList);
            if(!is_numeric($firstKey) && isset($contractsList['contractNumber'])) {
                $contractsList = [$contractsList];
            }

            foreach ($contractsList as $contract) {
                $result[] = [
                    'label' => $contract['label'],
                    'number' => $contract['contractNumber'],
                    'card_type' => $contract['cardType'],
                    'currency' => isset($contract['currency']) ? $contract['currency'] : null,
                    'point_of_sell_label' => $pointOfSellLabel,
                ];
            }

        }

        return $result;
    }

}
