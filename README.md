# T3S Vars – Global variables for TYPO3 v14

[![Latest Stable Version](https://poser.pugx.org/t3s/vars/v/stable)](https://packagist.org/packages/t3s/vars)
[![Monthly Downloads](https://poser.pugx.org/t3s/vars/d/monthly)](https://packagist.org/packages/t3s/vars)
[![Total Downloads](https://poser.pugx.org/t3s/vars/downloads)](https://packagist.org/packages/t3s/vars)
[![License](https://poser.pugx.org/t3s/vars/license)](https://packagist.org/packages/t3s/vars)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/t3sbootstrap)

**Version:** 1.0.0 · **Package:** `t3s/vars` · **Extension key:** `t3s_vars` · **Documentation:** [docs.typo3.org](https://docs.typo3.org/p/t3s/vars/main/en-us/) · **Live demo:** [www.t3sbootstrap.de/extensions/globale-variablen](https://www.t3sbootstrap.de/extensions/globale-variablen)

`t3s_vars` provides centrally maintained values such as company, address, phone or email as **placeholders**:

```
Call {{company}}: {{phone}}
```

- Values are maintained in the backend as **site settings** (different per site)
- Replacement in the **RTE** (bodytext) and wherever `lib.parseFunc` / `lib.parseFunc_RTE` applies
- **CKEditor 5 dropdown "Variables"** – added automatically to every preset (core or custom)
- **Automatic links**: phone → `tel:`, email → `mailto:` (with spam protection), website → URL
- **Fluid ViewHelpers** `<vars:get>` and `<vars:replace>` (e.g. for headers)
- **Content element "Address"** (`t3s_address`) – layouts *Compact*, *Compact (address & map)*, *Split* (contact + Google Maps) and *Split (contact only)*, `<address>` with schema.org markup
- **Icons** for phone, email, website etc. if EXT:iconpack with Font Awesome is installed
- **Google Maps**: route button to your Google Business profile, optional map with two-click privacy
- Values also available as **TypoScript constants** and in **PAGEVIEW**
- No database tables or fields required (the layout of the content element is stored in `pi_flexform`), no custom RTE preset required

## Requirements

| | |
|---|---|
| TYPO3 | 14.3+ |
| PHP | 8.2+ |
| RTE | CKEditor 5 (`rte_ckeditor`) |

## Installation

```bash
composer require t3s/vars
```

Then add the site set **"T3S Vars - Global variables"** (`t3s/vars`) to your site:

*Sites → Setup → Edit site → Sets*, or in `config/sites/<site>/config.yaml`:

```yaml
dependencies:
  - t3s/vars
```

Flush caches:

```bash
vendor/bin/typo3 cache:flush
```

## Maintaining values

*Sites → Setup → Edit settings* → category **Global variables**.
The values are stored in `config/sites/<site>/settings.yaml`:

```yaml
vars:
  company: 'TYPO3 Association'
  street: 'Bahnhofstrasse 4'
  zip: '6340'
  city: 'Baar'
  country: 'Switzerland'
  email: 'info@typo3.org'
  website: 'https://typo3.org'
```

### Included variables

| Placeholder | Setting | Link |
|---|---|---|
| `{{company}}` | `vars.company` | – |
| `{{person}}` | `vars.person` | – |
| `{{profession}}` | `vars.profession` | – |
| `{{street}}` | `vars.street` | – |
| `{{addressAddition}}` | `vars.addressAddition` | – |
| `{{zip}}` | `vars.zip` | – |
| `{{city}}` | `vars.city` | – |
| `{{country}}` | `vars.country` | – |
| `{{phone}}` | `vars.phone` | `tel:` |
| `{{fax}}` | `vars.fax` | `tel:` |
| `{{email}}` | `vars.email` | `mailto:` |
| `{{website}}` | `vars.website` | URL |
| `{{vatId}}` | `vars.vatId` | – |
| `{{maps}}` | `vars.maps` | – (used by the content element) |

### Adding custom variables

In your own site set in `settings.definitions.yaml` – every key with the prefix `vars.` automatically becomes a placeholder and shows up in the RTE dropdown:

```yaml
settings:
  vars.opening:
    label: 'Opening hours'
    category: vars
    type: string
    default: 'Mon–Fri 8am–4pm'
  vars.mobile:
    label: 'Mobile'
    category: vars
    type: string
    default: ''
```

Nested keys are possible: `vars.office.phone` → `{{office.phone}}`.

## Usage

### In the RTE

Type the placeholder or insert it via the **Variables** dropdown in the toolbar. The dropdown only lists variables that have a value, each with its label and current value.

| Notation | Output |
|---|---|
| `{{phone}}` | Link if configured as a link key, otherwise text |
| `{{phone:text}}` | always plain text |
| `{{unknown}}` | stays unchanged (typos are easy to spot) |

Empty values are replaced by an empty string.

### Controlling links

Category **Variables: links** in the site settings:

| Setting | Default | Link |
|---|---|---|
| `t3sVars.telKeys` | `phone,fax` | `tel:` – spaces, hyphens etc. are removed from the `href` |
| `t3sVars.mailKeys` | `email` | `mailto:` – `config.spamProtectEmailAddresses` applies |
| `t3sVars.urlKeys` | `website` | URL – opens in a new tab (`target="_blank" rel="noopener"`) |

Simply add your own keys, e.g. `phone,fax,mobile`.

**Website URLs:** enter the full URL including `https://` (e.g. `https://typo3.org`). If the scheme is missing, `https://` is added automatically for the link; the link text is shown exactly as entered.

Every setting has a short help text with format and example in the backend (English and German).

### Google Maps

| Setting | Category | Content |
|---|---|---|
| `vars.maps` | Global variables | Share link of your **Google Business profile** (Google Maps → your business → *Share* → `https://maps.app.goo.gl/…`) |
| `t3sVars.mapsEmbed` | Variables: links | Embed URL for the map: Google Maps → *Share* → *Embed a map* → paste the `src` URL (`https://www.google.com/maps/embed?pb=…`) or the whole `<iframe>` code – share links do not work here |
| `t3sVars.mapsConsent` | Variables: links | Load the map only after a click (default: on) |

Use the share link of your Business profile – not a plain address search – so the route button points to your verified listing. This links website and profile consistently (local SEO); together with identical NAP data (name, address, phone) on the website and in the profile it is a supporting signal, not a ranking guarantee.

Only `http(s)` URLs are accepted for both values.

### In Fluid

The namespace `vars` is registered globally – no `{namespace}` declaration needed.

```html
<!-- single value (linked if configured) -->
<vars:get key="phone" />
<vars:get key="phone" link="0" />
<vars:get key="opening" default="by appointment" />
{vars:get(key: 'company')}

<!-- replace placeholders in any text -->
<vars:replace link="0">{data.header}</vars:replace>
{data.header -> vars:replace(link: 0)}
```

`<vars:replace>` escapes the content before replacing. A ViewHelper output inside (e.g. `f:sanitize.html()`) is not escaped twice.

#### Example: placeholders in headers

Placeholders are replaced automatically in RTE text – but **headers** are output directly by the template, without `parseFunc`. An editor entering the header

```
Welcome to {{company}}
```

sees `Welcome to {{company}}` in the frontend. With `vars:replace` in the template that outputs the header, it becomes `Welcome to TYPO3 Association`.

Override the partial that outputs the header and wrap the output. With fluid_styled_content this is `Partials/Header/Header.html` – replace `{header}` with:

```html
<vars:replace link="0">{header}</vars:replace>
```

If your theme outputs headers in another partial, wrap the output there in the same way. Output that is already HTML (e.g. `{header -> f:format.raw()}`) can be wrapped as is – it is not escaped twice.

`link="0"` prevents nested links – headers are often linked themselves.

#### More examples

```html
<!-- page title in a PAGEVIEW template: "Contact – {{company}}" -->
<title>{page.title -> vars:replace(link: 0)}</title>

<!-- image caption or teaser text without RTE -->
<vars:replace>{file.description}</vars:replace>
```

In **PAGEVIEW** templates the raw values are available directly:

```html
{site.settings.vars.company}
```

### In TypoScript

Site settings are automatically available as TypoScript constants. `{$vars.…}` works **only in TypoScript** (setup/constants of a site set) – not in Fluid and not in the RTE.

Useful wherever TypoScript itself outputs or sets a value:

```typoscript
# meta tag
page.meta.author = {$vars.company}

# copyright in footer via lib object
lib.copyright = TEXT
lib.copyright.value = © {$vars.company}
lib.copyright.htmlSpecialChars = 1
lib.copyright.wrap = <span class="copyright">|</span>

# extension config that expects TypoScript
plugin.tx_news.settings.detail.media.image.alt = {$vars.company}
```

### Overview: which notation where

| Where | Notation |
|---|---|
| RTE / bodytext | `{{company}}` |
| Fluid template | `<vars:get key="company" />` |
| Headers etc. (override the header partial) | `<vars:replace link="0">…</vars:replace>` – see example above |
| PAGEVIEW template | `{site.settings.vars.company}` |
| TypoScript | `{$vars.company}` |

If no TypoScript object needs the value, `{$vars.…}` is not required.

## Content element "Address" (`t3s_address`)

In the new content element wizard under *Default* → **Address (global variables)**. The data comes directly from the site settings, so changes there apply to all address elements at once (after flushing the cache).

| Field | Use |
|---|---|
| Layout | *Compact (address only)*, *Compact (address & map)*, *Split (contact + map)* or *Split (contact only, no map)* – layouts with map need `t3sVars.mapsEmbed` (hint shown at the field) |
| Header / Subheader | Split: heading and small label above it (e.g. "Contact") |

### Layout "Compact"

Output (empty values are omitted):

- Header, frame, spacing etc. come from the `Default` layout of the content elements (e.g. fluid_styled_content).
- Links follow the settings under *Variables: links*.
- Labels (Phone, Fax, Email, Web) are translated (en, de).

### Layout "Compact (address & map)"

Same address block as *Compact*, the Google map below it (full width, min. 350px, rounded corners). Uses the same two-click privacy as *Split*. Without `t3sVars.mapsEmbed` it looks like *Compact*.

### Layout "Split"

Two columns from 992px: left subheading, heading, NAP blocks (*Headquarters*, *Direct contact*) and the route button; right the Google map. Without `t3sVars.mapsEmbed` – or with the layout *Split (contact only, no map)* – the section is single-column.


- The header is rendered inside the left column (the layout's header section is left empty). *Hidden* header layout hides it.
- The consent script is only loaded when a map is shown.
- Colors follow the surrounding text color, so light and dark mode work without configuration. Links and buttons use the Bootstrap primary/link color if available. Override per site:
  ```css
  .t3s-contact {
    --t3s-contact-primary: currentColor;
    --t3s-contact-accent: #2563eb;
    --t3s-contact-muted: color-mix(in srgb, currentColor 72%, transparent);
    --t3s-contact-bg-light: color-mix(in srgb, currentColor 6%, transparent);
    --t3s-contact-btn-bg: #2563eb;
    --t3s-contact-btn-color: #fff;
    --t3s-contact-padding: 2rem;
  }
  ```
- **Two-click privacy:** with `t3sVars.mapsConsent` (default) no request is sent to Google until the visitor clicks *Load map*. Turned off, the iframe is embedded directly (`loading="lazy"`).
- **Content Security Policy:** if CSP is active in the frontend, allow `frame-src https://www.google.com`.

### Icons (EXT:iconpack + Font Awesome)

If [EXT:iconpack](https://extensions.typo3.org/extension/iconpack) with [Font Awesome](https://extensions.typo3.org/extension/iconpack_fontawesome) (fa5, fa6 or fa7) is installed, the content element shows icons instead of the labels "Phone:", "Fax:", "Email:", "Web:":

| Variable | Icon |
|---|---|
| phone | `mobile` |
| fax | `fax` |
| email | `envelope` |
| website | `globe` |
| maps (route link) | `map-marker-alt` |

- Switch off with the site setting `t3sVars.icons`.
- Icons get the classes `t3s-icon fa-fw` and `aria-hidden="true"`.
- Other icons via TypoScript, e.g. a landline icon:
  ```typoscript
  tt_content.t3s_address.dataProcessing.1760.icons.phone = phone
  ```
- Without EXT:iconpack the labels are shown – nothing to configure.

### Spacing

The address is built from blocks: profession + owner, company + address, contact entries (each on its own line), VAT ID. `contact.css` is loaded in all layouts; adjust the spacing per site:

```css
.t3s-address {
  --t3s-address-gap: 1.5rem;       /* between the blocks */
  --t3s-address-item-gap: 1rem;    /* between phone, email, web … */
  --t3s-address-vat-gap: 3rem;     /* above the VAT ID */
}
```

### Customizing the template

Templates: `Address.fluid.html` (*Compact*), `AddressMap.fluid.html` (*Compact (address & map)*), `AddressSplit.fluid.html` (*Split*). Shared partials: `T3sVars/Address.fluid.html` (address block), `T3sVars/Parts.fluid.html` (sections `Identity`, `Postal`, `Contact`, `Vat` – used by all layouts) and `T3sVars/Map.fluid.html` (map incl. two-click privacy).

Place your own templates/partials in your own extension and add the path with a higher index:

```typoscript
tt_content.t3s_address.partialRootPaths.1770 = EXT:my_extension/Resources/Private/Partials/
tt_content.t3s_address.templateRootPaths.20 = EXT:my_extension/Resources/Private/Templates/Content/
```

Available in the template:

| Variable | Content |
|---|---|
| `{vars.<key>}` | raw value (escaped by Fluid) |
| `{varsLinked.<key>}` | escaped and linked if configured – output with `-> f:format.raw()` |
| `{varsConfig.mapsUrl}` | validated Business profile link |
| `{varsConfig.mapsEmbed}` | validated embed URL |
| `{varsConfig.mapsConsent}` | two-click privacy on/off |
| `{varsIcons.<key>}` | icon markup (phone, fax, email, website, maps) if EXT:iconpack + Font Awesome is available – output with `-> f:format.raw()` |

This also lets you add custom variables, e.g. `{vars.opening}`.

The DataProcessor `T3S\T3sVars\DataProcessing\VariablesProcessor` can also be used in other content elements or in PAGEVIEW:

```typoscript
tt_content.my_element.dataProcessing.1760 = T3S\T3sVars\DataProcessing\VariablesProcessor
```

## RTE integration

An event listener (`AfterPrepareConfigurationForEditorEvent`) adds the plugin and button to **every** CKEditor 5 preset – no custom preset required.

- **Set the position:** add `t3sVars` to the preset's toolbar yourself – the button is then not appended a second time:
  ```yaml
  editor:
    config:
      toolbar:
        items:
          - bold
          - t3sVars
  ```
- **Disable:** *Admin Tools → Settings → Extension Configuration → t3s_vars → rteButton*
- No button if the site has no variables with a value.

## Source and issues

Source code, bug reports and feature requests: [github.com/t3solution/t3s_vars](https://github.com/t3solution/t3s_vars)

## License and author

GPL-2.0-or-later · Helmut Hackbarth · [T3Solution](https://www.t3solution.de)
