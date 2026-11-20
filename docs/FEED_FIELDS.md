# Feed Fields

Every field the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) writes into the product feed, what it holds and where it comes from. Useful when the marketplace asks about a value you do not recognise, when you are deciding which attribute to point a setting at, or when you want to check the feed before handing the URL over. The feed is NDJSON: one JSON object per line, one line per product, no wrapper around it. That makes it easy to read with ordinary tools — `head` shows you the first products, and any JSON viewer will take a single line. Fields with no value are left out of the line rather than written empty.

---

## Identification

| Field | Holds | Comes from |
|---|---|---|
| `ID` | The Magento product id | The product itself |
| `sku` | The SKU | The product itself |
| `family_ID` | The id of the parent, for a variant | The configurable, bundle or grouped product it belongs to |

A product with a `family_ID` is a variant. One without it stands on its own. The marketplace uses this to group sizes and colours under a single listing.

## Text

| Field | Holds | Set under |
|---|---|---|
| `name` | Product name | Product Data > General Fields > Name |
| `description` | Short description | Product Data > General Fields > Short Description |
| `descriptionLong` | Long description | Product Data > General Fields > Long Description |

Each points at a product attribute, so a shop that keeps its marketplace copy in a separate attribute can publish that instead of the webshop text.

## Links and images

| Field | Holds |
|---|---|
| `productURL` | The page a shopper lands on |
| `imageURL` | The main image |
| `additionalImages` | The rest of the gallery |

A variant has no page of its own in Magento, so it is published on the page of its parent. With Auto-Link switched on, the link carries the attribute selection, and the right size and colour are chosen on arrival.

## Price

| Field | Holds |
|---|---|
| `fromPrice` | The price before any discount |
| `price` | What a customer pays today |
| `discount` | The difference as a percentage |
| `vat_pct` | The VAT rate, when you publish one |

`price` follows catalogue price rules and special prices. For a configurable product it is the cheapest price one of its variants actually sells for, which is what the product page shows.

`vat_pct` is off by default; switch it on under Product Data > VAT Percentage.

## Stock and delivery

| Field | Holds |
|---|---|
| `qty` | The salable quantity |
| `availability` | Whether it can be ordered |
| `deliveryTime` | How long delivery takes |
| `deliveryCosts` | What delivery costs |

`qty` is the salable quantity, not the raw stock figure: it is what is on the shelf minus what is reserved for orders that have not shipped yet. Ten in stock with eight ordered publishes as two.

`deliveryTime` has a separate value for in stock and out of stock, so you can say "ordered for you" instead of a number of days.

## Product attributes

| Field | Set under |
|---|---|
| `EAN` | Product Data > Additional Fields > EAN-code |
| `brand` | Product Data > Additional Fields > Brand |
| `color` | Product Data > Additional Fields > Color |
| `material` | Product Data > Additional Fields > Material |
| `size` | Product Data > Additional Fields > Size |

Point one at an attribute your catalogue does not fill and the field stays out of the feed. That is better than an empty value, which marketplaces tend to read as a thin listing.

## Categories

| Field | Holds |
|---|---|
| `categoryPath` | The full path, deepest level first |
| `categories` | The top level |
| `subcategories` | The second level |
| `subsubcategories` | The third level |

Published from the Magento category tree by default. Set Product Data > Category > Type to `Attribute` to publish a product attribute instead, for shops that keep a separate marketplace taxonomy.

## Free fields

| Field | Holds |
|---|---|
| `extra_info` | Anything the marketplace accepts that does not fit elsewhere |

Extra Fields under Additional Configuration adds a fixed field and value to every product — handy for a brand name in a single-brand shop, or any value the marketplace expects that your catalogue does not hold.

---

## Reading the file

Open the Feeds overview under Product Catalogue. Each store view has **Preview** for the first lines and **Download** for the whole file.

Use Download rather than the URL when you want to be certain you are looking at the latest run: the URL does not change between runs, so browsers tend to show you the previous one.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
