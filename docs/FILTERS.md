# Filtering the Feed

Deciding which products the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) publishes and which ones stay home. By default the feed carries your whole catalogue, and for many shops that is right. But there are usually products you would rather not advertise: things you cannot ship, a clearance category, items below a margin you are willing to accept on a marketplace. The extension has four ways to keep products out, and they stack — a product has to pass all of them. This page covers each filter, what it does, and the trap that catches most shops the first time. Filters live under **Product Catalogue > Filter Options**.

---

## Visibility

Publish only products whose visibility matches what you select.

**This is the filter that catches people out.** It is off by default, and for most shops it should stay off.

Switch it on and select only "Catalog, Search", and every variant of every configurable product disappears from your feed. Variants are deliberately *Not Visible Individually* — that is how Magento works. The result is listings without sizes, and a marketplace reporting nothing in stock.

If you do want this filter, include "Not Visible Individually" among the selected values.

Reasonable use: a catalogue with genuinely hidden products you never want advertised, where you have checked that your variants are not among them.

---

## Filter by Category

Publish only certain categories, or everything except certain categories.

Two modes:

- **In** — only products in the categories you pick
- **Not in** — everything except those

Useful for keeping a clearance or staff category off the marketplace, or for publishing only the range you have agreed with Webshop.nl.

A product in several categories passes as soon as one of them qualifies.

---

## Exclude Out of Stock

Keeps products without stock out of the feed.

**Worth switching on.** A listing for something you cannot ship earns a cancellation and a mark against your marketplace account.

The feed refreshes daily, so a product comes back by itself once stock returns. For fast-moving items, generating more often than once a day shortens the window where the marketplace knows about stock you no longer have.

Stock here means salable quantity: what is on the shelf minus what is reserved for orders not yet shipped.

---

## Custom Filters

Your own conditions, on any product attribute.

Each condition is an attribute, a comparison and a value. Set the mode to publish only what matches, or to keep out what matches.

Examples that come up:

- price above a threshold, to keep low-margin items off a marketplace that charges commission
- a yes/no attribute like `exclude_from_marketplace` that your team sets per product
- a brand you are not allowed to sell outside your own shop

Conditions combine: a product has to satisfy all of them.

Worth knowing: a filter on an attribute without an index makes the feed slower to build. If your run goes from seconds to minutes after adding one, that is usually why.

---

## How the filters stack

A product reaches the feed only when it passes every filter. When something is missing, check them in this order:

1. **Exclude Out of Stock** — is it in stock?
2. **Filter by Category** — is it in a category you publish?
3. **Visibility** — does its visibility match?
4. **Custom Filters** — does it satisfy your conditions?
5. **Product Types** — if it is a variant, are simples published at all?

That last one is not strictly a filter, but it keeps products out just as effectively. If **Use Configurable/Simple Products** is set to `Parent`, no variant will ever reach the feed no matter what the filters say.

---

## Checking your work

After changing a filter, generate the feed once and look at the count in the Feeds overview. Compare it with what you expect.

With **Debug Mode** on, the log names every product that was left out and which rule did it. That turns "something is missing" into a specific answer, and it is much faster than working through the filters by hand.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
