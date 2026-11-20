# Getting Started

This page walks you through the first run of the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html): switching it on, connecting your store to the marketplace, generating your first product feed and checking that it holds what you expect. It is the short route. Every setting has a sensible default, so you can be live with a handful of choices and tune the rest later. Reckon on five minutes for a shop that is otherwise ready, plus the time it takes to generate a feed for a large catalogue. If something does not behave the way it reads here, the troubleshooting page covers the usual causes.

---

## Step 1: Switch the extension on

Go to **Stores > Configuration > Webshop.nl > General**.

Set **Enabled** to `Yes` and save.

Two things appear once you do:

- **Mode** — leave this on `Production`. It decides which Webshop.nl environment the registration button sends you to.
- **Magento Integration Token** — generated during installation. Webshop.nl uses it to push orders into your store. You do not have to do anything with it; it is shown so you can check it exists.

## Step 2: Register your store

Press **Register now** in the blue header. It opens the Webshop.nl partner portal and carries your store details along, so the account there is linked to this shop.

## Step 3: Switch on the product feed

Go to **Webshop.nl > Product Catalogue** and set **Enable** to `Yes`.

Two settings appear:

- **Filename** — the name of the generated file. The default, `webshop.ndjson`, is fine.
- **Auto Generate** — how often the feed is refreshed. Daily at midnight is the default and suits most shops.

Save the configuration. **Save before you generate**, or the run uses the settings as they were.

## Step 4: Generate the feed

Scroll down to **Feeds**. Every store view has its own row.

Press **Generate** on the store view you want to publish. After a moment the row reports how many products went in and when it ran.

Press **Preview** to see the first lines without downloading, or **Download** to get the file itself.

## Step 5: Check what you published

Open the preview and look at a few rows:

- **productURL** should open the product page in your shop
- **price** should match what a customer pays
- **availability** should say `in stock` for something you can order

Variants of a configurable product are published on the page of their parent, since they have no page of their own. That is expected.

## Step 6: Hand the URL to Webshop.nl

Each row in the Feeds overview shows the `.ndjson` URL for that store view. Copy the one you need into your Webshop.nl account.

Because browsers cache, download the file rather than opening the URL when you want to be sure you are looking at the latest version.

---

## Accepting orders

Orders are a separate switch. Go to **Webshop.nl > Orders** and set **Enable** to `Yes`.

Set a **Default Shipping Method** — the method your Magento store uses for orders that come in from the marketplace. Pick a **Fallback** as well, used when the first one is unavailable for the address on the order.

With this off, your feed keeps being published but orders are refused. That is a valid setup if you only want to advertise.

## What is next

Your feed now runs on its own, once a day. Worth looking at when you have time:

- **Product Types** decides how configurable, bundle and grouped products are published — as the parent, the variants, or both
- **Filter Options** keeps products out of the feed: by visibility, by category, or out-of-stock items
- **Extra Fields** adds a fixed value to every product, for data the marketplace wants that your catalogue does not hold

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
