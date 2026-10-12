..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

Company name, address, phone number or email address appear on many pages
of a website. With :composer:`t3s/vars` these values are maintained in one
place – the site settings – and inserted wherever they are needed:

..  code-block:: text
    :caption: Text in the rich text editor

    Call {{company}}: {{phone}}

In the frontend the placeholders are replaced by the current values. Phone
numbers, email addresses and websites are linked automatically.

..  _live-demo:

Live demo
=========

All layouts of the content element "Address" and examples of the placeholders
can be seen on the `live demo page <https://www.t3sbootstrap.de/extensions/globale-variablen>`__.

..  _features:

Features
========

*   Values are maintained as **site settings** – different per site
*   Placeholders in the **RTE** and wherever ``lib.parseFunc`` /
    ``lib.parseFunc_RTE`` is used
*   **CKEditor 5 dropdown "Variables"** – added automatically to every
    preset (core or custom)
*   **Automatic links**: phone → ``tel:``, email → ``mailto:`` with spam
    protection, website → URL
*   **Content element "Address"** with the layouts *Compact*,
    *Compact (address & map)*, *Split* (contact + Google map) and
    *Split (contact only)*, ``<address>`` with schema.org markup
*   **Google Maps**: route button to the Google Business profile, optional
    map with two-click privacy
*   **Fluid ViewHelpers** ``<vars:get>`` and ``<vars:replace>``, registered
    globally
*   **Opening hours** in schema.org format, shown in the page language
*   **Social media profiles** (Facebook, Instagram, LinkedIn, XING,
    YouTube, X, TikTok, Mastodon) as icon links
*   **vCard download** – "Save contact" link in the content element
*   **JSON-LD** (``LocalBusiness`` or any schema.org type) in the
    ``<head>`` of every page – optional
*   **Multilingual sites**: translations of single values per language
*   Values available as **TypoScript constants** and in **PAGEVIEW**
*   **Custom variables** via :file:`settings.definitions.yaml` – no code
    required
*   No database tables or fields required (the layout of the content
    element is stored in ``pi_flexform``), no custom RTE preset required

..  _requirements:

Requirements
============

*   TYPO3 14.3 or later
*   PHP 8.2 or later
*   CKEditor 5 (system extension ``rte_ckeditor``)

No theme extension is required. The content element "Address" uses the
``Default`` layout of the content elements, e.g. from fluid_styled_content.
