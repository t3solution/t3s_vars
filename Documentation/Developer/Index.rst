..  _developer:

==============
For developers
==============

..  contents::
    :local:
    :depth: 1

..  _developer-notation:

Which notation where
====================

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   -   Where
        -   Notation
    *   -   RTE / bodytext
        -   ``{{company}}``
    *   -   Fluid template
        -   ``<vars:get key="company" />``
    *   -   Headers etc. in Fluid
        -   ``<vars:replace link="0">…</vars:replace>`` in the header
            partial, see :ref:`developer-viewhelper-headers`
    *   -   PAGEVIEW template
        -   ``{site.settings.vars.company}``
    *   -   TypoScript
        -   ``{$vars.company}``

..  _developer-fluid:

Fluid ViewHelpers
=================

The namespace ``vars`` is registered globally – no namespace declaration
is needed.

..  code-block:: html
    :caption: Single value (linked if configured)

    <vars:get key="phone" />
    <vars:get key="phone" link="0" />
    <vars:get key="opening" default="by appointment" />
    {vars:get(key: 'company')}

..  code-block:: html
    :caption: Replace placeholders in any text

    <vars:replace link="0">{data.header}</vars:replace>
    {data.header -> vars:replace(link: 0)}

``<vars:replace>`` escapes the child content before replacing. A ViewHelper
output inside (e.g. ``f:sanitize.html()``) is not escaped twice.

..  _developer-viewhelper-headers:

Example: placeholders in headers
--------------------------------

Placeholders are replaced automatically in RTE text – but **headers** are
output directly by the template, without ``parseFunc``. An editor entering
the header

..  code-block:: text

    Welcome to {{company}}

sees ``Welcome to {{company}}`` in the frontend. With ``vars:replace`` in the
template that outputs the header, it becomes ``Welcome to TYPO3
Association``.

Override the partial that outputs the header and wrap the output. With
fluid_styled_content this is :file:`Partials/Header/Header.html` – replace
``{header}`` with:

..  code-block:: html

    <vars:replace link="0">{header}</vars:replace>

If your theme outputs headers in another partial, wrap the output there in
the same way. Output that is already HTML (e.g.
``{header -> f:format.raw()}``) can be wrapped as is – it is not escaped
twice.

``link="0"`` prevents nested links – headers are often linked themselves.

..  code-block:: html
    :caption: More examples

    <!-- page title in a PAGEVIEW template: "Contact – {{company}}" -->
    <title>{page.title -> vars:replace(link: 0)}</title>

    <!-- image caption or teaser text without RTE -->
    <vars:replace>{file.description}</vars:replace>

..  _developer-viewhelper-arguments:

Arguments of ``vars:get``
-------------------------

..  list-table::
    :header-rows: 1
    :widths: 20 15 15 50

    *   -   Argument
        -   Type
        -   Default
        -   Description
    *   -   ``key``
        -   string
        -   –
        -   Variable key without the prefix ``vars.`` (required)
    *   -   ``link``
        -   bool
        -   ``true``
        -   Render ``tel:`` / ``mailto:`` / URL link if configured
    *   -   ``default``
        -   string
        -   ``''``
        -   Fallback if the variable does not exist

``vars:replace`` has the argument ``link`` (bool, default ``true``).

..  _developer-pageview:

PAGEVIEW
--------

In PAGEVIEW templates the raw values are available directly:

..  code-block:: html

    {site.settings.vars.company}

..  _developer-typoscript:

TypoScript
==========

Site settings are TypoScript constants. ``{$vars.…}`` works **only in
TypoScript** – not in Fluid and not in the RTE. Use it wherever TypoScript
itself outputs or sets a value:

..  code-block:: typoscript
    :caption: Examples

    # meta tag
    page.meta.author = {$vars.company}

    # copyright in footer via lib object
    lib.copyright = TEXT
    lib.copyright.value = © {$vars.company}
    lib.copyright.htmlSpecialChars = 1
    lib.copyright.wrap = <span class="copyright">|</span>

    # extension config that expects TypoScript
    plugin.tx_news.settings.detail.media.image.alt = {$vars.company}

The replacement in the RTE is done by a ``postUserFunc``:

..  code-block:: typoscript
    :caption: Set up by the site set t3s/vars

    lib.parseFunc_RTE.nonTypoTagStdWrap.postUserFunc = T3S\T3sVars\UserFunc\ParseFuncReplacer->replace
    lib.parseFunc.nonTypoTagStdWrap.postUserFunc = T3S\T3sVars\UserFunc\ParseFuncReplacer->replace

..  _developer-dataprocessor:

DataProcessor
=============

``T3S\T3sVars\DataProcessing\VariablesProcessor`` provides the variables to
Fluid. It is used by the content element "Address" and can be added to any
other content element or PAGEVIEW:

..  code-block:: typoscript

    tt_content.my_element.dataProcessing.1760 = T3S\T3sVars\DataProcessing\VariablesProcessor

Choose an index that is not used by other processors of the content element.

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   -   Variable
        -   Content
    *   -   ``{vars.<key>}``
        -   Raw value (escaped by Fluid)
    *   -   ``{varsLinked.<key>}``
        -   Escaped and linked if configured – output with
            ``-> f:format.raw()``
    *   -   ``{varsConfig.mapsUrl}``
        -   Validated Google Business profile link
    *   -   ``{varsConfig.mapsEmbed}``
        -   Validated map embed URL
    *   -   ``{varsConfig.mapsConsent}``
        -   Two-click privacy on/off
    *   -   ``{varsIcons.<key>}``
        -   Icon markup for ``phone``, ``fax``, ``email``, ``website``,
            ``maps`` – only with EXT:iconpack + Font Awesome. Output with
            ``-> f:format.raw()``

..  _developer-icons:

Icons
-----

If EXT:iconpack with Font Awesome (fa5, fa6 or fa7) is installed, the
processor renders icons via the iconpack API (style ``solid``, classes
``t3s-icon fa-fw``, ``aria-hidden="true"``). Without EXT:iconpack
``{varsIcons}`` is empty and the templates show the labels.

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Key
        -   Default icon
    *   -   ``phone``
        -   ``mobile``
    *   -   ``fax``
        -   ``fax``
    *   -   ``email``
        -   ``envelope``
    *   -   ``website``
        -   ``globe``
    *   -   ``maps``
        -   ``map-marker-alt``

Override an icon via TypoScript:

..  code-block:: typoscript

    tt_content.t3s_address.dataProcessing.1760.icons.phone = phone

..  _developer-templates:

Overriding templates
====================

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   -   Layout (FlexForm field ``layout`` in ``pi_flexform``)
        -   Template
    *   -   *Compact* (``compact``)
        -   ``Templates/Address.fluid.html``
    *   -   *Compact (address & map)* (``compactmap``)
        -   ``Templates/AddressMap.fluid.html``
    *   -   *Split* (``split``)
        -   ``Templates/AddressSplit.fluid.html``
    *   -   *Split (contact only, no map)* (``splitnomap``)
        -   ``Templates/AddressSplit.fluid.html`` (map suppressed)

Shared partials:

*   ``Partials/T3sVars/Address.fluid.html`` – address block
*   ``Partials/T3sVars/Parts.fluid.html`` – sections ``Identity``,
    ``Postal``, ``Contact`` and ``Vat``, used by all layouts
*   ``Partials/T3sVars/Map.fluid.html`` – map including two-click privacy

The layout is read in TypoScript with ``flexform:pi_flexform:layout`` and
is available in the templates as ``{t3sVarsLayout}``.

Place your own templates or partials in your extension and add the paths
with a higher index:

..  code-block:: typoscript

    tt_content.t3s_address.partialRootPaths.1770 = EXT:my_extension/Resources/Private/Partials/
    tt_content.t3s_address.templateRootPaths.20 = EXT:my_extension/Resources/Private/Templates/Content/


..  _developer-css:

Styling
-------

:file:`EXT:t3s_vars/Resources/Public/Css/contact.css` is loaded by all
layouts of the content element. Spacing between the address blocks:

..  code-block:: css

    .t3s-address {
      --t3s-address-gap: 1.5rem;
      --t3s-address-item-gap: 1rem;
      --t3s-address-vat-gap: 3rem;
    }

Colors of the layouts with map follow the surrounding text color, so light
and dark mode work without configuration. Links and buttons use the Bootstrap
primary/link color if available. Override them per site:

..  code-block:: css

    .t3s-contact {
      --t3s-contact-primary: currentColor;
      --t3s-contact-accent: #2563eb;
      --t3s-contact-muted: color-mix(in srgb, currentColor 72%, transparent);
      --t3s-contact-bg-light: color-mix(in srgb, currentColor 6%, transparent);
      --t3s-contact-btn-bg: #2563eb;
      --t3s-contact-btn-color: #fff;
      --t3s-contact-padding: 2rem;
    }

..  _developer-rte:

RTE integration
===============

The event listener for ``AfterPrepareConfigurationForEditorEvent`` adds
the CKEditor 5 plugin, the toolbar item and the values to **every** preset.
A custom preset is not required.

To set the position of the button, add ``t3sVars`` to the toolbar of your
preset – it is then not appended a second time:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/RTE/Default.yaml

    editor:
      config:
        toolbar:
          items:
            - bold
            - t3sVars

The button is not shown if the site has no variables with a value. It can
be disabled completely with :confval:`extconf-rtebutton`.
