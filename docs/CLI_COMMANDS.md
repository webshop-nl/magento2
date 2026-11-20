# CLI Commands

Command line tools for the [Webshop.nl extension for Magento 2](https://www.magmodules.eu/magento2-webshop-marketplace.html), for generating the feed by hand, wiring it into a script, or watching a run happen while you work out why something looks wrong. The feed runs on its own schedule, so most shops never need these commands, but they come in useful after a large catalogue import or when you want the feed refreshed as the last step of a nightly job. The command does the same work as the Generate button in the admin, for every store view that has the feed switched on.

---

## Available Commands

| Command | Description |
|---------|-------------|
| `webshopnl:feed:create` | Generate the product feed |

---

## webshopnl:feed:create

Generates the WebshopNL product feed for every store view that has the feed enabled. This is the same generation that runs via cron.

```bash
# Generate the feed for all enabled store views
bin/magento webshopnl:feed:create

# Generate the feed for one store view only
bin/magento webshopnl:feed:create --store-id=1
```

**Options:**

| Option | Description |
|--------|-------------|
| `--store-id=<id>` | Generate the feed for this store view only |

---

## When to use these

The feed runs on its own schedule, so you rarely need the command line. It earns its keep when:

- you have just changed a lot of products and do not want to wait for the next scheduled run
- you are scripting a catalogue import and want the feed refreshed as the last step
- something looks wrong and you want to watch a run happen, with Debug Mode on

The command does exactly what the **Generate** button in the admin does, for every store view that has the feed enabled.

---

## Need More Help?

**Documentation:**
- [All Help Articles](https://www.magmodules.eu/help/magento2-webshop-marketplace.html) - Complete documentation overview

**Support:**
- [Contact Support](https://www.magmodules.eu/support) - Get help from our team
