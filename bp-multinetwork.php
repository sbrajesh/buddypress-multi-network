<?php
/**
 * Plugin Name: BuddyPress Multi Network
 * Plugin URI: http://buddydev.com/plugins/buddypress-multi-network/
 * Version:1.0.2
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
class BPMultiNetworkHelper {

	/**
	 * Singleton.
	 *
	 * @var BPMultiNetworkHelper
	 */
	private static $instance;

	/**
	 * Get singleton instance.
	 *
	 * @return BPMultiNetworkHelper
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
		include_once( BP_MNETWORK_DIR . 'core/class-bp-mn-actions.php' );
		include_once( BP_MNETWORK_DIR . 'core/class-bp-mn-filters.php' );
		include_once( BP_MNETWORK_DIR . 'core/bp-multi-network-user-functions.php' );
		include_once( BP_MNETWORK_DIR . 'users.php' );

		// to install and create required tables.
		if ( is_network_admin() ) {
			include_once BP_MNETWORK_DIR . 'install.php';
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

BPMultiNetworkHelper::get_instance();

/**
 * Get the user->network mapping table.
 *
 * @return string
 */
function mnetwork_get_table_name() {

	$helper = BPMultiNetworkHelper::get_instance();

	return $helper->get_table_name();
}
