# Producttypes

Hoe de [Webshop.nl extensie voor Magento 2](https://www.magmodules.nl/magento2-webshop-marketplace.html) configureerbare, bundel- en groepsproducten publiceert, en waarom deze instellingen meer uitmaken dan alle andere in de feed. Een configureerbaar product is in Magento twee dingen tegelijk: een pagina waar een bezoeker landt, en een set varianten die de voorraad dragen. De marktplaats heeft allebei nodig, maar dan wel op de juiste manier verbonden. Zet je dit verkeerd, dan publiceer je advertenties zonder maten, varianten zonder omschrijving, of links die nergens heen gaan. Deze pagina loopt elke instelling langs, wat hij in de feed verandert en welke combinatie voor de meeste winkels werkt. Simpele producten zonder varianten merken hier niets van.

---

## Waar het probleem zit

Neem een hoodie in drie maten en twee kleuren. In Magento is dat:

- één **configureerbaar** product, met de naam, de omschrijving en de pagina
- zes **simpele** producten, met de voorraad, de maat en de kleur

De simpele producten staan meestal op *Not Visible Individually*: ze hebben geen eigen pagina, want het configureerbare product ís de pagina.

De marktplaats wil zes koopbare artikelen tonen, elk met eigen voorraad, allemaal wijzend naar één product. Deze instellingen bepalen hoe die koppeling wordt gelegd.

---

## Use ... Products

De eerste keuze: wat er in de feed komt.

| Waarde | Resultaat |
|---|---|
| `Parent` | Alleen het configureerbare product. Eén advertentie, geen maten, voorraad van de ouder. |
| `Simple` | Alleen de varianten. Zes advertenties met voorraad, maar zonder omschrijving en zonder pagina. |
| `Both` | Het configureerbare product én zijn varianten. |

**`Both` is voor vrijwel elke winkel de juiste keuze.** De ouder verkoopt het product, de varianten dragen wat er werkelijk beschikbaar is.

`Parent` is logisch als de marktplaats je advertentie als één artikel behandelt, ongeacht de maat. `Simple` komt zelden voor en is meestal een vergissing.

---

## Use Parent URL for Simples

Waar de link van een variant heen wijst.

| Waarde | Resultaat |
|---|---|
| `No` | De variant linkt naar zijn eigen pagina — die bij een onzichtbare variant niet bestaat. |
| `Yes` | De variant linkt naar de pagina van de ouder. |
| `Yes, with Auto-Link` | De pagina van de ouder, met de attribuutkeuze in de link. |

**`Yes, with Auto-Link` is de aanbevolen instelling.** De link draagt iets als `#93=49&144=166` mee, wat de productpagina vertelt dat blauw en maat M geselecteerd moeten worden bij aankomst. Iemand die op de marktplaats op "blauw, M" klikte landt precies daarop.

Met gewoon `Yes` landen ze op het product zonder selectie en moeten ze opnieuw kiezen.

`No` is alleen zinvol als je varianten echt een eigen pagina hebben.

Een variant zonder eigen pagina wordt hoe dan ook op de pagina van zijn ouder gepubliceerd, wat deze instelling ook zegt. Een bezoeker naar een pagina sturen die niet bestaat heeft nooit zin, dus doet de extensie dat niet.

---

## Use Parent Image for Simples

Welke afbeelding een variant publiceert.

| Waarde | Resultaat |
|---|---|
| `No` | De eigen afbeelding van de variant. |
| `Yes` | De afbeelding van de ouder. |
| `Only if Empty` | De eigen afbeelding, of die van de ouder als hij er geen heeft. |
| `Combine, simple images first` | Beide galerijen, die van de variant eerst. |
| `Combine, parent images first` | Beide galerijen, die van de ouder eerst. |

**`Only if Empty` past bij de meeste catalogi.** Een variant met een eigen foto publiceert die; een zonder leent van de ouder in plaats van zonder afbeelding de deur uit te gaan.

Kies een `Combine`-optie als je varianten elk een foto hebben en je de volledige galerij bij elke advertentie wilt.

---

## Use Parent Data for Simples

Welke attributen een variant overerft.

Omschrijvingen staan doorgaans alleen bij het configureerbare product. Zonder overerving gaat elke variant met een lege omschrijving de deur uit, en dat telt bij marktplaatsen als een zwakke advertentie.

Kies de attributen die bij de ouder staan — vrijwel altijd de omschrijving, soms het merk of een categorieattribuut.

---

## Use Fallback only on Non Visible Simples

Beperkt alle overerving hierboven tot varianten die *Not Visible Individually* zijn.

Zet hem op `Yes` en een simpel product met een eigen pagina houdt zijn eigen gegevens, terwijl de onzichtbare varianten van hun ouder lenen.

Dit doet ertoe bij catalogi waarin een product zowel los wordt verkocht als onderdeel is van een groepsproduct. Zonder deze instelling zou het overerven van de groep waar het toevallig bij hoort, en zo zijn eigen omschrijving en link verliezen.

---

## Parent Price (alleen groepsproducten)

Een groepsproduct heeft geen eigen prijs; het is een set producten die samen wordt verkocht.

| Waarde | Resultaat |
|---|---|
| `No` | De groep wordt zonder eigen prijs gepubliceerd. |
| `Yes` | De groep neemt de prijs van zijn goedkoopste onderdeel. |

De meeste marktplaatsen hebben een prijs nodig, dus `Yes` is meestal de werkbare keuze.

---

## Bundel- en groepsproducten

Dezelfde instellingen verschijnen bij bundels en groepsproducten, en betekenen hetzelfde.

Verschillen die het weten waard zijn:

- Een **bundel** is één product dat uit onderdelen is opgebouwd. De prijs is de som van wat erin zit, en daarom kan een bundel een prijsbereik tonen in plaats van één bedrag.
- Een **groepsproduct** is een set losse producten die samen worden getoond. Elk onderdeel wordt apart gekocht.
- Onderdelen van een bundel en leden van een groep zijn vaak niet los zichtbaar, dus dezelfde overerving van URL en gegevens geldt.

---

## De combinatie die bij de meeste winkels werkt

Voor configureerbare producten:

| Instelling | Waarde |
|---|---|
| Use Configurable/Simple Products | `Both` |
| Use Parent URL for Simples | `Yes, with Auto-Link` |
| Use Parent Image for Simples | `Only if Empty` |
| Use Parent Data for Simples | Omschrijving |
| Use Fallback only on Non Visible Simples | `Yes` |

Controleer daarna je werk: open de Feeds-preview, zoek een variant, en bevestig dat die zijn eigen maat en kleur draagt, de omschrijving van de ouder, en een link die de ouder opent met die variant geselecteerd.

---

## Meer Hulp Nodig?

**Documentatie:**
- [Alle Help Artikelen](https://www.magmodules.nl/help/magento2-webshop-marketplace.html) - Compleet documentatie overzicht

**Support:**
- [Contact Opnemen](https://www.magmodules.nl/support) - Hulp van ons team
