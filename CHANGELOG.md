# Changelog

## [1.2.0]

**Improvements:**
* The feed runs in its own cron group, so a long feed run no longer holds up other scheduled jobs.
* Every setting can be put back to "Use system value" per store view, and every choice has a default to fall back to.
* Magento 2.4.6 and PHP 8.2 are the minimum, tested against 2.4.7 through 2.4.9.
* A large catalogue needs one price lookup per batch instead of one per product.
* The feeds overview says the feed is switched off, instead of reporting the module as disabled.
* Added documentation for the feed command.

**Bugfixes:**
* Every product in the feed carries a link; variants of configurable and bundle products were published without one.
* A shop with more than one store view gets a feed for every store view, not only the first.
* The scheduled feed run works, and a custom schedule is kept as entered.
* Disabled products stay out of the feed, and "Exclude out of stock products" filters on stock.
* A configurable product is published at the cheapest price one of its variants sells for, which is what the product page shows.
* "Use Parent Data for Simples" and "Use Parent URL for Simples" apply to the products they are set for.
* A product that belongs to more than one parent takes its title, link, images and categories from its configurable, the same way everywhere in the feed.
* The feed can be generated for one store view from the command line.
* Imported orders carry their totals; the order, line and shipping amounts arrived at zero.
* The Product Feed and Order settings are available to admin roles without full access.
* The order import has its own Enable under Orders; that setting switched the whole module, so turning the import off also stopped the feed.
* Registration opens the Webshop.nl partner portal; new installations pointed at the development portal instead.
* The module installs and upgrades on Magento 2.4.9.
* The shop's REST schema can be generated again with the module installed.
* The feed no longer refers to attributes this module does not create; the category path is the Magento one.

**Tests:**
* 68 end-to-end tests on Magento 2.4.7 and 2.4.9 covering the feed, the order import over its own REST route, and the admin settings.
* 145 unit tests with architecture checks on the ACL, cron wiring, configuration paths and defaults, web API types, CLI wiring and the data patch contract.

**Compatibility:**
* Magento 2.4.6 and up
* PHP 8.2 and up

