<?php
namespace Monext\Payline\Model\System\Config\Source;

class AllMethods extends \Magento\Shipping\Model\Config\Source\Allmethods
{
    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $methods = [['value' => '', 'label' => '']];
        $carriers = $this->_shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $_title = $this->_scopeConfig->getValue(
                'carriers/' . $carrierCode . '/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $_title = ($_title)?:$carrierCode;
            $methods[$carrierCode] = ['label' => $_title, 'value' => []];
            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methodTitle = ($methodTitle)?:$methodCode;
                $methods[$carrierCode]['value'][] = [
                    'value' => $carrierCode . '_' . $methodCode,
                    'label' => '[' . $carrierCode . '] ' . $methodTitle,
                ];
            }
        }

        return $methods;
    }
}
