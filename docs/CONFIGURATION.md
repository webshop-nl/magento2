# Configuration

Every setting the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) offers, what it changes and when you would touch it. The extension sits under **Stores > Configuration > Webshop.nl** and has three sections: General for the connection, Product Catalogue for the feed, and Orders for what comes back from the marketplace. Nearly everything can be set per store view, so a Dutch and a Belgian storefront can publish different data from the same catalogue. This page follows the order of the admin screens. If you are setting the extension up for the first time, the quick start covers the handful of settings you actually need to get going.

---

## General

### Enabled

Switches the extension on. With this off nothing runs: no feed, no order import, and the other sections stay empty.

Ships as `No`, so installing the extension changes nothing until you decide it should.

### Mode

`Production` or `Development`. It decides which Webshop.nl environment the **Register now** button points at. Leave it on `Production` unless Webshop.nl asks you to do otherwise.

### Magento Integration Token

Read-only. Created during installation and used by Webshop.nl to push orders into your store over the REST API. Shown so you can confirm it exists.

---

## Product Catalogue

### General

| Setting | What it does |
|---|---|
| **Enable** | Switches the feed on for this store view. Off means no feed is generated and the overview says so. |
| **Filename** | Name of the generated file. The store view id is added automatically, so `webshop.ndjson` becomes `webshop-1.ndjson`. |
| **Auto Generate** | How often the feed refreshes. Daily at midnight by default. Pick `Custom` to enter your own schedule in crontab format. |

The feed runs in its own cron group, so a long run does not hold up the rest of Magento's scheduled work.

### Product Data

Which attribute fills which field in the feed. The defaults match a standard Magento install; change them when your shop keeps the data somewhere else.

**General Fields** — Name, Short Description and Long Description.

**Image Source** — which image goes out as the main one, and whether the gallery is published alongside it.

**Additional Fields** — EAN code, Brand, Color, Material, Size and Gender. Each points at a product attribute. Leave one empty and the field stays out of the feed.

**Extra Info** — a free field for anything the marketplace accepts that does not fit the ones above.

### Category

How the category path reaches Webshop.nl.

- **Type `Category`** publishes the Magento category path of the product, deepest level first.
- **Type `Attribute`** publishes the value of a product attribute instead, for shops that keep a separate marketplace taxonomy.

### Delivery

Delivery time per product, either from an attribute or as a fixed text. Separate values for in stock and out of stock, so you can say "ordered for you" instead of a number of days when something is not on the shelf.

### VAT Percentage

Off by default. Switch it on to publish a VAT rate per product, either from an attribute or as one fixed percentage for the whole catalogue.

### Product Types

The part worth reading twice, because it decides what the marketplace actually sees.

For **configurable**, **bundle** and **grouped** products you choose:

| Setting | What it does |
|---|---|
| **Use ... Products** | Publish the parent, the simples, or both. "Both" is the usual choice: the parent is the page a shopper lands on, the simples carry the stock. |
| **Use Parent URL for Simples** | `No` keeps each simple on its own page. `Yes` publishes it on the parent's page. `Yes, with Auto-Link` adds the attribute selection to the link, so the right variant is picked on arrival. |
| **Use Parent Image for Simples** | Take the parent's image, keep the simple's own, use the parent's only when the simple has none, or combine the two. |
| **Use Parent Data for Simples** | Which attributes a simple inherits from its parent — typically the description, which is usually only filled in on the parent. |
| **Use Fallback only on Non Visible Simples** | Limits the inheritance above to simples that are not visible on their own. A simple that has its own page keeps its own data. |

**Grouped products** have one extra: **Parent Price**, which decides whether the group is published at its own price or at the price of its cheapest member.

A variant that has no page of its own is always published on its parent's page, whatever these settings say. Sending a shopper to a page that does not exist is never useful.

### Additional Configuration

**Extra Fields** adds a fixed field and value to every product in the feed. Useful for data the marketplace asks for that your catalogue does not hold — a brand name for a single-brand shop, for instance.

**Shipping Prices** sets what is published as the delivery cost, per country if you need it.

### Filter Options

What stays out of the feed.

| Filter | What it does |
|---|---|
| **Visibility** | Only publish products with the visibilities you select. Off by default, so everything that passes the other filters goes out. |
| **Filter by Category** | Publish only the categories you pick, or everything except those. |
| **Exclude Out of Stock** | Keeps products without stock out of the feed. |
| **Custom Filters** | Your own conditions on any attribute: publish only what matches, or keep out what does. |

### Feeds

Not a setting but the overview: one row per store view, with the feed URL, when it last ran and how many products went in.

Each row has **Generate** to run it now, **Preview** to see the first lines, and **Download** to fetch the file.

Save the configuration before you generate. The run reads what is stored, not what is on your screen.

---

## Orders

### Enable

Switches the order import on for this store view. With this off the route refuses orders and nothing is written, while the feed keeps being published. That is a perfectly good setup if you only want to advertise on the marketplace.

### Shipping Method

**Default Shipping Method** is the method a marketplace order gets in your store. **Fallback** is used when the first one is not available for the address on the order.

### Customers

**Import Customers** decides whether a marketplace buyer becomes a customer account in your shop or the order is placed as a guest. With it on, **Customer Group** sets which group they land in.

### Address & Invoice

**Separate house number** splits the house number into its own address line, for shops whose address layout expects that.

**Send order e-mail to the customer** and **Send invoice e-mail to the customer** decide whether Magento's own mails go out for marketplace orders. Both off by default — the marketplace usually mails the buyer itself, and a second mail from your shop tends to confuse.

---

## Debugging

**Debug Mode** writes what the extension does to its own log. Leave it off in everyday use: it is verbose and the file grows quickly.

The **Debug Log** and **Error Log** buttons show the last hundred records without server access, and offer them as a text file. Errors are always logged, whether Debug Mode is on or not.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
