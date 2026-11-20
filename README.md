# Webshop.nl for Magento 2

Connect your Magento 2 catalogue to the Webshop.nl marketplace: publish your products and take the orders back into your own shop.

The [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) does both halves of a marketplace connection. It builds a product feed from your catalogue — prices, stock, images, categories and delivery, per store view — and refreshes it on a schedule you set. When a shopper buys on the marketplace, Webshop.nl posts that order straight into your Magento store, where it becomes an ordinary order you pick, pack and ship like any other. The marketplace then reads the shipment and tracking number back out, so the buyer gets their track and trace without you typing anything twice. Everything is set per store view, so a Dutch and a Belgian storefront can publish different data from the same catalogue.

## Features

- **A feed per store view** — each one gets its own file, its own URL and its own settings
- **Scheduled refresh** — daily by default or on a schedule of your own, in a cron group of its own so a long run holds nothing up
- **Variants handled properly** — a size or colour with no page of its own is published on its parent's page, with the right variant pre-selected
- **Accurate stock** — the quantity published is what you can actually sell, after reservations for orders not yet shipped
- **Order import** — marketplace orders arrive as Magento orders, with your shipping method, customer group and address layout
- **Status back to the marketplace** — ship in Magento and the tracking number is there for the buyer
- **Filters** — keep products out by visibility, category, stock, or conditions of your own
- **Log viewer in the admin** — read the last hundred debug or error records without server access

## Requirements

- Magento 2.4.6 or newer
- PHP 8.2 or newer

## Installation

```bash
composer require webshop-nl/magento2
bin/magento module:enable WebshopNL_Connect
bin/magento setup:upgrade
bin/magento cache:flush
```

Running in production mode? Redeploy the static content as well:

```bash
bin/magento setup:static-content:deploy
```

## Quick Start

1. **Stores > Configuration > Webshop.nl > General** — set **Enabled** to `Yes` and save
2. Press **Register now** to link your store to a Webshop.nl account
3. **Product Catalogue** — set **Enable** to `Yes` and save
4. Scroll to **Feeds**, press **Generate** on a store view, and check the result with **Preview**
5. Copy that store view's `.ndjson` URL into your Webshop.nl account
6. **Orders** — set **Enable** to `Yes` and pick a shipping method to start accepting orders

The extension ships switched off, so installing it changes nothing until you decide it should.

## Documentation

**English:**

**[Getting Started](docs/QUICKSTART.md)** - Up and running in six steps

**[Configuration Guide](docs/CONFIGURATION.md)** - Every setting in all three sections

**[Product Types](docs/PRODUCT_TYPES.md)** - Configurable, bundle and grouped products

**[Feed Fields](docs/FEED_FIELDS.md)** - All 31 fields and where they come from

**[Filtering the Feed](docs/FILTERS.md)** - Keeping products out, and in what order

**[Order Import](docs/ORDER_IMPORT.md)** - How an order arrives and what Magento makes of it

**[Testing Orders](docs/TESTING_ORDERS.md)** - Proving the order side works

**[Best Practices](docs/BEST_PRACTICES.md)** - The choices behind the settings

**[Troubleshooting](docs/TROUBLESHOOTING.md)** - Symptom, cause and fix

**[CLI Commands](docs/CLI_COMMANDS.md)** - Generating the feed from the command line

**Nederlands:**

**[Aan de slag](docs/QUICKSTART_NL.md)** - In zes stappen draaiend

**[Configuratie](docs/CONFIGURATION_NL.md)** - Elke instelling in alle drie de secties

**[Producttypes](docs/PRODUCT_TYPES_NL.md)** - Configureerbare, bundel- en groepsproducten

**[Feedvelden](docs/FEED_FIELDS_NL.md)** - Alle 31 velden en waar ze vandaan komen

**[De feed filteren](docs/FILTERS_NL.md)** - Producten buiten houden, en in welke volgorde

**[Orderimport](docs/ORDER_IMPORT_NL.md)** - Hoe een order binnenkomt en wat Magento ervan maakt

**[Orders testen](docs/TESTING_ORDERS_NL.md)** - Bewijzen dat de orderkant werkt

**[Best Practices](docs/BEST_PRACTICES_NL.md)** - De afwegingen achter de instellingen

**[Probleemoplossing](docs/TROUBLESHOOTING_NL.md)** - Symptoom, oorzaak en oplossing

**[CLI Commando's](docs/CLI_COMMANDS_NL.md)** - De feed genereren vanaf de commandoregel

## How it Works

The feed is NDJSON: one JSON object per line, one line per product. A row looks like this:

```json
{"ID":"1234","sku":"WH12-M-Blue","name":"Hoodie","family_ID":"1200","price":"49.95 EUR","qty":7,"availability":"in stock","productURL":"https://shop.test/hoodie.html#93=49"}
```

`family_ID` ties a variant to its parent, so the marketplace can group sizes and colours under one listing. `qty` is the salable quantity, after reservations. The `#93=49` on the link selects that variant when the shopper arrives.

Orders travel the other way, over three REST routes that Webshop.nl calls with the integration token the extension creates during installation:

| Route | Used for |
|---|---|
| `/V1/webshopnl/order/<store_id>` | Placing an order |
| `/V1/webshopnl/status/<order_id>` | Reading status, shipment and tracking |
| `/V1/webshopnl/info` | Reading your feed URLs and settings |

## Support

- **Product Page:** [Webshop.nl for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html)
- **Documentation:** [Magmodules Help Center](https://www.magmodules.eu/help/magento2-webshop-marketplace.html)
- **Support:** [Contact Magmodules Support](https://www.magmodules.eu/support)

## Development by Magmodules

We are a Dutch Magento-only agency dedicated to the development of extensions for Magento and Shopware. All our extensions are coded by our own team, and our support team is always there to help you out.

[Visit Magmodules.eu](https://www.magmodules.eu/)

## License

See COPYING.txt

## Copyright

Copyright © Magmodules.eu. All rights reserved.
