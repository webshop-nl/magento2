# Probleemoplossing

Wat je kunt nakijken als de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) niet doet wat je verwacht: een lege feed, producten die buiten blijven, links die nergens heen gaan, orders die niet binnenkomen. Elk punt hieronder noemt wat je ziet, waar het vandaan komt en wat je verandert, met een aantekening hoe je het voorkomt. Begin bij het symptoom dat op jouw situatie lijkt in plaats van van boven naar beneden te lezen. Past er niets bij, dan vertellen Debug Mode en de logknoppen onder Debugging je meestal meer dan gokken, en ons supportteam kan vanaf daar met je meekijken.

---

## De feed

### Het overzicht zegt "Feed disabled"

**Wat je ziet:** bij elke store view staat *Feed disabled. Set Enable to Yes under General to generate it.*

**Waarom:** de feed staat uit voor die store view. Let op: dit is de **Enable** onder Product Catalogue > General, niet die onder Webshop.nl > General — de extensie kan aanstaan terwijl de feed uitstaat.

**Oplossing:** zet Product Catalogue > General > **Enable** op `Yes` en sla op.

### Generate doet niets, of het aantal blijft nul

**Waarom:** je filters sluiten alles uit. De gebruikelijke oorzaak is het **Visibility**-filter op "Catalog, Search" in een catalogus die vooral uit configureerbare producten bestaat, want varianten zijn bewust niet los zichtbaar.

**Oplossing:** zet het Visibility-filter uit, of voeg "Not Visible Individually" toe aan de gekozen waarden. Genereer opnieuw en kijk naar het aantal.

**Voorkomen:** genereer één keer na het wijzigen van een filter en vergelijk het aantal met wat je verwacht.

### Er ontbreken producten in de feed

Loop de filters in deze volgorde na:

1. **Exclude Out of Stock** — is het product op voorraad?
2. **Filter by Category** — zit het in een categorie die je publiceert?
3. **Visibility** — komt de zichtbaarheid overeen met wat je hebt gekozen?
4. **Custom Filters** — voldoet het aan je eigen voorwaarden?
5. **Product Types** — als het een variant is: worden simpele producten überhaupt gepubliceerd?

Met Debug Mode aan noemt het log elk product dat is overgeslagen en welke regel dat deed.

### De feed toont oude gegevens

**Waarom:** je browser heeft het bestand gecachet. De URL verandert niet tussen runs, dus de browser ziet geen reden om opnieuw op te halen.

**Oplossing:** gebruik de knop **Download** in plaats van de URL te openen. Die haalt altijd het huidige bestand op.

### Prijzen komen niet overeen met de productpagina

**Waarom:** meestal een catalogusprijsregel. Een configureerbaar product wordt gepubliceerd tegen de goedkoopste prijs waarvoor een van zijn varianten echt te koop is.

**Oplossing:** open de preview, zoek het product, vergelijk het met de productpagina. Verschillen ze, herindexeer dan (`bin/magento indexer:reindex`) en genereer opnieuw — de feed leest de prijsindex.

**Voorkomen:** controleer een paar configureerbare producten nadat je een catalogusprijsregel toevoegt of wijzigt.

### Alleen de eerste store view krijgt een feed

**Waarom:** bij de andere store views staat de feed uit, of de feed is maar voor één store view gegenereerd.

**Oplossing:** controleer **Enable** op het niveau van elke store view en klik op Generate bij elke regel. Elke store view heeft een eigen bestand en een eigen URL.

---

## Links in de feed

### Een productlink geeft een 404

**Waarom:** het product heeft geen eigen URL. Varianten van een configureerbaar product zijn meestal niet los zichtbaar, dus geeft Magento ze geen pagina.

**Oplossing:** geen — de extensie publiceert zo'n product zelf op de pagina van zijn ouder.

Zie je toch een 404, dan heeft het product ook geen ouder: het is onzichtbaar en staat op zichzelf. Maak het zichtbaar, geef het een ouder, of houd het met een filter uit de feed.

**Voorkomen:** controleer na een catalogusimport of er producten zijn binnengekomen zonder ouder en zonder zichtbaarheid.

### Links wijzen naar de verkeerde store view

**Waarom:** "Add Store Code to Urls" staat aan en de links zijn zonder die code opgebouwd.

**Oplossing:** genereer opnieuw. De feed zet de store code erin als je winkel die gebruikt.

### Variantlinks hebben een hash zoals `#93=49`

Dat is **Auto-Link**, en dat hoort zo: hij opent de ouderpagina met die variant geselecteerd.

Wil je dat niet? Zet **Use Parent URL for Simples** dan op `Yes` in plaats van `Yes, with Auto-Link`.

---

## Voorraad

### De marktplaats verkoopt iets dat niet op voorraad is

**Waarom:** de feed is een momentopname. Tussen twee runs kan je voorraad opraken.

**Oplossing:** zet **Exclude Out of Stock** aan, zodat een product uit de feed verdwijnt zodra het op is.

**Voorkomen:** genereer vaker dan één keer per dag bij snellopende artikelen.

### Het gepubliceerde aantal is hoger dan wat je hebt

**Waarom:** het aantal in de feed is de verkoopbare voorraad — voorraad min wat gereserveerd is voor orders die nog niet verzonden zijn.

**Oplossing:** bekijk het product in Magento. Komt de verkoopbare voorraad daar overeen met de feed, dan klopt de feed en klopte de verwachting niet.

---

## Orders

### Orders komen niet binnen

Controleer in deze volgorde:

1. **Webshop.nl > Orders > Enable** — staat de orderimport aan voor deze store view?
2. **Webshop.nl > General > Enabled** — staat de extensie überhaupt aan?
3. **Magento Integration Token** — staat die onder General? Zonder token kan Webshop.nl zich niet aanmelden.
4. De beheerdersrol die de integratie gebruikt heeft toegang tot de Webshop.nl-onderdelen nodig.

Met Debug Mode aan wordt elke binnenkomende order gelogd, ook de geweigerde en waarom.

### Een order wordt geweigerd als "already imported"

Dat is opzet: dezelfde marktplaatsorder wordt maar één keer weggeschreven. Webshop.nl probeert het opnieuw na een hapering, en dit voorkomt dat je twee Magento-orders voor één verkoop krijgt.

### De import loopt vast op de verzendmethode

**Waarom:** de standaard verzendmethode is niet beschikbaar voor het adres op de order.

**Oplossing:** stel een **Fallback for Shipping Method** in onder Orders > Shipping Method.

### Een order komt binnen zonder klantaccount

**Waarom:** **Import Customers** staat uit, dus marktplaatsorders worden als gastorder geplaatst.

**Oplossing:** zet hem aan en kies een **Customer Group**. Bestaande orders blijven zoals ze zijn.

---

## Logs

### Uitzoeken wat er gebeurd is

**Stores > Configuration > Webshop.nl > General > Debugging** heeft een knop **Debug Log** en een knop **Error Log**. Elk toont de laatste honderd regels en biedt ze aan als tekstbestand.

Fouten worden altijd gelogd. Debug Mode voegt de rest toe: welke producten zijn overgeslagen, welke orders binnenkwamen, hoe lang een run duurde.

Laat Debug Mode in het dagelijks gebruik uit — hij is uitgebreid en het bestand groeit snel. Zet hem aan terwijl je iets uitzoekt, en daarna weer uit.

### Na een upgrade werkt iets niet meer

Draai `bin/magento setup:upgrade` en `bin/magento cache:flush`. Draait je winkel in productiemodus, zet dan ook de statische content opnieuw klaar.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
