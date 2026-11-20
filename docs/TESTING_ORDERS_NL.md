# Orders testen

Hoe je bevestigt dat de orderkant van de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) werkt voordat er echte orders van afhangen, en hoe je uitzoekt wat er misging als er een niet binnenkomt. De feedkant is makkelijk te controleren: je genereert hem en kijkt in het bestand. Orders zijn lastiger, want die komen van buiten en je merkt het pas als een verkoop niet opduikt. Deze pagina behandelt wat je zelf kunt nagaan, wat de marktplaats aan hun kant moet doen, en hoe je het resultaat leest. Doe dit waar mogelijk op een testomgeving — elke geslaagde test laat een echte Magento-order achter.

---

## Voordat je test

Vier dingen moeten kloppen:

1. **Webshop.nl > General > Enabled** staat op `Yes`
2. **Webshop.nl > Orders > Enable** staat op `Yes`
3. Er staat een **Magento Integration Token** onder General
4. Er is een **Default Shipping Method** en een **Fallback** ingesteld

Zet **Debug Mode** aan onder Debugging terwijl je test. Elke binnenkomende order wordt gelogd, aangenomen of geweigerd, met reden.

---

## De verbinding controleren

De extensie beantwoordt een route die zijn eigen toestand meldt:

```
GET /rest/V1/webshopnl/info
```

Die geeft terug of de extensie aanstaat, welke versie is geïnstalleerd, je feed-URL's per store view, de adressen waar Webshop.nl orders naartoe moet sturen, en wanneer de laatste order binnenkwam.

Die laatste waarde is het snelste antwoord op "komt er überhaupt iets binnen". Is hij leeg, dan is er nooit een order geïmporteerd.

Deze route heeft het integratietoken nodig, dus Webshop.nl roept hem aan, niet jij in een browser.

---

## Een testorder plaatsen

Een testorder komt van de kant van Webshop.nl. Vraag of ze er een tegen jouw winkel plaatsen; zij hebben het token en het adres.

Wat erin moet zitten om de import te laten slagen:

- een afleveradres met e-mailadres, voornaam en achternaam
- een factuuradres
- minstens één product, gematcht op SKU
- verzendkosten

Laat er een weg en de order wordt geweigerd met een bericht dat het veld noemt. Dat is opzet — het betekent dat een weigering je vertelt wat je moet repareren.

---

## Het resultaat lezen

**Sales > Orders** toont de order tussen de rest, met het Webshop.nl-ordernummer als eigen kolom.

Controleer dit:

| Wat | Hoort te zijn |
|---|---|
| Producten | De SKU's uit de marktplaatsorder |
| Prijzen | Wat de koper betaalde, per regel |
| Verzendkosten | Het bedrag dat de marktplaats stuurde |
| Totalen | Regeltotalen en het eindtotaal, niet nul |
| Adressen | Aflever- en factuuradres zoals aangeleverd |
| Klant | Een account in de gekozen groep, of een gastorder |

Zijn de totalen nul terwijl de regelprijs klopt, meld het dan bij support — dat patroon had een oorzaak en is in 1.2.0 verholpen, dus het hoort niet terug te komen.

---

## De statusroute testen

Zodra er een order is, kan de marktplaats vragen hoe het ermee staat:

```
GET /rest/V1/webshopnl/status/<order_id>
```

Die antwoordt met de Magento-orderstatus, of er een verzending bestaat, en de bezorggegevens inclusief het track-and-tracenummer.

Van begin tot eind testen: verzend de order in Magento met een trackingnummer, en laat Webshop.nl daarna de status opvragen. Het trackingnummer hoort terug te komen. Dat is de route waar de track en trace van de koper op leunt.

---

## Een weigering testen

Eén keer doen is de moeite waard, zodat je hem later herkent.

Laat Webshop.nl dezelfde order twee keer sturen. De tweede poging wordt geweigerd met *Order already imported*, en er verschijnt geen tweede Magento-order.

Dat is opzet: Webshop.nl probeert het opnieuw na een netwerkhapering, en dit voorkomt dat één verkoop twee orders wordt.

---

## Als een test mislukt

**Er komt helemaal niets binnen.** Controleer de vier voorwaarden hierboven. Kijk daarna naar de beheerdersrol die de integratie gebruikt: die heeft toegang tot de Webshop.nl-onderdelen nodig, en zonder dat weigert de route voordat de extensie er iets van ziet.

**De order wordt geweigerd.** Het antwoord noemt het ontbrekende veld. Debug Mode logt hetzelfde aan jouw kant.

**De import loopt vast op de verzendmethode.** De standaardmethode is niet beschikbaar voor dat adres. Stel een terugvaloptie in.

**De order komt binnen maar ziet er verkeerd uit.** Vergelijk de Magento-order met wat de marktplaats stuurde. Prijzen, verzendkosten en adressen komen over zoals aangeleverd; verschillen ze, dan laat het log zien wat er is ontvangen.

---

## Na het testen

Zet **Debug Mode** weer uit. Hij is uitgebreid en het log groeit snel.

Verwijder of annuleer de testorders als je ze op een live winkel hebt gedraaid, zodat ze niet in je rapportage terechtkomen.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
