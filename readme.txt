=== Native Gettext for Wordpress ===

Contributors: colinleroy
Tags: performance, gettext, translation
Requires at least: 4.7
Tested up to: 6.6.1
Requires PHP: 5.3
Stable tag: 1.1.4
Donate link: https://paypal.me/colinleroymira
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A very light wrapper plugin that uses the native gettext library for faster localization.

== Description ==

This plugin has no setting or UI. It just does one thing : use the php-gettext library to handle translations. This results in much faster translation than using the default PHP gettext implementation of Wordpress.

If the extension is not available, the plugin will simply do nothing.

You can verify that the plugin is working by looking for an HTTP header: X-Native-Gettext: 1, that will be sent as soon as a string will be translated by the native library.

If your Wordpress install is in English, this plugin has no purpose. Otherwise, it can help speed up your install quite a lot.

Requirements:
- php-gettext
- OS-level support for the locale(s) you want to translate to.
