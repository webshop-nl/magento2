# Changelog

## [1.2.0]

**Features:**
* The product feed is now built on a shared ProductData service. Products, stock, prices, images and categories come out of one collector. The feed fields and the NDJSON structure are unchanged.
* Added a unit test suite (`Test/Unit`) covering the collectors and a set of architecture gates that pin the ACL, the CLI wiring, the configuration paths, the cron wiring, the data patch contract and the changelog format. It runs in CI on PHP 8.2 to 8.5.
* Added `docs/CLI_COMMANDS.md` for `bin/magento webshopnl:feed:create`.

**Improvements:**
* PHP 8.2 or newer is required, and Magento 2.4.6 or newer, the first release that runs on PHP 8.2. The compile and PHPStan job runs PHP 8.2 on Magento 2.4.7-p10, 8.3 and 8.4 on 2.4.8-p5 and 8.5 on 2.4.9.
* PHPStan runs at level 8 without a baseline; it ran at level 1 before.
* The code passes the Magento coding standard at severity 6. The sniffer ran without a standard before and so checked nothing. CI also runs a PHP 8.2 to 8.6 compatibility check.
* The feed cron runs in its own cron group (`webshopnl`, separate process), so a long feed run no longer holds up Magento's default group.
* Every configuration field offers "Use system value". A select whose first option means something now has that choice declared as a default, so what the admin shows with the checkbox ticked is what the module reads.

**Bugfixes:**
* Fixed the product feed carrying no link for most of its products. 1859 of 2040 rows had an empty `productURL`: only a product with a URL rewrite of its own got one, so every variant of a configurable or bundle was published without a link. Every row now carries one.
* Fixed `bin/magento webshopnl:feed:create --store-id=1` failing with a type error. The option arrives as a string and was passed straight to a method that requires an integer, so the option could never be used.
* Fixed a configurable parent being published with a price nothing in the catalogue sells for. The parent took the minimum price from Magento's price index, which with a catalog price rule sits below the price of every variant. It now carries the cheapest price a customer can pay for one of its variants, which is what the product page shows. Neither index column answers that on its own: the minimum can sit below every variant and the final price above them, depending on whether the discount applies to the parent or to the variants.
* Fixed the feed cron never running. `crontab.xml` named `Model\Cron\GenerateFeed` while the class is `GenerateFeeds`, so Magento could not start the job at all; and the job read its frequency from `webshopnl/feeds/cron_frequency` while the field writes `webshopnl/feed/cron_frequency`, so it would have returned at once. The schedule is now read from the Frequency field itself, "Custom" resolves to the custom expression on save, and a stored "Custom" from an earlier release is replaced by its expression during `setup:upgrade`.
* Fixed a shop with more than one store view generating only the first feed. The price collection was built once and reused, so the second store view added the same join a second time and the run stopped on "You cannot define a correlation name 'price_index' more than once".
* Fixed disabled products reaching the feed. The "Exclude out of stock products" setting filled in the unrelated "add disabled products" flag, so with the setting off every disabled product was published, and no product was ever excluded for being out of stock. Both now do what they say.
* Fixed "Use Parent Data for Simples" doing nothing for configurable, bundle and grouped products. The fields stored under `..._parent_atts` while the feed read `..._parent_attributes`.
* Fixed "Use Parent URL for Simples" under Grouped Products doing nothing. The field had no configuration path at all; a value saved by an earlier release is moved during `setup:upgrade`.
* Fixed `setup:upgrade` failing on Magento 2.4.9 with "Class WebshopNL\Connect\Console\Command\GenerateFeed\Interceptor does not exist". The console command is registered on the interface Magento resolves, both of its dependencies are injected as proxies, and `configure()` declares its return type.
* Fixed the Product Feed and Order sections being behind ACL resources that no `acl.xml` declares, which denied them to every admin role without full access. All three sections are now behind `WebshopNL_Connect::config`.
* Removed a reference to another extension's product attribute in the feed filter, and the two category attributes this extension never creates. The category path in the feed is the Magento one.

**Tests:**
* `Test/Unit/Architecture` pins the ACL, the CLI wiring, the configuration paths and defaults, the cron wiring, the data patch contract, the namespaces and the changelog format.
* `Test/Unit/Service/ProductData` covers the collectors: price carry-over, rule prices, MSI reservations, configurable keys, category paths, item groups and the visibility filter.
* An end-to-end suite runs on Magento 2.4.7 and 2.4.9 and pins what this release repairs: every row has a link, a shop with two store views gets two feeds, a disabled product stays out, the stock filter works, and a configurable parent carries the price of its cheapest buyable variant.
* The order import is driven over its own REST route with a real integration token: a posted order becomes a Magento order with the right price, quantity, shipping cost and addresses, a customer account is created when that is switched on, the same order twice is refused, every refusal names the field it is missing, an unauthenticated request is denied, and the status route reports a shipment with its tracking number.
* `E2E-TESTS.md` is generated from the Playwright test list.

**Compatibility:**
* Magento 2.4.6 and up
* PHP 8.2 and up
