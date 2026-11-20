# Aan de slag

Deze pagina loopt met je mee door de eerste keer dat je de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) gebruikt: aanzetten, je winkel koppelen aan de marktplaats, je eerste productfeed genereren en controleren of daar in staat wat je verwacht. Dit is de korte route. Elke instelling heeft een verstandige standaardwaarde, dus met een handvol keuzes sta je live en de rest stel je later af. Reken op vijf minuten voor een winkel die verder klaar is, plus de tijd die het genereren van een grote catalogus kost. Gedraagt iets zich anders dan hier staat, dan noemt de pagina over probleemoplossing de gebruikelijke oorzaken.

---

## Stap 1: Zet de extensie aan

Ga naar **Stores > Configuration > Webshop.nl > General**.

Zet **Enabled** op `Yes` en sla op.

Er verschijnen dan twee dingen:

- **Mode** — laat deze op `Production` staan. Hij bepaalt naar welke omgeving van Webshop.nl de registratieknop je stuurt.
- **Magento Integration Token** — aangemaakt tijdens de installatie. Webshop.nl gebruikt hem om orders in je winkel te zetten. Je hoeft er niets mee te doen; hij staat er zodat je kunt zien dat hij bestaat.

## Stap 2: Registreer je winkel

Klik op **Register now** in de blauwe balk. Die opent het partnerportaal van Webshop.nl en neemt je winkelgegevens mee, zodat het account daar aan deze winkel gekoppeld is.

## Stap 3: Zet de productfeed aan

Ga naar **Webshop.nl > Product Catalogue** en zet **Enable** op `Yes`.

Er verschijnen twee instellingen:

- **Filename** — de naam van het gegenereerde bestand. De standaard, `webshop.ndjson`, voldoet.
- **Auto Generate** — hoe vaak de feed wordt ververst. Dagelijks om middernacht is de standaard en past bij de meeste winkels.

Sla de configuratie op. **Opslaan vóór je genereert**, anders draait de run met de instellingen zoals ze waren.

## Stap 4: Genereer de feed

Scroll naar **Feeds**. Elke store view heeft zijn eigen regel.

Klik op **Generate** bij de store view die je wilt publiceren. Even later meldt de regel hoeveel producten erin zijn gegaan en wanneer hij draaide.

Klik op **Preview** om de eerste regels te zien zonder te downloaden, of op **Download** voor het bestand zelf.

## Stap 5: Controleer wat je publiceert

Open de preview en bekijk een paar regels:

- **productURL** moet de productpagina in je winkel openen
- **price** moet overeenkomen met wat een klant betaalt
- **availability** moet `in stock` zijn bij iets dat je kunt bestellen

Varianten van een configureerbaar product worden gepubliceerd op de pagina van hun ouder, omdat ze geen eigen pagina hebben. Dat hoort zo.

## Stap 6: Geef de URL door aan Webshop.nl

Elke regel in het Feeds-overzicht toont de `.ndjson`-URL van die store view. Kopieer de juiste naar je Webshop.nl-account.

Omdat browsers cachen: download het bestand in plaats van de URL te openen als je zeker wilt weten dat je naar de laatste versie kijkt.

---

## Orders aannemen

Orders staan los. Ga naar **Webshop.nl > Orders** en zet **Enable** op `Yes`.

Stel een **Default Shipping Method** in — de methode die je Magento-winkel gebruikt voor orders van de marktplaats. Kies ook een **Fallback**, die wordt gebruikt als de eerste niet beschikbaar is voor het adres op de order.

Staat dit uit, dan blijft je feed gewoon verschijnen maar worden orders geweigerd. Dat is een prima opzet als je alleen wilt adverteren.

## Hoe nu verder

Je feed draait nu vanzelf, één keer per dag. De moeite waard als je tijd hebt:

- **Product Types** bepaalt hoe configureerbare, bundel- en groepsproducten worden gepubliceerd — als ouder, als varianten, of allebei
- **Filter Options** houdt producten uit de feed: op zichtbaarheid, op categorie, of wat niet op voorraad is
- **Extra Fields** voegt een vaste waarde toe aan elk product, voor gegevens die de marktplaats wil en jouw catalogus niet heeft

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
