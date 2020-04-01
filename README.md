# Magento 2 Payline Module #

## Installation (EN)

Log in to the Magento server, go to your Magento install dir.
 
You have to create a new directory to store the module zip package, payline-magento2-__x.y.z__.zip (__x.y.z__ is the version of the module).
 ```
mkdir -p extra/composer/artifact/zip
```

Save the zip package payline-magento2-__x.y.z__.zip in the directory extra/composer/artifact/zip

Run composer to deploy module and dependencies (_monext/payline-sdk_)
```
composer config repositories.zip artifact extra/composer/artifact/zip
composer require monext/module-payline:x.y.z
```

Run magento command to enable the module
```
php -f bin/magento module:enable Monext_Payline
php -f bin/magento setup:upgrade
```


## Installation  (FR)

Se connecter en ssh sur le serveur qui héberge Magento et se positionner dans le répertoire d'installation.
 
Si il n'existe pas encore, créer le répertoire extra/composer/artifact/zip pour stocker l'archive du module (payline-magento2-__x.y.z__.zip, __x.y.z__ étant la version du module).
 ```
mkdir -p extra/composer/artifact/zip
```

Copier l'archive du module payline-magento2-__x.y.z__.zip dans ce  répertoire ( extra/composer/artifact/zip )

Lancer composer pour déployer le module et ses dépendances (_monext/payline-sdk_)
```
composer config repositories.zip artifact extra/composer/artifact/zip
composer require monext/module-payline:x.y.z
```

Lancer la commande Magento pour activer le module
```
php -f bin/magento module:enable Monext_Payline
php -f bin/magento setup:upgrade
```

Vérifier dans le dossier vendor que les dossiers sont bien présents et non vide
- vendor/monext/module-payline
- vendor/monext/payline-sdk


## Configuration
*  Find Payline configuration menu under STORES > Settings > Configuration > SALES > Payment Methods > Payline
*  Fill your Merchant ID and Access key under the "Common settings" section.
*  Contracts are imported under the "Payline Contracts" section. The multi-select "Contracts" fied allows to choose which one will be visible on your store.
*  "Payment solutions" section displays the "Payline - Web Payment Cpt" method configuration (more to come). You have to enable the solution and select your payment options and order status mapping in this section.
