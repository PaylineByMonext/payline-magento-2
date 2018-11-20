# Magento 2 Payline Module #

## Installation

Log in to the Magento server, go to your Magento install dir and run these commands:
```
composer config repositories.paylinebymonext-payline-magento-2 vcs https://github.com/PaylineByMonext/payline-magento-2
composer require monext/module-payline

php -f bin/magento module:enable Monext_Payline
php -f bin/magento setup:upgrade
```

## Configuration
*Find Payline configuration menu under STORES > Settings > Configuration > SALES > Payment Methods
*Fill your merchant ID and access key under the "Common settings" section.
*Contracts are imported under the "Payline Contracts" section. The multi-select "Contracts" fied allows to choose which one will be visible on your store.
* "Payment solutions" section displays the "Payline - Web Payment Cpt" method configuration (more to come). Select your payment options and order status mapping in this section.

##  Payline Category Mapping
The dedicated "Payline Category Mapping" field is added by the Payline extension under the "Content" section of each product category. The drop-down list allows to map your custom category with one from Payline's nomenclature. This mapping is necessary for partner solutions like Oney, Cofinoga, Cetelem,...
