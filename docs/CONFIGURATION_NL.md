# Configuratie

Elke instelling die de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) kent, wat hij verandert en wanneer je eraan komt. De extensie staat onder **Stores > Configuration > Webshop.nl** en heeft drie secties: General voor de koppeling, Product Catalogue voor de feed en Orders voor wat er van de marktplaats terugkomt. Bijna alles is per store view in te stellen, dus een Nederlandse en een Belgische winkel kunnen andere gegevens publiceren uit dezelfde catalogus. Deze pagina volgt de volgorde van de beheerschermen. Zet je de extensie voor het eerst op, dan behandelt de pagina Aan de slag de handvol instellingen die je echt nodig hebt.

---

## General

### Enabled

Zet de extensie aan. Staat dit uit, dan draait er niets: geen feed, geen orderimport, en de andere secties blijven leeg.

Wordt uitgeleverd op `No`, zodat het installeren van de extensie niets verandert tot jij vindt dat het mag.

### Mode

`Production` of `Development`. Bepaalt naar welke omgeving van Webshop.nl de knop **Register now** wijst. Laat hem op `Production` staan, tenzij Webshop.nl je iets anders vraagt.

### Magento Integration Token

Alleen-lezen. Aangemaakt tijdens de installatie en gebruikt door Webshop.nl om orders via de REST API in je winkel te zetten. Hij staat er zodat je kunt zien dat hij bestaat.

---

## Product Catalogue

### General

| Instelling | Wat hij doet |
|---|---|
| **Enable** | Zet de feed aan voor deze store view. Uit betekent geen feed, en het overzicht meldt dat. |
| **Filename** | Naam van het gegenereerde bestand. Het store view-id wordt er automatisch aan toegevoegd, dus `webshop.ndjson` wordt `webshop-1.ndjson`. |
| **Auto Generate** | Hoe vaak de feed ververst. Standaard dagelijks om middernacht. Kies `Custom` om je eigen schema in crontab-formaat op te geven. |

De feed draait in zijn eigen cron-groep, zodat een lange run de rest van het ingeplande werk in Magento niet ophoudt.

### Product Data

Welk attribuut welk veld in de feed vult. De standaardwaarden passen bij een gewone Magento-installatie; verander ze als jouw winkel de gegevens ergens anders bijhoudt.

**General Fields** — Naam, Korte omschrijving en Lange omschrijving.

**Image Source** — welke afbeelding als hoofdafbeelding meegaat, en of de hele galerij ernaast wordt gepubliceerd.

**Additional Fields** — EAN-code, Merk, Kleur, Materiaal, Maat en Gender. Elk wijst naar een productattribuut. Laat er een leeg en het veld blijft uit de feed.

**Extra Info** — een vrij veld voor alles wat de marktplaats accepteert en niet in de velden hierboven past.

### Category

Hoe het categoriepad bij Webshop.nl terechtkomt.

- **Type `Category`** publiceert het Magento-categoriepad van het product, diepste niveau eerst.
- **Type `Attribute`** publiceert in plaats daarvan de waarde van een productattribuut, voor winkels die een aparte marktplaats-indeling bijhouden.

### Delivery

Levertijd per product, uit een attribuut of als vaste tekst. Aparte waarden voor op voorraad en niet op voorraad, zodat je "voor je besteld" kunt zeggen in plaats van een aantal dagen als iets niet op de plank ligt.

### VAT Percentage

Standaard uit. Zet hem aan om een btw-percentage per product te publiceren, uit een attribuut of als één vast percentage voor de hele catalogus.

### Product Types

Het stuk dat je twee keer moet lezen, want het bepaalt wat de marktplaats daadwerkelijk ziet.

Voor **configureerbare**, **bundel-** en **groepsproducten** kies je:

| Instelling | Wat hij doet |
|---|---|
| **Use ... Products** | Publiceer de ouder, de simpele producten, of allebei. "Both" is de gebruikelijke keuze: de ouder is de pagina waar een bezoeker landt, de simpele producten dragen de voorraad. |
| **Use Parent URL for Simples** | `No` houdt elk simpel product op zijn eigen pagina. `Yes` publiceert het op de pagina van de ouder. `Yes, with Auto-Link` zet de attribuutkeuze in de link, zodat bij aankomst de juiste variant geselecteerd is. |
| **Use Parent Image for Simples** | Neem de afbeelding van de ouder, houd die van het simpele product, gebruik die van de ouder alleen als het simpele product er geen heeft, of combineer ze. |
| **Use Parent Data for Simples** | Welke attributen een simpel product overneemt van zijn ouder — meestal de omschrijving, die doorgaans alleen bij de ouder is ingevuld. |
| **Use Fallback only on Non Visible Simples** | Beperkt bovenstaande overerving tot simpele producten die niet los zichtbaar zijn. Een simpel product met een eigen pagina houdt zijn eigen gegevens. |

**Groepsproducten** hebben er één extra: **Parent Price**, die bepaalt of de groep met zijn eigen prijs wordt gepubliceerd of met die van zijn goedkoopste onderdeel.

Een variant zonder eigen pagina wordt altijd op de pagina van zijn ouder gepubliceerd, wat deze instellingen ook zeggen. Een bezoeker naar een pagina sturen die niet bestaat heeft nooit zin.

### Additional Configuration

**Extra Fields** voegt een vast veld met waarde toe aan elk product in de feed. Handig voor gegevens die de marktplaats vraagt en jouw catalogus niet heeft — een merknaam bij een winkel met één merk, bijvoorbeeld.

**Shipping Prices** bepaalt wat er als bezorgkosten wordt gepubliceerd, zo nodig per land.

### Filter Options

Wat er buiten de feed blijft.

| Filter | Wat hij doet |
|---|---|
| **Visibility** | Publiceer alleen producten met de zichtbaarheden die je kiest. Standaard uit, dus alles wat door de andere filters komt gaat mee. |
| **Filter by Category** | Publiceer alleen de categorieën die je kiest, of juist alles behalve die. |
| **Exclude Out of Stock** | Houdt producten zonder voorraad uit de feed. |
| **Custom Filters** | Je eigen voorwaarden op elk attribuut: publiceer alleen wat voldoet, of houd buiten wat voldoet. |

### Feeds

Geen instelling maar het overzicht: één regel per store view, met de feed-URL, wanneer hij laatst draaide en hoeveel producten erin gingen.

Elke regel heeft **Generate** om hem nu te draaien, **Preview** om de eerste regels te zien en **Download** om het bestand op te halen.

Sla de configuratie op voordat je genereert. De run leest wat is opgeslagen, niet wat op je scherm staat.

---

## Orders

### Enable

Zet de orderimport aan voor deze store view. Staat dit uit, dan weigert de route orders en wordt er niets weggeschreven, terwijl de feed gewoon verschijnt. Dat is een prima opzet als je alleen op de marktplaats wilt adverteren.

### Shipping Method

**Default Shipping Method** is de methode die een marktplaatsorder in je winkel krijgt. **Fallback** wordt gebruikt als de eerste niet beschikbaar is voor het adres op de order.

### Customers

**Import Customers** bepaalt of een koper op de marktplaats een klantaccount in je winkel wordt of dat de order als gast wordt geplaatst. Staat hij aan, dan bepaalt **Customer Group** in welke groep ze terechtkomen.

### Address & Invoice

**Separate house number** zet het huisnummer op een eigen adresregel, voor winkels waarvan de adresindeling dat verwacht.

**Send order e-mail to the customer** en **Send invoice e-mail to the customer** bepalen of de mails van Magento zelf uitgaan bij marktplaatsorders. Allebei standaard uit — de marktplaats mailt de koper meestal zelf, en een tweede mail uit jouw winkel zorgt eerder voor verwarring.

---

## Debugging

**Debug Mode** schrijft weg wat de extensie doet, in een eigen logbestand. Laat hem in het dagelijks gebruik uit: hij is uitgebreid en het bestand groeit snel.

De knoppen **Debug Log** en **Error Log** tonen de laatste honderd regels zonder dat je op de server hoeft te kijken, en bieden ze aan als tekstbestand. Fouten worden altijd gelogd, of Debug Mode nu aanstaat of niet.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
