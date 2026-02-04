<?php
/**
 * Plugin Name: Listings
 * Plugin URI: https://github.com/nonatech-uk/wp-listings
 * Description: Display categorized listings (facilities, businesses, events, etc.) with metadata-driven content and flexible shortcode options.
 * Version: 1.0.0
 * Author:
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: listings
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'PARISH_LISTINGS_VERSION', '1.0.1' );
define( 'PARISH_LISTINGS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PARISH_LISTINGS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the main class
require_once PARISH_LISTINGS_PLUGIN_DIR . 'includes/class-parish-listings.php';
require_once PARISH_LISTINGS_PLUGIN_DIR . 'includes/class-github-updater.php';

/**
 * Initialize the plugin
 */
function parish_listings_init() {
    Parish_Listings::get_instance();

    // Initialize GitHub updater
    if (is_admin()) {
        new Listings_GitHub_Updater(
            __FILE__,
            'nonatech-uk/wp-listings',
            PARISH_LISTINGS_VERSION
        );
    }
}
add_action( 'plugins_loaded', 'parish_listings_init' );

/**
 * Activation hook - flush rewrite rules
 */
function parish_listings_activate() {
    // Instantiate class to register CPT
    Parish_Listings::get_instance();
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'parish_listings_activate' );

/**
 * Deactivation hook - flush rewrite rules
 */
function parish_listings_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'parish_listings_deactivate' );
