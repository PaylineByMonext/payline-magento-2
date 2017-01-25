# Magento 2 Payline Module #

## Installation

Log in to the Magento server, go to your Magento install dir and run these commands:
```
composer config repositories.paylinebymonext-payline-magento-2 vcs https://github.com/PaylineByMonext/payline-magento-2
composer require monext/module-payline

php -f bin/magento module:enable Monext_Payline
php -f bin/magento setup:upgrade