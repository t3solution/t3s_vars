..  _configuration:

=============
Configuration
=============

All values are maintained in the backend module
:guilabel:`Sites > Setup > Edit settings`. Every setting shows a short help
text with format and example (English and German). The values are stored in
:file:`config/sites/<site>/settings.yaml`:

..  code-block:: yaml
    :caption: config/sites/<site>/settings.yaml

    vars:
      company: 'TYPO3 Association'
      person: 'Olivier Dobberkau'
      profession: 'President'
      street: 'Bahnhofstrasse 4'
      zip: '6340'
      city: 'Baar'
      country: 'Switzerland'
      email: 'info@typo3.org'
      website: 'https://typo3.org'
      vatId: 'CHE-112.256.297 MWST'

..  contents::
    :local:
    :depth: 1

..  _configuration-variables:

Global variables
================

Category :guilabel:`Global variables`. Every setting ``vars.<key>`` is
available as placeholder ``{{<key>}}``.

..  confval:: vars.company
    :name: vars-company
    :type: string
    :default: ''

    Official company name, exactly as in the Google Business profile
    (consistent NAP data). Placeholder ``{{company}}``.

..  confval:: vars.person
    :name: vars-person
    :type: string
    :default: ''

    Name of the owner or contact person, shown in bold above the company,
    e.g. ``Olivier Dobberkau``. Placeholder ``{{person}}``.

..  confval:: vars.profession
    :name: vars-profession
    :type: string
    :default: ''

    Profession or activity, shown above the owner and company name, e.g.
    ``President``. Placeholder ``{{profession}}``.

..  confval:: vars.street
    :name: vars-street
    :type: string
    :default: ''

    Street and house number, e.g. ``Bahnhofstrasse 4``. Placeholder
    ``{{street}}``.

..  confval:: vars.addressAddition
    :name: vars-addressaddition
    :type: string
    :default: ''

    Optional address line below the street, e.g. ``2nd floor``. Placeholder ``{{addressAddition}}``.

..  confval:: vars.zip
    :name: vars-zip
    :type: string
    :default: ''

    Postal code, e.g. ``6340``. Placeholder ``{{zip}}``.

..  confval:: vars.city
    :name: vars-city
    :type: string
    :default: ''

    City, e.g. ``Baar``. Placeholder ``{{city}}``.

..  confval:: vars.country
    :name: vars-country
    :type: string
    :default: ''

    Country name or ISO code, e.g. ``Switzerland`` or ``CH``. Placeholder
    ``{{country}}``.

..  confval:: vars.phone
    :name: vars-phone
    :type: string
    :default: ''

    International format recommended, e.g. ``+41 41 000 00 00``. Spaces,
    hyphens and brackets are allowed – the ``tel:`` link uses digits and
    ``+`` only. Placeholder ``{{phone}}``.

..  confval:: vars.fax
    :name: vars-fax
    :type: string
    :default: ''

    Same format as phone. Placeholder ``{{fax}}``.

..  confval:: vars.email
    :name: vars-email
    :type: string
    :default: ''

    Plain address without ``mailto:``, e.g. ``info@typo3.org``. The spam
    protection ``config.spamProtectEmailAddresses`` applies. Placeholder
    ``{{email}}``.

..  confval:: vars.website
    :name: vars-website
    :type: string
    :default: ''

    Full URL including ``https://``, e.g. ``https://typo3.org``. If
    the scheme is missing, ``https://`` is added automatically for the link.
    The link text is shown exactly as entered. Placeholder ``{{website}}``.

..  confval:: vars.vatId
    :name: vars-vatid
    :type: string
    :default: ''

    VAT identification number including country code, e.g.
    ``CHE-112.256.297 MWST``. Placeholder ``{{vatId}}``.

..  confval:: vars.maps
    :name: vars-maps
    :type: string
    :default: ''

    Share link of your Google Business profile, used for the button
    "Plan route on Google Maps". See :ref:`configuration-google-maps`.

..  _configuration-custom-variables:

Custom variables
----------------

Every key with the prefix ``vars.`` becomes a placeholder and shows up in
the RTE dropdown. Define it in your own site set:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Sets/MySet/settings.definitions.yaml

    settings:
      vars.opening:
        label: 'Opening hours'
        category: vars
        type: string
        default: 'Mon–Fri 8am–4pm'

Nested keys are possible: ``vars.office.phone`` → ``{{office.phone}}``.

..  _configuration-links:

Links
=====

Category :guilabel:`Variables: links`. Comma-separated lists of variable
keys that are rendered as links.

..  confval:: t3sVars.telKeys
    :name: t3svars-telkeys
    :type: string
    :default: 'phone,fax'

    Rendered as ``tel:`` link. Spaces, hyphens etc. are removed from the
    ``href``.

..  confval:: t3sVars.mailKeys
    :name: t3svars-mailkeys
    :type: string
    :default: 'email'

    Rendered as ``mailto:`` link with spam protection.

..  confval:: t3sVars.urlKeys
    :name: t3svars-urlkeys
    :type: string
    :default: 'website'

    Rendered as URL link, opening in a new tab
    (``target="_blank" rel="noopener"``). Values without scheme get ``https://``.

..  _configuration-google-maps:

Google Maps
===========

..  confval:: t3sVars.mapsEmbed
    :name: t3svars-mapsembed
    :type: string
    :default: ''

    Embed URL of the map shown in the layout *Split*. In Google Maps open
    :guilabel:`Share > Embed a map > Copy HTML` and paste the URL inside
    ``src="…"`` (``https://www.google.com/maps/embed?pb=…``) or the whole
    ``<iframe>`` code. Only Google Maps embed URLs are accepted – share links
    such as ``maps.app.goo.gl`` or ``share.google`` cannot be embedded.
    Empty = no map.

..  confval:: t3sVars.mapsConsent
    :name: t3svars-mapsconsent
    :type: bool
    :default: true

    Two-click privacy: the map is loaded only after the visitor clicks
    :guilabel:`Load map`. No data is sent to Google before. If disabled,
    the iframe is embedded directly with ``loading="lazy"``.

..  _configuration-google-maps-link:

Which link for :confval:`vars-maps`?
------------------------------------

Use the share link of your **Google Business profile**, not a plain
address search:

#.  Open `Google Maps <https://www.google.com/maps>`__ and search for your
    business.
#.  Open the business entry (profile with photos, reviews, opening hours).
#.  Click :guilabel:`Share` and :guilabel:`Copy link` – you get
    ``https://maps.app.goo.gl/…``.

Links of the form ``https://share.google/…`` or ``https://g.page/r/…``
also point to the profile, but may open Google Search instead of Google
Maps.

To open the route planner directly, use a Google Maps URL:

..  code-block:: text

    https://www.google.com/maps/dir/?api=1&destination=TYPO3+Association%2C+Bahnhofstrasse+4%2C+6340+Baar

Only ``http(s)`` URLs are accepted for :confval:`vars-maps` and
:confval:`t3svars-mapsembed`; a missing scheme is completed with
``https://``.

..  tip::
    Use identical name, address and phone (NAP) on the website and in the
    Google Business profile. Together with the profile link this is a
    supporting local SEO signal – not a ranking guarantee.

..  _configuration-icons:

Icons
=====

..  confval:: t3sVars.icons
    :name: t3svars-icons
    :type: bool
    :default: true

    Show icons instead of the labels "Phone:", "Fax:", "Email:", "Web:" in
    the content element "Address". Only effective if :composer:`quellenform/t3x-iconpack`
    with :composer:`quellenform/t3x-iconpack-fontawesome` (fa5, fa6 or fa7)
    is installed. See :ref:`developer-icons`.

..  _configuration-extension:

Extension configuration
=======================

:guilabel:`Admin Tools > Settings > Extension Configuration > t3s_vars`

..  confval:: rteButton
    :name: extconf-rtebutton
    :type: boolean
    :default: 1

    Add the dropdown "Variables" to the toolbar of every CKEditor 5 preset.
