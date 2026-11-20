# CLI Commands

Command line tools for the WebshopNL Connect extension, for manual feed generation, automation scripts and troubleshooting.

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
