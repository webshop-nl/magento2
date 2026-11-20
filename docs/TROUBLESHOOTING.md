# Troubleshooting

What to check when the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) does not do what you expect: an empty feed, products that stay out, links that go nowhere, orders that do not arrive. Each item below names what you see, what causes it and what to change, with a note on how to keep it from happening again. Start with the symptom that matches yours rather than reading top to bottom. If none of them fit, Debug Mode and the log viewer under Debugging will usually tell you more than guessing does, and our support team can read along with you from there.

---

## The feed

### The overview says "Feed disabled"

**What you see:** every store view row reads *Feed disabled. Set Enable to Yes under General to generate it.*

**Why:** the feed is switched off for that store view. Note this is the **Enable** under Product Catalogue > General, not the one under Webshop.nl > General — the extension can be on while the feed is off.

**Fix:** set Product Catalogue > General > **Enable** to `Yes` and save.

### Generate does nothing, or the count stays at zero

**Why:** your filters exclude everything. The usual culprit is the **Visibility** filter set to "Catalog, Search" in a catalogue that is mostly configurable products, since variants are deliberately not visible on their own.

**Fix:** switch the Visibility filter off, or add "Not Visible Individually" to the selected values. Generate again and watch the count.

**Prevention:** after changing a filter, generate once and compare the count with what you expect.

### Products are missing from the feed

Work through the filters in this order:

1. **Exclude Out of Stock** — is the product in stock?
2. **Filter by Category** — is it in a category you publish?
3. **Visibility** — does its visibility match what you selected?
4. **Custom Filters** — does it pass your own conditions?
5. **Product Types** — if it is a variant, are simples published at all?

With Debug Mode on, the log names every product left out and which rule did it.

### The feed shows old data

**Why:** your browser cached the file. The URL does not change between runs, so the browser sees no reason to fetch it again.

**Fix:** use the **Download** button instead of opening the URL. That always fetches the current file.

### Prices do not match the product page

**Why:** usually a catalogue price rule. A configurable product is published at the cheapest price one of its variants actually sells for.

**Fix:** open the preview, find the product, compare it to the product page. If they differ, reindex (`bin/magento indexer:reindex`) and generate again — the feed reads the price index.

**Prevention:** check a few configurable products after adding or changing a catalogue price rule.

### Only the first store view gets a feed

**Why:** the other store views have their feed switched off, or the feed was only generated for one of them.

**Fix:** check **Enable** at each store view's scope, and press Generate on each row. Each store view has its own file and its own URL.

---

## Links in the feed

### A product link opens a 404

**Why:** the product has no URL of its own. Variants of a configurable are usually not visible individually, so Magento gives them no page.

**Fix:** nothing — the extension publishes such a product on its parent's page by itself.

If you do see a 404, the product has no parent either: it is invisible and stands alone. Make it visible, give it a parent, or keep it out of the feed with a filter.

**Prevention:** after a catalogue import, check whether products arrived without a parent and without visibility.

### Links point at the wrong store view

**Why:** "Add Store Code to Urls" is on and the links were built without it.

**Fix:** generate again. The feed adds the store code when your shop uses it.

### Variant links carry a hash like `#93=49`

That is **Auto-Link**, and it is intentional: it opens the parent page with that variant selected.

Do not want it? Set **Use Parent URL for Simples** to `Yes` instead of `Yes, with Auto-Link`.

---

## Stock

### The marketplace sells something that is out of stock

**Why:** the feed is a snapshot. Between two runs your stock can run out.

**Fix:** switch **Exclude Out of Stock** on, so a product leaves the feed as soon as it runs out.

**Prevention:** for fast-moving items, generate more often than once a day.

### The published quantity is higher than what you have

**Why:** the quantity in the feed is the salable quantity — stock minus what is reserved for orders not yet shipped.

**Fix:** check the product in Magento. If the salable quantity there matches the feed, the feed is right and the expectation was off.

---

## Orders

### Orders do not arrive

Check in this order:

1. **Webshop.nl > Orders > Enable** — is the order import on for this store view?
2. **Webshop.nl > General > Enabled** — is the extension on at all?
3. **Magento Integration Token** — is it shown under General? Without it Webshop.nl cannot authenticate.
4. The admin role the integration uses needs access to the Webshop.nl resources.

With Debug Mode on, every incoming order is logged, including the ones that are refused and why.

### An order is refused as "already imported"

That is deliberate: the same marketplace order is only written once. Webshop.nl retries after a hiccup, and this stops you ending up with two Magento orders for one sale.

### The import stops on the shipping method

**Why:** the default shipping method is not available for the address on the order.

**Fix:** set a **Fallback for Shipping Method** under Orders > Shipping Method.

### An order comes in without a customer account

**Why:** **Import Customers** is off, so marketplace orders are placed as guest orders.

**Fix:** switch it on and pick a **Customer Group**. Existing orders stay as they are.

---

## Logs

### Finding out what happened

**Stores > Configuration > Webshop.nl > General > Debugging** has a **Debug Log** and an **Error Log** button. Each shows the last hundred records and offers them as a text file.

Errors are always logged. Debug Mode adds everything else: which products were left out, which orders came in, how long a run took.

Leave Debug Mode off in everyday use — it is verbose and the file grows quickly. Switch it on while you are investigating, then off again.

### After an upgrade something stopped working

Run `bin/magento setup:upgrade` and `bin/magento cache:flush`. If your shop runs in production mode, redeploy the static content as well.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
