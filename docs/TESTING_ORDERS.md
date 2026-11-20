# Testing Orders

How to confirm that the order side of the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html) works before real orders depend on it, and how to find out what went wrong when one does not land. The feed side is easy to check: you generate it and look at the file. Orders are harder, because they arrive from outside and you only notice a problem when a sale does not show up. This page covers what you can verify yourself, what the marketplace has to do on their end, and how to read the result. Do this on a staging shop where possible — every successful test leaves a real Magento order behind.

---

## Before you test

Four things have to be true:

1. **Webshop.nl > General > Enabled** is `Yes`
2. **Webshop.nl > Orders > Enable** is `Yes`
3. A **Magento Integration Token** is shown under General
4. A **Default Shipping Method** and a **Fallback** are set

Switch **Debug Mode** on under Debugging while you test. Every incoming order is logged, accepted or refused, with the reason.

---

## Checking the connection

The extension answers a route that reports its own state:

```
GET /rest/V1/webshopnl/info
```

It returns whether the extension is enabled, which version is installed, your feed URLs per store view, the addresses Webshop.nl should post orders to, and when the last order came in.

That last value is the quickest answer to "is anything arriving at all". If it is empty, no order has ever been imported.

This route needs the integration token, so it is Webshop.nl who calls it, not you in a browser.

---

## Placing a test order

A test order comes from Webshop.nl's side. Ask them to place one against your store; they have the token and the address.

What has to be in it for the import to succeed:

- a delivery address with an e-mail address, a first name and a surname
- a billing address
- at least one product, matched on SKU
- a shipping cost

Leave one of those out and the order is refused with a message naming the field. That is deliberate — it means a refusal tells you what to fix.

---

## Reading the result

**Sales > Orders** shows the order alongside the rest, with the Webshop.nl order id as its own column.

Check these:

| What | Should be |
|---|---|
| Products | The SKUs from the marketplace order |
| Prices | What the buyer paid, per line |
| Shipping cost | The amount the marketplace sent |
| Totals | Line totals and the grand total, not zero |
| Addresses | Delivery and billing as supplied |
| Customer | An account in your chosen group, or a guest order |

If totals are zero while the line price is right, raise it with support — that pattern had a cause and it was fixed in 1.2.0, so it should not come back.

---

## Testing the status route

Once an order exists, the marketplace can ask how it is doing:

```
GET /rest/V1/webshopnl/status/<order_id>
```

It answers with the Magento order status, whether a shipment exists, and the delivery data including the tracking number.

To test it end to end: ship the order in Magento with a tracking number, then have Webshop.nl ask for the status. The tracking number should come back. That is the route the buyer's track and trace depends on.

---

## Testing a refusal

Worth doing once, so you recognise it later.

Have Webshop.nl send the same order twice. The second attempt is refused with *Order already imported*, and no second Magento order appears.

That is intentional: Webshop.nl retries after a network hiccup, and this stops one sale becoming two orders.

---

## When a test fails

**Nothing arrives at all.** Check the four prerequisites above. Then check the admin role the integration uses: it needs access to the Webshop.nl resources, and without it the route refuses before the extension sees anything.

**The order is refused.** The answer names the field that is missing. Debug Mode logs the same thing on your side.

**The import stops on the shipping method.** The default method is not available for that address. Set a fallback.

**The order arrives but looks wrong.** Compare the Magento order with what the marketplace sent. Prices, shipping cost and addresses come across as supplied; if they differ, the log shows what was received.

---

## After testing

Switch **Debug Mode** back off. It is verbose and the log grows quickly.

Delete or cancel the test orders if you ran them on a live shop, so they do not end up in your reporting.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
