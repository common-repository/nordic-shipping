<?php

/**
 * Fired during plugin activation
 *
 * @link       https://uniwin.se
 * @since      1.0.0
 *
 * @package    Nordic_Shipping
 * @subpackage Nordic_Shipping/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Nordic_Shipping
 * @subpackage Nordic_Shipping/includes
 * @author     uniwin <test@gmail.com>
 */
class NODS_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		global $wpdb;
		$db_table_name_3 = $wpdb->prefix . 'shipit_uniwin_shipping_mapping';
		$charset_collate = $wpdb->get_charset_collate();
		// Prepare the SQL query using $wpdb->prepare()
		$sql = $wpdb->prepare("SHOW TABLES LIKE %s", $db_table_name_3);

		// Check if the table exists
		if ($wpdb->get_var($sql) != $db_table_name_3) {
			$sql3 = "CREATE TABLE `$db_table_name_3` (
				`id` int(11) NOT NULL auto_increment,
				`shipping_method` int(15) NOT NULL,
				`service_id` varchar(100) NOT NULL,
				`created_at` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
		) $charset_collate;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql3);
		}
	}
}
