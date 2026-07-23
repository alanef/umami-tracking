=== Umami Tracking ===
Contributors: alanef
Donate link: https://ko-fi.com/wpalan
Tags: umami, analytics, tracking
Requires at least: 5.0
Tested up to: 7.1
Stable tag: 1.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that adds the Umami tracking script to your website.

== Description ==

This plugin adds the Umami tracking script to your WordPress site with advanced configuration options.

Features:
* Custom tracker URL support for self-hosted Umami instances
* Automatic external link click tracking
* Custom event tracking script - add your own JavaScript after the tracking tag to capture custom events
* Self-exclusion toggle using localStorage (per Umami documentation)
* Admin bar tracking toggle for logged-in users
* Role-based tracking exclusion (administrators and editors excluded by default)
* Respect Do Not Track browser setting
* Domain restrictions for multi-site setups
* Custom data collection endpoint support
* Cookie-less tracking (Umami's default behavior)
* GDPR compliant - no personal data collection

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

= 1.2.2 =
* Added GitHub Plugin URI header so updates can be delivered from GitHub via Git Updater (afragen/git-updater).
* Updated the donation link.

= 1.2.1 =
* Custom Event Tracking Script now detects up front when the account lacks the `unfiltered_html` capability (removed by some security plugins, or limited to Super Admins on Multisite): the field is shown read-only with a clear explanation, and a save that would be blocked now shows an error instead of silently discarding the change.
* Fixed the field placeholder that showed a full example script, which made an empty (unsaved) field look identical to a saved one. The example now lives below the field as copyable documentation.

= 1.2.0 =
* Added custom event tracking script setting - output your own JavaScript immediately after the Umami tag to capture custom events (e.g. listen for DOM events and call window.umami.track())

= 1.1.1 =
* Fixed script enqueueing to use proper WordPress methods (plugin check compliance)
* Fixed self-exclusion floating button to hide for logged-out users when tracking is OFF
* Added debug logging using console.debug instead of console.log
* Improved settings descriptions for clarity
* Fixed PHPCS security warnings

= 1.1.0 =
* Added custom tracker URL setting for self-hosted Umami instances
* Added Do Not Track browser setting support
* Added domain restriction option for staging/development environments
* Added custom host URL for data collection endpoint override
* Added automatic external link click tracking
* Added self-exclusion feature using localStorage (per Umami docs)
* Added admin bar toggle for tracking on/off
* Added optional floating button for self-exclusion
* Moved external link tracking to separate JS file (WordPress compliance)
* Improved settings page with better organization
* Changed default tracker URL to use example.com

= 1.0.3 =
* Added role-based tracking exclusion feature
* Administrators and Editors are excluded from tracking by default
* Settings page now allows selecting which roles to exclude
* Improved privacy for site administrators

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
