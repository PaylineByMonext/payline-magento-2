<?php

namespace Monext\Payline\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Language implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            //['value'=>'', 'label'=>__('Based on browser')],
            ['value'=>'', 'label'=>''],
            ['value' => 'fr', 'label'=>__('French')],
            ['value' => 'eng', 'label'=>__('English')],
            ['value' => 'spa', 'label'=>__('Spanish')],
            ['value' => 'pt', 'label'=>__('Portuguese')],
            ['value' => 'it', 'label'=>__('Italian')],
            ['value' => 'de', 'label'=>__('German')],
            ['value' => 'nl', 'label'=>__('Flemish')],
            ['value' => 'fi', 'label'=>__('Finn')]
        ];
    }
}
