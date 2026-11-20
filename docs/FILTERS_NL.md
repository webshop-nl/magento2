# De feed filteren

Bepalen welke producten de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) publiceert en welke thuisblijven. Standaard draagt de feed je hele catalogus, en voor veel winkels klopt dat. Maar er zijn meestal producten die je liever niet adverteert: dingen die je niet kunt versturen, een opruimcategorie, artikelen onder een marge die je op een marktplaats acceptabel vindt. De extensie heeft vier manieren om producten buiten te houden, en ze stapelen — een product moet ze allemaal doorstaan. Deze pagina behandelt elk filter, wat het doet, en de valkuil waar de meeste winkels de eerste keer in lopen. Filters staan onder **Product Catalogue > Filter Options**.

---

## Visibility

Publiceer alleen producten waarvan de zichtbaarheid overeenkomt met wat je kiest.

**Dit is het filter waar mensen op stuklopen.** Het staat standaard uit, en bij de meeste winkels moet dat zo blijven.

Zet je hem aan met alleen "Catalog, Search", dan verdwijnt elke variant van elk configureerbaar product uit je feed. Varianten staan bewust op *Not Visible Individually* — zo werkt Magento. Het resultaat is advertenties zonder maten, en een marktplaats die meldt dat er niets op voorraad is.

Wil je dit filter toch, neem dan "Not Visible Individually" op in de gekozen waarden.

Zinvol gebruik: een catalogus met echt verborgen producten die je nooit wilt adverteren, waarbij je hebt gecontroleerd dat je varianten daar niet bij zitten.

---

## Filter by Category

Publiceer alleen bepaalde categorieën, of juist alles behalve bepaalde categorieën.

Twee standen:

- **In** — alleen producten in de categorieën die je kiest
- **Not in** — alles behalve die

Handig om een opruim- of personeelscategorie van de marktplaats te houden, of om alleen het assortiment te publiceren dat je met Webshop.nl hebt afgesproken.

Een product in meerdere categorieën komt erdoor zodra één daarvan voldoet.

---

## Exclude Out of Stock

Houdt producten zonder voorraad uit de feed.

**De moeite waard om aan te zetten.** Een advertentie voor iets dat je niet kunt versturen levert een annulering op en een aantekening bij je marktplaatsaccount.

De feed ververst dagelijks, dus een product komt vanzelf terug zodra de voorraad er weer is. Bij snellopende artikelen verkort vaker genereren dan één keer per dag de periode waarin de marktplaats voorraad kent die je niet meer hebt.

Voorraad betekent hier verkoopbare voorraad: wat op de plank ligt min wat gereserveerd is voor orders die nog niet verzonden zijn.

---

## Custom Filters

Je eigen voorwaarden, op elk productattribuut.

Elke voorwaarde is een attribuut, een vergelijking en een waarde. Zet de stand op alleen publiceren wat voldoet, of juist buitenhouden wat voldoet.

Voorbeelden die voorbijkomen:

- prijs boven een drempel, om artikelen met weinig marge van een marktplaats met commissie te houden
- een ja/nee-attribuut als `exclude_from_marketplace` dat je team per product zet
- een merk dat je buiten je eigen winkel niet mag verkopen

Voorwaarden combineren: een product moet aan alle voldoen.

Goed om te weten: een filter op een attribuut zonder index maakt het opbouwen van de feed trager. Gaat je run van seconden naar minuten nadat je er een toevoegt, dan is dat meestal de reden.

---

## Hoe de filters stapelen

Een product haalt de feed alleen als het elk filter doorstaat. Mist er iets, controleer ze dan in deze volgorde:

1. **Exclude Out of Stock** — is het op voorraad?
2. **Filter by Category** — zit het in een categorie die je publiceert?
3. **Visibility** — komt de zichtbaarheid overeen?
4. **Custom Filters** — voldoet het aan je voorwaarden?
5. **Product Types** — als het een variant is: worden simpele producten überhaupt gepubliceerd?

Die laatste is strikt genomen geen filter, maar houdt producten net zo effectief buiten. Staat **Use Configurable/Simple Products** op `Parent`, dan haalt geen enkele variant ooit de feed, wat de filters ook zeggen.

---

## Je werk controleren

Genereer na het wijzigen van een filter één keer de feed en kijk naar het aantal in het Feeds-overzicht. Vergelijk dat met wat je verwacht.

Met **Debug Mode** aan noemt het log elk product dat is overgeslagen en welke regel dat deed. Dat maakt van "er mist iets" een concreet antwoord, en het gaat een stuk sneller dan de filters met de hand aflopen.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
