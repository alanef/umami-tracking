<?php
/**
 * Plugin Name: Umami Tracking
 * Plugin URI:  https://github.com/alanef/umami-tracking
 * Description: Adds Umami tracking script to your website.
 * Version:     1.0.0
 * Author:      Alan E. Fortune
 * Author URI:  https://alanefortune.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: umami-tracking
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'UMAMI_TRACKING_VERSION', '1.0.0' );
define( 'UMAMI_TRACKING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once UMAMI_TRACKING_PLUGIN_DIR . 'includes/class-umami-tracking.php';

function umami_tracking() {
    return Umami_Tracking::instance();
}

add_action( 'plugins_loaded', 'umami_tracking' );
