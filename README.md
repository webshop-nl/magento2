# Webshop.nl plugin for Magento® 2

The plugin makes it effortless to connect your Magento® 2 catalog with the Webshop.nl Marketplace.

### Installation using Composer ###
Magento® 2 use the Composer to manage the module package and the library. Composer is a dependency manager for PHP. Composer declare the libraries your project depends on and it will manage (install/update) them for you.

Check if your server has composer installed by running the following command:
```
composer –v
``` 
If your server doesn’t have composer installed, you can easily install it by using this manual: https://getcomposer.org/doc/00-intro.md

Step-by-step to install the Magento® 2 extension through Composer:

1.	Connect to your server running Magento® 2 using SSH or other method (make sure you have access to the command line).
2.	Locate your Magento® 2 project root.
3.	Install the Magento® 2 extension through composer and wait till it's completed:
```
composer require webshop-nl/magento2
``` 
4.	Once completed run the Magento module enable command:
```
bin/magento module:enable WebshopNL_Connect
``` 
5.	After that run the Magento® upgrade and clean the caches:
```
php bin/magento setup:upgrade
php bin/magento cache:flush
```
6.  If Magento® is running in production mode you also need to redeploy the static content:
```
php bin/magento setup:static-content:deploy
```
7.  After the installation: Go to your Magento® admin portal and open ‘Stores’ > ‘Configuration’ > ‘WebshopNL.
  
   
## Development by Magmodules

We are a Dutch Magento® Only Agency dedicated to the development of extensions for Magento & Shopware. 
All our extensions are coded by our own team and our support team is always there to help you out. 

[Visit Magmodules.eu](https://www.magmodules.eu/)
