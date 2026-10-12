..  _changelog:

=========
Changelog
=========

..  _changelog-1-0-0:

1.0.0
=====

First release.

*   Site set ``t3s/vars`` with the variables company, person, profession,
    street, address addition,
    zip, city, country, phone, fax, email, website, VAT ID, maps,
    opening hours and social media profiles
*   Placeholders ``{{key}}`` / ``{{key:text}}`` in RTE and ``parseFunc``
*   CKEditor 5 dropdown "Variables" for every preset
*   Automatic ``tel:``, ``mailto:`` and URL links
*   Fluid ViewHelpers ``vars:get`` and ``vars:replace``
*   Content element "Address" with the layouts *Compact*,
    *Compact (address & map)*, *Split* and *Split (contact only)*
*   Icons via EXT:iconpack with Font Awesome (optional)
*   Google Maps route button and map with two-click privacy
*   Opening hours in schema.org format, shown in the page language
*   Social media links with brand icons
*   vCard download "Save contact" in the content element
*   JSON-LD (``LocalBusiness`` or any schema.org type) before ``</head>``
*   Translations of single values per language
*   ``VariablesProcessor`` for own content elements
*   Help texts for all settings in English and German

..  _changelog-planned:

Planned
=======

*   **Placeholders in headers without overriding the partial** – a site
    setting that enables ``<vars:replace>`` for headers
*   **Backend preview** – the page module shows the real address and the
    chosen layout instead of the header only
