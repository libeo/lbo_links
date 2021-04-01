.. include:: ../Includes.txt

.. _configuration:

=============
Configuration
=============

Define a new type of link
===============

The extension come with already defined file link and external link template, but you can define your own types of links.

In this example, if combinaison of *btn btn-default content-link-more* classes are used on a link, the template *Button.html* of *lbo_links* will be used.

.. code-block:: typoscript
    plugin.tx_lbolinks.types.button {
        condition {
            class = btn btn-default content-link-more
        }
        rendering < plugin.tx_lbolinks.renderer
        rendering.templateName = button
    }
