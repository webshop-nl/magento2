# CLI Commando's

Commandoregel-hulpmiddelen voor de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html), om de feed met de hand te genereren, hem in een script op te nemen, of een run te zien gebeuren terwijl je uitzoekt waarom iets er niet goed uitziet. De feed draait op zijn eigen schema, dus de meeste winkels hebben deze commando's nooit nodig, maar ze komen van pas na een grote catalogusimport of als je de feed als laatste stap van een nachtelijke taak wilt verversen. Het commando doet hetzelfde werk als de knop Generate in de beheeromgeving, voor elke store view waarvoor de feed aanstaat.

---

## Beschikbare commando's

| Commando | Omschrijving |
|---------|-------------|
| `webshopnl:feed:create` | Genereer de productfeed |

---

## webshopnl:feed:create

Genereert de Webshop.nl-productfeed voor elke store view waarvoor de feed aanstaat. Dit is dezelfde generatie die via cron draait.

```bash
# Genereer de feed voor alle ingeschakelde store views
bin/magento webshopnl:feed:create

# Genereer de feed voor één store view
bin/magento webshopnl:feed:create --store-id=1
```

**Opties:**

| Optie | Omschrijving |
|--------|-------------|
| `--store-id=<id>` | Genereer de feed alleen voor deze store view |

---

## Wanneer gebruik je dit

De feed draait op zijn eigen schema, dus de commandoregel heb je zelden nodig. Hij bewijst zijn nut als:

- je net veel producten hebt gewijzigd en niet op de volgende geplande run wilt wachten
- je een catalogusimport scriptt en de feed als laatste stap wilt verversen
- er iets niet klopt en je een run wilt zien gebeuren, met Debug Mode aan

Het commando doet precies wat de knop **Generate** in de beheeromgeving doet, voor elke store view waarvoor de feed aanstaat.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
