..  _installation:

============
Installation
============

..  _installation-composer:

Install the extension
=====================

..  code-block:: bash
    :caption: Composer mode

    composer require t3s/vars

In classic mode install the extension ``t3s_vars`` via the extension
manager.

..  _installation-cache:

Flush the caches
================

No database update is required. Flush the caches:

..  code-block:: bash

    vendor/bin/typo3 cache:flush

..  _installation-site-set:

Add the site set
================

Add the site set **T3S Vars - Global variables** (``t3s/vars``) to your
site, either in the backend module :guilabel:`Sites > Setup > Edit site`
or in the site configuration:

..  code-block:: yaml
    :caption: config/sites/<site>/config.yaml

    dependencies:
      - t3s/vars

Then maintain the values as described in :ref:`configuration`.
