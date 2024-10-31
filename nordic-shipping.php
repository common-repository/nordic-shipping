<?php
/**
 * The plugin file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://uniwin.se
 * @since             1.0.1
 * @package           Nordic_Shipping
 *
 * @wordpress-plugin
 * Plugin Name:       Nordic Shipping
 * Plugin URI:        https://uniwin.se
 * Description:       Nordic Shipping Plugin is a connector app for syncing shipping details from woocommerce store to your Shipit account.
 * Version:           1.0.1
 * Author:            uniwin
 * Author URI:        https://uniwin.se
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nordic-shipping
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('NODS_VERSION', '1.0.1');

// Check if WooCommerce is active (requires wodpress 2.5.0)
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
function shipit_uniwin_woocommerce_cant_find()
{
	printf(
		'<div class="notice notice-error"><p>'
			. '<strong>%s ERROR:</strong> <span>%s</span></p></div>',
		(string) esc_html__('Nordic Shipping', 'nordic-shipping'),
		(string) esc_html__(
			"Can't find WooCommerce, is it installed and activated?",
			'nordic-shipping'
		)
	);
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nordic-shipping-activator.php
 */
function NODS_activate_nordic_shipping()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-NODS-activator.php';
	NODS_Activator::activate();
}

register_activation_hook(__FILE__, 'NODS_activate_nordic_shipping');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nordic-shipping-deactivator.php
 */
function NODS_deactivate_nordic_shipping()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-NODS-deactivator.php';
	NODS_Deactivator::deactivate();
}


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-NODS-main.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function NODS_run_nordic_shipping()
{
	$plugin = new NODS_Main();
	$plugin->run();
}

add_action('admin_init', 'shipit_activation_condition_the_plugins');
function shipit_activation_condition_the_plugins()
{
	if (!function_exists('is_woocommerce_activated')) {
		if (class_exists('WooCommerce')) {
			activate_plugins(
				array(
					'/nordic-shipping/nordic-shipping.php'
				),
				'', // redirect url, does not matter (default is '')
				false, // network wise
				true // silent mode (no activation hooks fired)
			);
		} else {
			add_action(
				'admin_notices',
				'shipit_uniwin_woocommerce_cant_find'
			);
			deactivate_plugins( // deactivate for media_manager
				array(
					'/nordic-shipping/nordic-shipping.php'
				),
				true, // silent mode (no deactivation hooks fired)
				false // network wide
			);
			register_deactivation_hook(__FILE__, 'NODS_deactivate_nordic_shipping');
?>
			<style>
				div#message {
					display: none;
				}
			</style>
<?php
		}
	}
}

// include the admin settings file

include(plugin_dir_path(__FILE__) . 'admin/settings.php');
?>