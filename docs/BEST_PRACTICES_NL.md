# Best Practices

Keuzes die goed uitpakken met de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html), en een paar die verstandig lijken maar je omzet kosten. Het komt vooral neer op twee vragen: wat ziet een bezoeker als hij op je advertentie klikt, en klopt je voorraadaantal. Deze pagina gaat over de afwegingen achter de instellingen, niet over de instellingen zelf — die staan in de configuratiereferentie. Lees hem als je feed draait en je wilt weten of hij ook goed draait. De scenario's aan het eind behandelen de winkelvormen die we het meest zien: één winkel, meerdere store views, en een catalogus die vooral uit configureerbare producten bestaat.

---

## Varianten publiceren

### Publiceer zowel de ouder als de simpele producten

Zet bij configureerbare producten **Use Configurable/Simple Products** op `Both`.

De ouder is de pagina waar een bezoeker landt en die de omschrijving en de categorie draagt. De simpele producten dragen maat, kleur en voorraad. Publiceer je alleen de ouder, dan kan de marktplaats niet zien welke maat beschikbaar is; publiceer je alleen de simpele producten, dan verlies je de pagina die het product verkoopt.

### Stuur varianten naar de ouderpagina met Auto-Link

Zet **Use Parent URL for Simples** op `Yes, with Auto-Link`.

Een variant heeft in Magento meestal geen eigen pagina — hij is niet los zichtbaar. Auto-Link publiceert de pagina van de ouder met de attribuutkeuze in de link, zodat iemand die op "blauw, maat M" klikt aankomt bij het product met blauw en M al geselecteerd.

`Yes` zonder Auto-Link stuurt ze naar de ouderpagina zonder selectie, wat betekent dat ze de variant opnieuw moeten kiezen.

### Laat simpele producten de omschrijving overerven

Zet **Use Parent Data for Simples** zo dat de omschrijving meekomt.

Omschrijvingen staan meestal alleen bij de ouder. Zonder overerving gaat elke variant met een lege omschrijving de deur uit, en dat telt bij marktplaatsen als een magere advertentie.

Combineer het met **Use Fallback only on Non Visible Simples** op `Yes`: een simpel product dat wél een eigen pagina heeft houdt zijn eigen gegevens, en alleen de onzichtbare varianten lenen van hun ouder.

---

## De feed eerlijk houden

### Sluit producten zonder voorraad uit

Zet **Exclude Out of Stock** aan.

Een advertentie voor iets dat je niet kunt versturen levert een annulering op en een aantekening bij je account. De feed ververst dagelijks, dus een product komt vanzelf terug zodra de voorraad er weer is.

### Filter niet op zichtbaarheid tenzij je het meent

Het **Visibility**-filter staat standaard uit, en voor de meeste winkels is dat juist.

Zet je hem aan op alleen "Catalog, Search", dan verdwijnt elke variant van elk configureerbaar product — die zijn bewust niet los zichtbaar. Winkels die dit doen ontdekken het meestal als de marktplaats meldt dat er geen maten beschikbaar zijn.

### Controleer je prijzen na een catalogusprijsregel

Een configureerbaar product wordt gepubliceerd tegen de goedkoopste prijs waarvoor een van zijn varianten echt te koop is, en dat is wat de productpagina toont.

De moeite waard om na te kijken nadat je een catalogusprijsregel toevoegt: open de preview, pak een configureerbaar product en vergelijk de prijs met de productpagina in je winkel. Die horen gelijk te zijn.

---

## Categorieën

### Gebruik het Magento-categoriepad

Laat **Type** op `Category` staan tenzij je een reden hebt om dat niet te doen.

Het pad wordt diepste niveau eerst gepubliceerd, zodat de marktplaats het op zijn eigen boom kan leggen. Een attribuut loont pas als je al een aparte marktplaats-indeling bijhoudt — en dan moet je die voor elk nieuw product bijhouden.

### Houd de categorieboom ondiep waar het kan

Marktplaatsen matchen op het pad. Een product dat vier niveaus diep zit in een boom die niemand anders gebruikt is lastiger te plaatsen dan een product in een categorie die leest zoals de rest van de markt.

---

## De feed draaien

### Dagelijks om middernacht is genoeg

Laat **Auto Generate** op de standaardwaarde staan.

De marktplaats leest je feed op zijn eigen moment; vaker genereren dan hij leest kost alleen servertijd. Genereer handmatig na een grote catalogusaanpassing als je niet wilt wachten.

### Sla op vóór je genereert

De knop **Generate** draait met de instellingen die zijn opgeslagen, niet met die op je scherm. Verander iets, sla op, genereer dan.

### Let op de eerste run van een grote catalogus

Een feed van een paar duizend producten duurt seconden. Duurt die van jou minuten, dan haalt er iets meer op dan nodig — meestal een eigen filter op een attribuut zonder index. Debug Mode laat zien waar de tijd blijft.

---

## Orders

### Stel een terugvaloptie voor verzending in

Kies er een onder **Fallback for Shipping Method**.

De standaardmethode is niet altijd beschikbaar voor het adres op een marktplaatsorder — een ander land, een postcode die je vervoerder niet bedient. Zonder terugvaloptie loopt de import op die order vast.

### Bepaal wie de koper mailt

**Send order e-mail** en **Send invoice e-mail** staan standaard uit.

De marktplaats mailt de koper over zijn bestelling. Een tweede mail van een winkel waar hij nog nooit van gehoord heeft levert eerder vragen op dan vertrouwen. Zet ze alleen aan als je met Webshop.nl hebt afgesproken dat jij degene bent die communiceert.

### Importeer klanten als je de relatie wilt

Met **Import Customers** aan wordt een koper op de marktplaats een klantaccount in je winkel en zie je hun historie. Uit is elke order een gastorder.

Allebei verdedigbaar. Goed om te weten: kopers op een marktplaats zijn in de eerste plaats klant van die marktplaats, en ze mailen alsof ze van jou zijn is meestal in strijd met de voorwaarden.

---

## Scenario's

### Eén winkel, één land

De standaardwaarden volstaan. Extensie aan, feed aan, Auto-Link aan, niet-op-voorraad uitsluiten, en je bent klaar.

### Meerdere store views

Elke store view heeft zijn eigen feed, zijn eigen URL en zijn eigen instellingen.

Zet de gedeelde keuzes op het standaardniveau en overschrijf alleen wat afwijkt — meestal de bestandsnaam en het categoriefilter. Genereer elke store view apart en geef elke URL aan het juiste Webshop.nl-account.

Een instelling die overal hetzelfde is hoort op het standaardniveau. Hem per store view overschrijven betekent dat je hem later op drie plekken moet aanpassen.

### Catalogus met veel configureerbare producten

De instellingen onder **Product Types** doen hier meer dan al het andere.

Publiceer beide, Auto-Link aan, omschrijvingen overerven, terugval beperkt tot niet-zichtbare simpele producten. Open daarna de preview en bekijk een variant: die hoort zijn eigen maat en kleur te dragen, de omschrijving van de ouder, en een link die de ouder opent met die variant geselecteerd.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
