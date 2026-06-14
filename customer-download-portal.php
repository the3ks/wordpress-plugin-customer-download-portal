<?php
/**
 * Plugin Name: Customer Download Portal
 * Description: Customer portal for admin-granted digital product downloads, licenses, release notes, documentation, and support links. No checkout required.
 * Version: 2.0.0
 * Author: VACIF / Custom
 * Text Domain: customer-download-portal
 */

if (!defined('ABSPATH')) exit;

define('CDP_VERSION', '2.0.0');
define('CDP_PLUGIN_FILE', __FILE__);
define('CDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CDP_DOWNLOAD_DIR', WP_CONTENT_DIR . '/customer-download-files');

require_once CDP_PLUGIN_DIR . 'includes/helpers.php';
require_once CDP_PLUGIN_DIR . 'includes/install.php';
require_once CDP_PLUGIN_DIR . 'includes/admin-products.php';
require_once CDP_PLUGIN_DIR . 'includes/admin-assignments.php';
require_once CDP_PLUGIN_DIR . 'includes/admin-licenses.php';
require_once CDP_PLUGIN_DIR . 'includes/admin-settings.php';
require_once CDP_PLUGIN_DIR . 'includes/admin-help.php';
require_once CDP_PLUGIN_DIR . 'includes/shortcode-portal.php';
require_once CDP_PLUGIN_DIR . 'includes/download-handler.php';

register_activation_hook(__FILE__, 'cdp_activate_plugin');
register_deactivation_hook(__FILE__, 'cdp_deactivate_plugin');

add_action('admin_init', 'cdp_maybe_upgrade_plugin');
add_action('admin_menu', 'cdp_register_admin_menu');
add_action('init', 'cdp_handle_download_request');
if (function_exists('cdp_handle_asset_download_request')) {
    add_action('init', 'cdp_handle_asset_download_request');
}
add_shortcode('customer_downloads', 'cdp_customer_downloads_shortcode');

function cdp_register_admin_menu() {
    add_menu_page('Customer Downloads', 'Customer Downloads', 'manage_options', 'cdp-products', 'cdp_render_products_page', 'dashicons-download', 56);
    add_submenu_page('cdp-products', 'Products & Versions', 'Products', 'manage_options', 'cdp-products', 'cdp_render_products_page');
    add_submenu_page('cdp-products', 'Assignments', 'Assignments', 'manage_options', 'cdp-assignments', 'cdp_render_assignments_page');
    add_submenu_page('cdp-products', 'Licenses', 'Licenses', 'manage_options', 'cdp-licenses', 'cdp_render_licenses_page');
    add_submenu_page('cdp-products', 'Settings', 'Settings', 'manage_options', 'cdp-settings', 'cdp_render_settings_page');
    add_submenu_page('cdp-products', 'Help / Guide', 'Help / Guide', 'manage_options', 'cdp-help', 'cdp_render_help_page');
}
