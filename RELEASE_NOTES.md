# Release Notes – EXT:t3s_vars 1.0.0

**Version:** 1.0.0 · **Datum:** 11.10.2026 · **Paket:** `t3s/vars` · **TYPO3:** 14.3+ · **PHP:** 8.2+ · **Autor:** Helmut Hackbarth · T3Solution

**Live-Demo:** https://www.t3sbootstrap.de/extensions/globale-variablen · **Quellcode:** https://github.com/t3solution/t3s_vars

## Überblick

`t3s_vars` stellt zentral gepflegte Werte wie Firma, Adresse, Telefon oder E-Mail als Platzhalter `{{key}}` bereit. Die Werte werden im Backend als **Site Settings** gepflegt und stehen im RTE, in Fluid, in TypoScript und in einem eigenen **Content Element „Adresse“** zur Verfügung.

## Highlights

- **Platzhalter im RTE** – `{{company}}`, `{{phone}}`, `{{email}}` … werden im Frontend automatisch ersetzt
- **CKEditor-5-Dropdown „Variablen“** – hängt sich automatisch in jedes Preset ein (Core oder eigene)
- **Automatische Verlinkung** – Telefon → `tel:`, E-Mail → `mailto:` mit Spamschutz, Website → URL (neuer Tab)
- **Content Element „Adresse“** (`t3s_address`) – Layouts *Kompakt*, *Kompakt (Adresse & Karte)*, *Split* (Kontakt + Karte) und *Split (nur Kontakt)*, `<address>` mit schema.org-Auszeichnung
- **Google Maps** – Routen-Button zum Google-Business-Profil, Karte optional mit 2-Klick-Lösung (Datenschutz)
- **Icons** für Telefon, Fax, E-Mail, Website und Routen-Link, wenn EXT:iconpack mit Font Awesome installiert ist
- **Öffnungszeiten** im schema.org-Format – in der Seitensprache angezeigt („Mo–Fr 08:00–17:00 Uhr“)
- **Social-Media-Profile** (Facebook, Instagram, LinkedIn, XING, YouTube, X, TikTok, Mastodon) als Icon-Links
- **vCard-Download** – Link „Kontakt speichern“ im Adress-Element (`.vcf` fürs Adressbuch)
- **JSON-LD** (`LocalBusiness` oder ein anderer schema.org-Typ) im `<head>` jeder Seite – optional
- **Mehrsprachige Sites**: einzelne Werte pro Sprache übersetzen
- **Fluid-ViewHelper** `<vars:get>` und `<vars:replace>`, global registriert
- **Eigene Variablen** per `settings.definitions.yaml` – ohne Code-Änderung

## Funktionsumfang

| Bereich | Umsetzung |
|---|---|
| Werte | Site Set `t3s/vars`, Settings `vars.*` (company, person, profession, street, addressAddition, zip, city, country, phone, fax, email, website, vatId, maps, openingHours, facebook, instagram, linkedin, xing, youtube, x, tiktok, mastodon) |
| Öffnungszeiten | `vars.openingHours` im schema.org-Format, übersetzte Anzeige, Microdata und JSON-LD |
| Social Media | `vars.facebook` … `vars.mastodon` – Icon-Links im Adress-Element, `sameAs` im JSON-LD |
| vCard | Link „Kontakt speichern“ – `.vcf` als Data-URL, abschaltbar über `t3sVars.vcard` |
| JSON-LD | `t3sVars.jsonLd` (an/aus), `t3sVars.jsonLdType` (Standard `LocalBusiness`), per PSR-14-Event vor `</head>` |
| Übersetzungen | `t3sVars.translations`: `en.country = Germany; en.profession = Web developer` |
| RTE / parseFunc | `postUserFunc` auf `lib.parseFunc_RTE` und `lib.parseFunc` |
| CKEditor 5 | PSR-14 `AfterPrepareConfigurationForEditorEvent` – Plugin, Button, Werte |
| Verlinkung | Site Settings `t3sVars.telKeys`, `t3sVars.mailKeys`, `t3sVars.urlKeys` |
| Fluid | `<vars:get key="…" />`, `<vars:replace>…</vars:replace>` |
| Content Element | `t3s_address` – Layouts Kompakt / Kompakt mit Karte / Split / Split ohne Karte, `<address>` mit schema.org |
| Google Maps | `vars.maps` (Business-Profil), `t3sVars.mapsEmbed` (Karte), `t3sVars.mapsConsent` (2-Klick) |
| DataProcessor | `VariablesProcessor` – `{vars}` / `{varsLinked}` / `{varsConfig}` / `{varsIcons}` |
| Icons | EXT:iconpack + Font Awesome (fa5/fa6/fa7): `mobile`, `fax`, `envelope`, `globe`, `map-marker-alt` – abschaltbar über `t3sVars.icons` |
| TypoScript | `{$vars.<key>}` als Konstante |
| PAGEVIEW | `{site.settings.vars.<key>}` |
| Sprachen | Englisch, Deutsch – inkl. Hilfetexten mit Format und Beispiel zu jedem Setting |

### Content Element „Adresse“

CType `t3s_address` im Wizard unter *Standard*. Die Daten kommen direkt aus den Site Settings.

| Layout | Ausgabe |
|---|---|
| Kompakt | Adresse, Kontakt und Routen-Link als `<address>` |
| Kompakt (Adresse & Karte) | Wie Kompakt, darunter die Google-Karte in voller Breite – mit 2-Klick-Lösung |
| Split | Zwei Spalten ab 992px: Subheader, Header, NAP-Blöcke *Unternehmenssitz* / *Direktkontakt*, Button „Route auf Google Maps planen“ – rechts die Karte |
| Split (nur Kontakt, ohne Karte) | Wie Split, aber immer einspaltig ohne Karte |

- schema.org `Organization` / `PostalAddress` inkl. `addressCountry`
- Leere Werte werden ausgelassen
- Eigenes CSS (`contact.css`), wird nur geladen, wenn das Element auf der Seite steht
- Hell- und Dunkelmodus ohne Konfiguration: Texte übernehmen die Farbe der Umgebung, Links und Buttons die Primärfarbe des Themes; anpassbar über `--t3s-contact-*`
- Karte mit **2-Klick-Lösung**: Vor dem Klick auf „Karte laden“ werden keine Daten an Google übertragen (abschaltbar)
- Nutzt das Layout `Default` der Content Elemente (z. B. fluid_styled_content)
- Template über `templateRootPaths` überschreibbar

### Öffnungszeiten

```yaml
vars:
  openingHours: 'Mo-Fr 08:00-17:00; Sa 09:00-12:00; Termine nach Vereinbarung'
```

Zeiträume im schema.org-Format, getrennt durch `;` (der Settings-Editor zeigt ein einzeiliges Feld). Tage: `Mo Tu We Th Fr Sa Su`. Angezeigt wird in der Seitensprache – „Mo–Fr 08:00–17:00 Uhr“ auf Deutsch, „Mon–Fri 08:00–17:00“ auf Englisch. Freie Einträge wie „Termine nach Vereinbarung“ erscheinen als Text und fließen nicht in die strukturierten Daten ein.

### Social Media

`vars.facebook`, `vars.instagram`, `vars.linkedin`, `vars.xing`, `vars.youtube`, `vars.x`, `vars.tiktok`, `vars.mastodon` – vollständige Profil-URL. Das Adress-Element zeigt sie als Icon-Links (Brand-Icons über EXT:iconpack, sonst der Name als Text), im JSON-LD stehen sie als `sameAs`.

### vCard „Kontakt speichern“

Das Adress-Element endet bei den Kontaktdaten mit dem Link **„Kontakt speichern“**. Er lädt eine `.vcf`-Datei (vCard 3.0) mit Name, Firma, Beruf, Adresse, Telefon, Fax, E-Mail, Website und Social-Media-Profilen – fürs Adressbuch auf Handy und Computer. Die Datei steckt als Data-URL im Link: kein zusätzlicher Request, keine Server-Route, mit der Seite gecacht. Abschaltbar über `t3sVars.vcard`.

### JSON-LD

Mit `t3sVars.jsonLd` bekommt jede Seite strukturierte Daten im `<head>`: Name, URL, Telefon, Fax, E-Mail, USt-IdNr., Adresse, Öffnungszeiten, Karten-Link und Social-Media-Profile. Der Typ ist über `t3sVars.jsonLdType` einstellbar (z. B. `Organization`, `ProfessionalService`, `Store`, `Restaurant`) und gilt auch für die Microdata des Adress-Elements. Das Script wird per Event vor `</head>` eingefügt und funktioniert mit `PAGE` und `PAGEVIEW`. Standard: aus – abschalten, wenn eine andere Extension bereits LocalBusiness-Daten ausgibt.

### Mehrsprachige Sites

Site Settings gelten für alle Sprachen. Abweichende Werte stehen in `t3sVars.translations`, getrennt durch `;`:

```text
en.country = Germany; en.profession = Web developer
```

Gilt für Platzhalter, ViewHelper, das Adress-Element und JSON-LD – nicht für TypoScript-Konstanten und `{site.settings.vars.*}`. Wochentage werden automatisch übersetzt.

### Darstellung

Die Adresse ist in Blöcke gegliedert – Beruf und Inhaber, Firma und Anschrift, Kontaktangaben (jede in eigener Zeile), USt-IdNr. – mit deutlichen Abständen dazwischen, in allen Layouts. Die Abstände lassen sich per CSS-Variable anpassen: `--t3s-address-gap`, `--t3s-address-item-gap`, `--t3s-address-vat-gap`.

### Icons

Ist EXT:iconpack mit Font Awesome (fa5, fa6 oder fa7) installiert, zeigt das Content Element statt „Telefon:“, „E-Mail:“ usw. Icons. Ohne iconpack bleiben die Beschriftungen – keine Konfiguration nötig. Andere Icons per TypoScript, z. B. `tt_content.t3s_address.dataProcessing.1760.icons.phone = phone`.

### Google Maps

| Setting | Inhalt |
|---|---|
| `vars.maps` | Teilen-Link des **Google-Business-Profils** (`https://maps.app.goo.gl/…`) |
| `t3sVars.mapsEmbed` | Einbettungs-URL der Karte (`https://www.google.com/maps/embed?pb=…`) oder ganzer `<iframe>`-Code – Teilen-Links funktionieren hier nicht |
| `t3sVars.mapsConsent` | Karte erst nach Klick laden (Standard: an) |

Der Routen-Button verweist auf das verifizierte Business-Profil statt auf eine reine Adresssuche. Zusammen mit identischen NAP-Daten auf Website und Profil ist das ein unterstützendes Local-SEO-Signal. Es werden nur `http(s)`-URLs akzeptiert.

### DataProcessor `VariablesProcessor`

Stellt in Fluid `{vars.<key>}` (Rohwert), `{varsLinked.<key>}` (escaped, ggf. verlinkt) und `{varsConfig}` (Maps-Einstellungen) bereit – nutzbar auch in eigenen Content Elementen:

```typoscript
tt_content.my_element.dataProcessing.1760 = T3S\T3sVars\DataProcessing\VariablesProcessor
```

### Schreibweisen im RTE

| Schreibweise | Ausgabe |
|---|---|
| `{{phone}}` | Link, falls als Link-Schlüssel konfiguriert, sonst Text |
| `{{phone:text}}` | immer reiner Text (z. B. innerhalb eines Links) |
| `{{unbekannt}}` | bleibt stehen – Tippfehler fallen auf |

### Welche Schreibweise wo

| Wo | Schreibweise |
|---|---|
| RTE / bodytext | `{{company}}` |
| Fluid-Template | `<vars:get key="company" />` |
| Überschriften (Header-Partial überschreiben) | `<vars:replace link="0">…</vars:replace>` |
| PAGEVIEW-Template | `{site.settings.vars.company}` |
| TypoScript | `{$vars.company}` |

## Installation

```bash
composer require t3s/vars
vendor/bin/typo3 cache:flush
```

Danach das Site Set **„T3S Vars - Globale Variablen“** (`t3s/vars`) in der Site einbinden und die Werte unter *Sites → Setup → Settings bearbeiten* pflegen.

```yaml
dependencies:
  - t3s/vars
```

Kein Datenbank-Update nötig: Die Extension benötigt weder eigene Tabellen noch eigene Felder – das Layout des Content Elements „Adresse“ wird im vorhandenen Feld `pi_flexform` gespeichert.

## Hinweise

- **Cache:** Nach Änderung der Werte den Frontend-Cache leeren – ersetzte Texte werden mit der Seite gecacht.
- **Platzhalter nicht verlinken:** Verlinkte Variablen in einem Redakteurs-Link erzeugen verschachtelte `<a>`-Tags – dort `{{phone:text}}` verwenden.
- **Schreibweise exakt:** `{{ company }}` (mit Leerzeichen) oder Formatierung mitten im Platzhalter wird nicht ersetzt.
- **Header-Felder** laufen nicht über `parseFunc`. Damit `Willkommen bei {{company}}` auch in Überschriften funktioniert, das Header-Partial überschreiben (bei fluid_styled_content `Partials/Header/Header.html`) und die Ausgabe in `<vars:replace link="0">…</vars:replace>` einschließen.
- **postUserFunc:** Setzt eine andere Extension `nonTypoTagStdWrap.postUserFunc`, gewinnt das zuletzt geladene Set.
- **Website-Link:** Vollständige URL mit `https://` eintragen; fehlt das Schema, wird `https://` für den Link automatisch ergänzt.
- **Content Security Policy:** Bei aktiver CSP im Frontend `frame-src https://www.google.com` erlauben.
- **RTE-Button** abschaltbar unter *Extension Configuration → t3s_vars → rteButton*; Position steuerbar über den Toolbar-Eintrag `t3sVars` im Preset.

## Geplant

- **Platzhalter in Überschriften ohne Partial-Override** – Site Setting, das `<vars:replace>` für Header aktiviert
- **Vorschau im Seitenmodul** – echte Adresse und gewähltes Layout statt nur der Überschrift

## Versionen

| Version | Änderung |
|---|---|
| 1.0.0 | Erste Veröffentlichung: Site Set, Platzhalter, CKEditor-Dropdown, Verlinkung, ViewHelper, Content Element „Adresse“ (Kompakt / Kompakt mit Karte / Split / Split ohne Karte), Google Maps mit 2-Klick-Lösung, Öffnungszeiten, Social Media, vCard, JSON-LD, Übersetzungen, `VariablesProcessor` |
