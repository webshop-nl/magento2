# Orderimport

Wat er gebeurt als een marktplaatsorder via de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) in je winkel aankomt: hoe hij binnenkomt, wat er wordt gecontroleerd, wat Magento ervan maakt en wat de marktplaats terugkrijgt. Orders komen niet via de feed — Webshop.nl stuurt elke order naar je winkel zodra hij geplaatst is, en je winkel antwoordt of hij hem heeft aangenomen. Dat betekent dat een order geweigerd kan worden, en de reden doet ertoe: sommige weigeringen zijn opzet, andere wijzen op een instelling. Deze pagina behandelt de route, de controles, hoe een marktplaatsorder eruitziet als Magento-order en wat de marktplaats er achteraf over kan vragen.

---

## Hoe een order binnenkomt

Webshop.nl stuurt de order naar je winkel via de REST API van Magento, en meldt zich aan met het integratietoken dat bij de installatie is aangemaakt.

Je hoeft niets open te zetten of een webhook in te stellen. Het token staat onder **Webshop.nl > General**, zodat je kunt zien dat het bestaat.

Er zijn drie routes bij betrokken:

| Route | Waarvoor |
|---|---|
| `/V1/webshopnl/order/<store_id>` | Een order plaatsen |
| `/V1/webshopnl/status/<order_id>` | Vragen hoe het met een order staat |
| `/V1/webshopnl/info` | Je feed-URL's en instellingen uitlezen |

---

## Wat er wordt gecontroleerd

Voordat er iets wordt weggeschreven, wordt de order nagekeken op de dingen die Magento niet kan missen:

- een afleveradres, met e-mailadres, voornaam en achternaam
- een factuuradres
- minstens één product
- verzendkosten

Ontbreekt er een veld, dan wordt de order geweigerd en noemt het antwoord dat veld, zodat Webshop.nl het kan melden in plaats van jou te laten gissen.

Er gelden nog twee regels:

**De orderimport moet aanstaan.** Staat **Webshop.nl > Orders > Enable** uit, dan weigert de route alles. Je feed blijft gewoon verschijnen — een prima opzet als je alleen wilt adverteren.

**Dezelfde order wordt maar één keer weggeschreven.** Webshop.nl probeert het opnieuw na een hapering, en die tweede poging wordt geweigerd als *Order already imported*. Dat is opzet: het voorkomt dat één verkoop twee Magento-orders wordt.

---

## Wat Magento ervan maakt

Een gewone Magento-order, met een paar marktplaatsspecifieke zaken:

**Verzendmethode** — de **Default Shipping Method** die je onder Orders instelt. Is die methode niet beschikbaar voor het adres op de order, dan wordt de **Fallback** gebruikt. Stel er een in; een land dat je standaardvervoerder niet bedient laat de import anders vastlopen.

**Klant** — met **Import Customers** aan wordt de koper een klantaccount in de **Customer Group** die je hebt gekozen. Uit wordt de order als gastorder geplaatst.

**Adressen** — aflever- en factuuradres komen van de marktplaats. **Separate house number** zet het huisnummer op een eigen regel voor winkels waarvan de adresindeling dat verwacht.

**E-mails** — **Send order e-mail** en **Send invoice e-mail** staan standaard uit. De marktplaats mailt de koper zelf, en een tweede mail van een winkel die hij niet herkent levert eerder vragen op.

**Het marktplaats-ordernummer** wordt bij de order bewaard en als kolom in het ordergrid getoond, zodat je een Magento-order kunt terugleiden naar de marktplaats.

---

## Wat de marktplaats terugkrijgt

Bij een geslaagde import draagt het antwoord het Magento order-id en increment-id, zodat Webshop.nl in hun eigen systeem naar jouw order kan verwijzen.

Daarna kan de marktplaats de statusroute vragen hoe het met een order staat. Die antwoordt met:

- de huidige Magento-orderstatus
- of er een verzending bestaat
- de bezorggegevens, inclusief het track-and-tracenummer zodra je hebt verzonden

Zo komt de koper aan zijn track en trace: jij verzendt in Magento, en de marktplaats pikt het daar op.

---

## Een order volgen

**Sales > Orders** toont marktplaatsorders tussen de rest, met het Webshop.nl-ordernummer als kolom.

Met **Debug Mode** aan onder Debugging wordt elke binnenkomende order gelogd — de aangenomen en de geweigerde, met reden. De knop **Debug Log** toont de laatste honderd regels zonder dat je op de server hoeft te kijken.

Laat Debug Mode in het dagelijks gebruik uit; zet hem aan terwijl je iets uitzoekt.

---

## Als orders niet binnenkomen

Controleer in deze volgorde:

1. **Orders > Enable** — staat de import aan voor deze store view?
2. **General > Enabled** — staat de extensie überhaupt aan?
3. **Magento Integration Token** — staat die onder General?
4. De beheerdersrol die de integratie gebruikt heeft toegang tot de Webshop.nl-onderdelen nodig.

Kloppen die vier en komen er nog steeds geen orders, dan laat Debug Mode zien of ze binnenkomen en worden geweigerd, of helemaal niet binnenkomen. Dat is het verschil tussen een instelling aan jouw kant en iets om bij Webshop.nl aan te kaarten.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
