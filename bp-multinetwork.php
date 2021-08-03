<?php
/**
 * Plugin Name: BuddyPress Multi Network
 * Plugin URI: http://buddydev.com/plugins/buddypress-multi-network/
 * Version:1.0.3
 * Author:Brajesh Singh
 * Author URI: https://buddydev.com
 * Description: Helps you to Build multiple BuddyPress network on a WordPress Multisite/BuddyPress Install
 * License: GPL
 *
 * @package buddypress-multi-network
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

define( 'BP_MNETWORK_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Class BPMultiNetworkHelper
 */
class BP_Multi_Network_Helper {

	/**
	 * Singleton.
	 *
	 * @var BP_Multi_Network_Helper
	 */
	private static $instance;

	/**
	 * Get singleton instance.
	 *
	 * @return BP_Multi_Network_Helper
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// if MULTIBLOG is not enabled, dont do anything fancy.
		add_action( 'bp_loaded', array( $this, 'setup_network_component' ), 1 );
		include_once( BP_MNETWORK_DIR . 'core/class-bp-multi-network-actions.php' );
		include_once( BP_MNETWORK_DIR . 'core/class-bp-multi-network-filters.php' );
		include_once( BP_MNETWORK_DIR . 'core/class-bp-multi-network-users.php' );
		include_once( BP_MNETWORK_DIR . 'core/bp-multi-network-user-functions.php' );

		// to install and create required tables.
		if ( is_network_admin() ) {
			include_once BP_MNETWORK_DIR . 'core/bp-multi-network-install.php';
		}
	}

	/**
	 * Load.
	 */
	public function setup_network_component() {
		include_once BP_MNETWORK_DIR . 'class-bp-multi-network-component.php';
		buddypress()->mnetwork = BP_Multi_Network_Component::get_instance();
	}

	/**
	 * Get the user table name.
	 *
	 * @return string
	 */
	public function get_table_name() {
		global $wpdb;
		return $wpdb->base_prefix . 'bp_mnetwork_users';
	}
}

BP_Multi_Network_Helper::get_instance();

// backward compatibility.
class_alias( 'BP_Multi_Network_Helper', 'BPMultiNetworkHelper' );

/**
 * Get the user->network mapping table.
 *
 * @return string
 */
function mnetwork_get_table_name() {
	return BP_Multi_Network_Helper::get_instance()->get_table_name();
}
