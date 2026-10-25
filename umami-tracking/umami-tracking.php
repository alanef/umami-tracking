<?php
/**
 * Plugin Name: Umami Tracking
 * Plugin URI:  https://github.com/alanef/umami-tracking
 * Description: Adds Umami tracking script to your website.
 * Version:     1.2.2
 * Author:      Alan E. Fortune
 * Author URI:  https://alanefortune.com
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Update URI:        https://github.com/alanef/umami-tracking
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: umami-tracking
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'UMAMI_TRACKING_VERSION', '1.2.2' );
define( 'UMAMI_TRACKING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once UMAMI_TRACKING_PLUGIN_DIR . 'includes/class-umami-tracking.php';

function umami_tracking() {
    return Umami_Tracking::instance();
}

add_action( 'plugins_loaded', 'umami_tracking' );

// Self-update from GitHub releases. Managed by wordpress-plugin-boilerplate/tooling: present only
// while readme.txt has no Type: header (GitHub-only release).
if ( file_exists( __DIR__ . '/includes/class-github-updater.php' ) ) {
	require_once __DIR__ . '/includes/class-github-updater.php';
}
