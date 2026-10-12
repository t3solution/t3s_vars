..  _known-problems:

==============
Known problems
==============

Caching
    Replaced texts are cached with the page. Flush the frontend cache after
    changing a value.

Nested links
    A linked placeholder (phone, email, website) inside a link set by an
    editor results in nested ``<a>`` tags. Use ``{{phone:text}}`` inside
    links.

Exact notation
    ``{{ company }}`` (with spaces) is not replaced. Neither is a
    placeholder with formatting in the middle, e.g.
    ``{{<strong>company</strong>}}``. The RTE dropdown applies the current
    formatting to the whole placeholder.

HTML attributes
    Placeholders in HTML attributes (``href``, ``title`` …) are not
    replaced, only in text.

Header fields
    Headers are not processed by ``parseFunc``. Override the header
    partial with ``<vars:replace>``, see
    :ref:`developer-viewhelper-headers`.

postUserFunc
    The extension sets ``nonTypoTagStdWrap.postUserFunc`` on
    ``lib.parseFunc_RTE`` and ``lib.parseFunc``. If another extension sets
    the same property, the set loaded last wins.

Content Security Policy
    If CSP is active in the frontend, allow
    ``frame-src https://www.google.com`` for the map.

Translations and TypoScript constants
    :confval:`t3svars-translations` applies to placeholders, ViewHelpers,
    the content element and JSON-LD, but not to TypoScript constants
    ``{$vars.*}`` and ``{site.settings.vars.*}`` – these always show the
    value of the site settings.

Single-line fields
    The settings editor shows opening hours and translations as single-line
    fields. Separate entries with ``;`` (opening hours inside a translation
    with ``|``).
