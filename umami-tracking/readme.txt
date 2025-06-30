=== Umami Tracking ===
Contributors: alanef
Donate link: https://alanefortune.com
Tags: umami, analytics, tracking
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that adds the Umami tracking script to your website.

== Description ==

This plugin adds the Umami tracking script to the `<head>` of your site. It provides a settings page where you can easily configure your Umami Website ID.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/umami-tracking` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->Umami Tracking screen to configure the plugin

== Frequently Asked Questions ==

= How do I find my Website ID? =

You can find your Website ID in your Umami account under the settings for your website.

== Screenshots ==

1. The Umami Tracking settings page.

== Changelog ==

= 1.0.2 =
* Fixed release workflow to properly build plugin zip
* Updated build process to match other plugins

= 1.0.1 =
* Fixed script injection using wp_enqueue_script with script_loader_tag filter
* Added defer attribute and data-website-id to tracking script
* Updated WordPress compatibility to 6.8
* Added language POT file

= 1.0.0 =
* Initial release.
