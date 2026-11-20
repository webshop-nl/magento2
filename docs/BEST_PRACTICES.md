# Best Practices

Choices that tend to work out well with the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html), and a few that look sensible but cost you sales. Most of it comes down to two questions: what does a shopper see when they click your listing, and does your stock figure tell the truth. This page is about the decisions behind the settings rather than the settings themselves — those are covered in the configuration reference. Read it when your feed runs and you want to know whether it runs well. The scenarios at the end cover the shop shapes we see most: a single store, several store views, and a catalogue that leans heavily on configurable products.

---

## Publishing variants

### Publish both the parent and the simples

For configurable products, set **Use Configurable/Simple Products** to `Both`.

The parent is the page a shopper lands on and the one that carries the description and the category. The simples carry size, colour and stock. Publish only the parent and the marketplace cannot tell which size is available; publish only the simples and you lose the page that sells the product.

### Send variants to the parent page with Auto-Link

Set **Use Parent URL for Simples** to `Yes, with Auto-Link`.

A variant usually has no page of its own in Magento — it is not visible individually. Auto-Link publishes the parent's page with the attribute selection in the link, so someone clicking "blue, size M" arrives at the product with blue and M already chosen.

`Yes` without Auto-Link sends them to the parent page with nothing selected, which means picking the variant again.

### Let simples inherit the description

Set **Use Parent Data for Simples** to include the description.

Descriptions are usually only filled in on the parent. Without inheritance every variant goes out with an empty description, which marketplaces treat as a thin listing.

Combine it with **Use Fallback only on Non Visible Simples** set to `Yes`: a simple that does have its own page keeps its own data, and only the invisible variants borrow from their parent.

---

## Keeping the feed honest

### Exclude out of stock products

Switch **Exclude Out of Stock** on.

A listing for something you cannot ship earns a cancellation and a mark against your account. The feed refreshes daily, so a product returns by itself once stock comes back.

### Do not filter on visibility unless you mean it

The **Visibility** filter is off by default, and for most shops that is right.

Switch it on and set it to "Catalog, Search" only, and you drop every variant of every configurable product — those are deliberately not visible on their own. Shops that do this tend to discover it when the marketplace reports no sizes available.

### Check your prices after a catalogue price rule

A configurable product is published at the cheapest price one of its variants actually sells for, which is what the product page shows.

Worth checking after you add a catalogue price rule: open the preview, pick a configurable, and compare its price to the product page in your shop. They should match.

---

## Categories

### Use the Magento category path

Leave **Type** on `Category` unless you have a reason not to.

The path is published deepest level first, so the marketplace can map it to its own tree. An attribute only pays off when you already maintain a separate marketplace taxonomy — and then you have to keep it up to date for every new product.

### Keep the category tree shallow where you can

Marketplaces match on the path. A product four levels deep in a tree nobody else uses is harder to place than one in a category that reads like the rest of the market.

---

## Running the feed

### Daily at midnight is enough

Leave **Auto Generate** on its default.

The marketplace reads your feed on its own schedule; generating more often than it reads only costs you server time. Generate by hand after a big catalogue change if you do not want to wait.

### Save before you generate

The **Generate** button runs with the settings that are stored, not the ones on your screen. Change something, save, then generate.

### Watch the first run of a large catalogue

A feed of a few thousand products takes seconds. If yours takes minutes, something is fetching more than it needs — usually a custom filter on an attribute without an index. Debug Mode shows where the time goes.

---

## Orders

### Set a fallback shipping method

Pick one under **Fallback for Shipping Method**.

The default method is not always available for the address on a marketplace order — a different country, a postcode your carrier does not serve. Without a fallback the import stops on that order.

### Decide who mails the buyer

**Send order e-mail** and **Send invoice e-mail** are off by default.

The marketplace mails the buyer about their order. A second mail from a shop they have never heard of tends to generate support questions rather than confidence. Switch them on only if you have agreed with Webshop.nl that you are the one communicating.

### Import customers if you want the relationship

With **Import Customers** on, a marketplace buyer becomes a customer account in your shop and you can see their history. Off, every order is a guest order.

Either is defensible. Worth knowing: marketplace buyers are the marketplace's customers first, and mailing them as if they are yours is usually against the terms.

---

## Scenarios

### One store, one country

Defaults do the job. Switch the extension on, switch the feed on, set Auto-Link, exclude out of stock, and you are done.

### Several store views

Each store view has its own feed, its own URL and its own settings.

Set the shared choices at default scope and only override what differs — usually the filename and the category filter. Generate each store view separately and hand each URL to the right Webshop.nl account.

A setting that is the same everywhere belongs at default scope. Overriding it per store view means changing it in three places later.

### Catalogue with many configurable products

The settings under **Product Types** matter more than anything else here.

Publish both, Auto-Link on, descriptions inherited, fallback limited to non-visible simples. Then open the preview and check a variant: it should carry its own size and colour, the parent's description, and a link that opens the parent with that variant selected.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
