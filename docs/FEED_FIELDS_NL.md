# Feedvelden

Elk veld dat de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) in de productfeed schrijft, wat erin staat en waar het vandaan komt. Handig als de marktplaats vraagt naar een waarde die je niet herkent, als je beslist waar je een instelling naar laat wijzen, of als je de feed wilt nakijken voordat je de URL doorgeeft. De feed is NDJSON: één JSON-object per regel, één regel per product, zonder omhulsel eromheen. Dat maakt hem makkelijk leesbaar met gewone hulpmiddelen — `head` toont je de eerste producten, en elke JSON-viewer neemt één regel aan. Velden zonder waarde blijven uit de regel in plaats van leeg te worden geschreven.

---

## Identificatie

| Veld | Bevat | Komt van |
|---|---|---|
| `ID` | Het Magento product-id | Het product zelf |
| `sku` | De SKU | Het product zelf |
| `family_ID` | Het id van de ouder, bij een variant | Het configureerbare, bundel- of groepsproduct waar het bij hoort |

Een product met een `family_ID` is een variant. Eén zonder staat op zichzelf. De marktplaats gebruikt dit om maten en kleuren onder één advertentie te groeperen.

## Tekst

| Veld | Bevat | Ingesteld onder |
|---|---|---|
| `name` | Productnaam | Product Data > General Fields > Name |
| `description` | Korte omschrijving | Product Data > General Fields > Short Description |
| `descriptionLong` | Lange omschrijving | Product Data > General Fields > Long Description |

Elk wijst naar een productattribuut, zodat een winkel die zijn marktplaatsteksten in een apart attribuut bijhoudt die kan publiceren in plaats van de webshoptekst.

## Links en afbeeldingen

| Veld | Bevat |
|---|---|
| `productURL` | De pagina waar een bezoeker landt |
| `imageURL` | De hoofdafbeelding |
| `additionalImages` | De rest van de galerij |

Een variant heeft in Magento geen eigen pagina, dus die wordt gepubliceerd op de pagina van zijn ouder. Met Auto-Link aan draagt de link de attribuutkeuze mee, en zijn de juiste maat en kleur bij aankomst geselecteerd.

## Prijs

| Veld | Bevat |
|---|---|
| `fromPrice` | De prijs vóór korting |
| `price` | Wat een klant vandaag betaalt |
| `discount` | Het verschil als percentage |
| `vat_pct` | Het btw-tarief, als je dat publiceert |

`price` volgt catalogusprijsregels en speciale prijzen. Bij een configureerbaar product is het de goedkoopste prijs waarvoor een van zijn varianten echt te koop is, en dat is wat de productpagina toont.

`vat_pct` staat standaard uit; zet hem aan onder Product Data > VAT Percentage.

## Voorraad en levering

| Veld | Bevat |
|---|---|
| `qty` | De verkoopbare voorraad |
| `availability` | Of het besteld kan worden |
| `deliveryTime` | Hoe lang levering duurt |
| `deliveryCosts` | Wat levering kost |

`qty` is de verkoopbare voorraad, niet het ruwe voorraadaantal: het is wat op de plank ligt min wat gereserveerd is voor orders die nog niet verzonden zijn. Tien op voorraad met acht besteld publiceert als twee.

`deliveryTime` heeft een aparte waarde voor op voorraad en niet op voorraad, zodat je "voor je besteld" kunt zeggen in plaats van een aantal dagen.

## Productattributen

| Veld | Ingesteld onder |
|---|---|
| `EAN` | Product Data > Additional Fields > EAN-code |
| `brand` | Product Data > Additional Fields > Brand |
| `color` | Product Data > Additional Fields > Color |
| `material` | Product Data > Additional Fields > Material |
| `size` | Product Data > Additional Fields > Size |

Laat er een wijzen naar een attribuut dat je catalogus niet vult en het veld blijft uit de feed. Dat is beter dan een lege waarde, die marktplaatsen vaak lezen als een magere advertentie.

## Categorieën

| Veld | Bevat |
|---|---|
| `categoryPath` | Het volledige pad, diepste niveau eerst |
| `categories` | Het bovenste niveau |
| `subcategories` | Het tweede niveau |
| `subsubcategories` | Het derde niveau |

Standaard gepubliceerd uit de Magento-categorieboom. Zet Product Data > Category > Type op `Attribute` om in plaats daarvan een productattribuut te publiceren, voor winkels met een aparte marktplaats-indeling.

## Vrije velden

| Veld | Bevat |
|---|---|
| `extra_info` | Alles wat de marktplaats accepteert en nergens anders past |

Extra Fields onder Additional Configuration voegt een vast veld met waarde toe aan elk product — handig voor een merknaam bij een winkel met één merk, of elke waarde die de marktplaats verwacht en je catalogus niet heeft.

---

## Het bestand lezen

Open het Feeds-overzicht onder Product Catalogue. Elke store view heeft **Preview** voor de eerste regels en **Download** voor het hele bestand.

Gebruik Download in plaats van de URL als je zeker wilt weten dat je naar de laatste run kijkt: de URL verandert niet tussen runs, dus browsers tonen je vaak de vorige.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
