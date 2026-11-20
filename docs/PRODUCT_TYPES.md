# Product Types

How the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) publishes configurable, bundle and grouped products, and why these settings matter more than any others in the feed. A configurable product is two things at once in Magento: a page a shopper lands on, and a set of variants that carry the stock. The marketplace needs both, but it needs them connected in the right way. Get these settings wrong and you publish listings without sizes, variants without descriptions, or links that lead nowhere. This page walks through each setting, what it changes in the feed and which combination works for most shops. Simple products without variants are unaffected by anything here.

---

## The shape of the problem

Take a hoodie in three sizes and two colours. In Magento that is:

- one **configurable** product, which has the name, the description and the page
- six **simple** products, which have the stock, the size and the colour

The simples are usually set to *Not Visible Individually*: they have no page of their own, because the configurable is the page.

The marketplace wants to list six buyable items, each with its own stock, all pointing at one product. These settings decide how that mapping is made.

---

## Use ... Products

The first choice: what goes in the feed.

| Value | Result |
|---|---|
| `Parent` | Only the configurable. One listing, no sizes, stock from the parent. |
| `Simple` | Only the variants. Six listings with stock, but no description and no page. |
| `Both` | The configurable and its variants. |

**`Both` is right for nearly every shop.** The parent sells the product, the variants carry what is actually available.

`Parent` makes sense when the marketplace treats your listing as a single item regardless of size. `Simple` is rare and usually a mistake.

---

## Use Parent URL for Simples

Where a variant's link points.

| Value | Result |
|---|---|
| `No` | The variant links to its own page — which, for an invisible variant, does not exist. |
| `Yes` | The variant links to the parent's page. |
| `Yes, with Auto-Link` | The parent's page, with the attribute selection in the link. |

**`Yes, with Auto-Link` is the recommended setting.** The link carries something like `#93=49&144=166`, which tells the product page to select blue and size M on arrival. A shopper who clicked "blue, M" on the marketplace lands on exactly that.

With plain `Yes` they land on the product with nothing selected and have to choose again.

`No` is only sensible when your variants really do have their own pages.

A variant that has no page of its own is published on its parent's page regardless of this setting. Sending a shopper to a page that does not exist is never useful, so the extension does not do it.

---

## Use Parent Image for Simples

Which image a variant publishes.

| Value | Result |
|---|---|
| `No` | The variant's own image. |
| `Yes` | The parent's image. |
| `Only if Empty` | The variant's own image, or the parent's when it has none. |
| `Combine, simple images first` | Both galleries, the variant's first. |
| `Combine, parent images first` | Both galleries, the parent's first. |

**`Only if Empty` suits most catalogues.** A variant with its own photo publishes it; one without borrows from the parent rather than going out imageless.

Pick a `Combine` option when your variants have a photo each and you want the full gallery on every listing.

---

## Use Parent Data for Simples

Which attributes a variant inherits.

Descriptions are typically only filled in on the configurable. Without inheritance every variant goes out with an empty description, which marketplaces treat as a weak listing.

Select the attributes that live on the parent — the description almost always, sometimes the brand or a category attribute.

---

## Use Fallback only on Non Visible Simples

Limits all of the inheritance above to variants that are *Not Visible Individually*.

Set it to `Yes` and a simple product with its own page keeps its own data, while the invisible variants borrow from their parent.

This matters for catalogues where a product is both sold on its own and part of a grouped product. Without this setting it would inherit from the group it happens to belong to, losing its own description and link.

---

## Parent Price (grouped products only)

A grouped product has no price of its own; it is a set of products sold together.

| Value | Result |
|---|---|
| `No` | The group is published without a price of its own. |
| `Yes` | The group takes the price of its cheapest member. |

Most marketplaces need a price, so `Yes` is usually the workable choice.

---

## Bundle and grouped products

The same settings appear for bundles and grouped products, and mean the same thing.

Differences worth knowing:

- A **bundle** is one product built from components. Its price is the sum of what is in it, which is why a bundle can show a range rather than a single figure.
- A **grouped** product is a set of separate products shown together. Each member is bought on its own.
- Components of a bundle and members of a group are often not visible individually, so the same URL and data inheritance applies.

---

## The combination that works for most shops

For configurable products:

| Setting | Value |
|---|---|
| Use Configurable/Simple Products | `Both` |
| Use Parent URL for Simples | `Yes, with Auto-Link` |
| Use Parent Image for Simples | `Only if Empty` |
| Use Parent Data for Simples | Description |
| Use Fallback only on Non Visible Simples | `Yes` |

Then check your work: open the Feeds preview, find a variant, and confirm it carries its own size and colour, the parent's description, and a link that opens the parent with that variant selected.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
