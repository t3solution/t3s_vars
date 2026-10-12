..  _editor:

===========
For editors
===========

..  _editor-rte:

Placeholders in the rich text editor
====================================

Type a placeholder or insert it via the dropdown :guilabel:`Variables` in
the editor toolbar. The dropdown lists all variables that have a value,
each with label and current value – in the same order as in the content
element "Address".

..  figure:: /Images/rte-dropdown.png
    :alt: Dropdown "Variables" in the CKEditor toolbar
    :class: with-shadow

    Dropdown :guilabel:`Variables` in the CKEditor toolbar

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   -   Notation
        -   Output
    *   -   ``{{phone}}``
        -   Link, if the key is configured as link key – otherwise text
    *   -   ``{{phone:text}}``
        -   Always plain text, e.g. inside a link
    *   -   ``{{unknown}}``
        -   Stays unchanged – typos are easy to spot

Empty values are replaced by an empty string.

..  important::
    Do not set a link on a linked placeholder (phone, email, website). This
    results in nested ``<a>`` tags. Inside links use ``{{phone:text}}``.

..  _editor-address:

Content element "Address"
=========================

New content element wizard: :guilabel:`Default > Address (global
variables)`. The data comes from the site settings – changes there apply to
all address elements at once.

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   -   Field
        -   Use
    *   -   Header / Subheader
        -   *Compact*: normal header. *Split*: heading and small label
            above it, e.g. "Contact"
    *   -   Layout
        -   *Compact (address only)*, *Compact (address & map)* or
            *Split (contact + map)* or *Split (contact only, no map)*

..  important::
    The layouts with map need the Google Maps embed URL in the site
    settings (:confval:`t3svars-mapsembed`). The layout field shows this
    hint in the backend as well.

Opening hours (:confval:`vars-openinghours`) and social media links
(:ref:`configuration-social`) appear automatically below the contact details
as soon as they are maintained in the site settings. The link
:guilabel:`Save contact` downloads the contact for the address book
(:confval:`t3svars-vcard`).

..  _editor-address-compact:

Layout "Compact"
----------------

..  figure:: /Images/address-compact.png
    :alt: Layout Compact
    :class: with-shadow

    Layout *Compact* with icons (EXT:iconpack + Font Awesome)

Profession, owner, company, address (incl. address addition), phone, fax, email, website, VAT ID and the
route link as ``<address>`` with schema.org markup. Empty values are
omitted. If EXT:iconpack with Font Awesome is installed, icons are shown
instead of the labels.

..  _editor-address-compactmap:

Layout "Compact (address & map)"
--------------------------------

..  figure:: /Images/address-compact-map.png
    :alt: Layout Compact with map
    :class: with-shadow

    Layout *Compact (address & map)* – map with two-click privacy

The same address block as *Compact*, with the Google map below it in full
width. The map uses the same two-click privacy as *Split*. Without an embed
URL the layout looks like *Compact*.

..  _editor-address-split:

Layout "Split"
--------------

..  figure:: /Images/address-split.png
    :alt: Layout Split
    :class: with-shadow

    Layout *Split (contact + map)*

..  figure:: /Images/address-split-nomap.png
    :alt: Layout Split without map
    :class: with-shadow

    Layout *Split (contact only, no map)*

Two columns from 992 px: on the left subheader, header, the blocks
*Headquarters* and *Direct contact* and the button
:guilabel:`Plan route on Google Maps`; on the right the Google map. Without
an embed URL the section is single-column.

The layout *Split (contact only, no map)* uses the same design but never
shows the map – e.g. if the map is already shown elsewhere on the page.

With two-click privacy enabled the map shows a notice and the button
:guilabel:`Load map`. The map is only loaded after this click.

..  hint::
    The header layout *Hidden* hides the heading in both layouts.
