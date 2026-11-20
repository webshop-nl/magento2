# Order Import

What happens when a marketplace order reaches your store through the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html): how it arrives, what is checked, what Magento ends up with and what the marketplace gets back. Orders do not come in over the feed — Webshop.nl posts each one to your shop as it is placed, and your shop answers whether it accepted it. That means an order can be refused, and the reason matters: some refusals are intentional, others point at a setting. This page covers the route, the checks it runs, how a marketplace order looks once it is a Magento order, and what the marketplace can ask about it afterwards.

---

## How an order arrives

Webshop.nl posts the order to your shop over the Magento REST API, authenticating with the integration token that was created when the extension was installed.

You do not have to open anything up or configure a webhook. The token is shown under **Webshop.nl > General** so you can confirm it exists.

Three routes are involved:

| Route | Used for |
|---|---|
| `/V1/webshopnl/order/<store_id>` | Placing an order |
| `/V1/webshopnl/status/<order_id>` | Asking how an order is doing |
| `/V1/webshopnl/info` | Reading your feed URLs and settings |

---

## What is checked

Before anything is written, the order is checked for the things Magento cannot do without:

- a delivery address, with an e-mail address, a first name and a surname
- a billing address
- at least one product
- a shipping cost

A missing field means the order is refused and the answer names the field, so Webshop.nl can report it rather than leaving you guessing.

Two more rules apply:

**The order import has to be on.** With **Webshop.nl > Orders > Enable** off, the route refuses everything. Your feed keeps being published — a valid setup if you only want to advertise.

**The same order is only written once.** Webshop.nl retries after a hiccup, and the second attempt is refused as *Order already imported*. That is deliberate: it stops one sale becoming two Magento orders.

---

## What Magento ends up with

A normal Magento order, with a few marketplace specifics:

**Shipping method** — the **Default Shipping Method** you set under Orders. When that method is not available for the address on the order, the **Fallback** is used. Set one; a country your default carrier does not serve otherwise stops the import.

**Customer** — with **Import Customers** on, the buyer becomes a customer account in the **Customer Group** you chose. Off, the order is placed as a guest order.

**Addresses** — delivery and billing come from the marketplace. **Separate house number** puts the house number on its own line for shops whose address layout expects that.

**E-mails** — **Send order e-mail** and **Send invoice e-mail** are off by default. The marketplace mails the buyer itself, and a second mail from a shop they do not recognise tends to cause support questions.

**The marketplace order id** is stored on the order and shown as a column in the sales order grid, so you can tie a Magento order back to the marketplace.

---

## What the marketplace gets back

On a successful import, the answer carries the Magento order id and increment id, so Webshop.nl can refer to your order in their own system.

Afterwards the marketplace can ask the status route how an order is doing. That answers with:

- the current Magento order status
- whether a shipment exists
- the delivery data, including the tracking number once you have shipped

That is how the buyer gets their track and trace: you ship in Magento, and the marketplace picks it up from there.

---

## Following an order

**Sales > Orders** shows marketplace orders alongside the rest, with the Webshop.nl order id as a column.

With **Debug Mode** on under Debugging, every incoming order is logged — the accepted ones and the refused ones, with the reason. The **Debug Log** button shows the last hundred records without server access.

Leave Debug Mode off in everyday use; switch it on while you are investigating.

---

## When orders do not arrive

Check in this order:

1. **Orders > Enable** — is the import on for this store view?
2. **General > Enabled** — is the extension on at all?
3. **Magento Integration Token** — is it shown under General?
4. The admin role the integration uses needs access to the Webshop.nl resources.

If all four are right and orders still do not land, Debug Mode will show whether they are arriving and being refused, or not arriving at all. That is the difference between a setting on your side and something to raise with Webshop.nl.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
