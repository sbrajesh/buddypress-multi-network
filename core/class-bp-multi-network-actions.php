<?php
/**
 * Action handler.
 *
 * @package    buddypress-mult-network
 * @copyright  Copyright (c) 2019, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * User attachment handler.
 */
class BP_Multi_Network_Actions {

	/**
	 * Singleton.
	 *
	 * @var BP_Multi_Network_Actions
	 */
	private static $instance;

	/**
	 * Constructor
	 */
	private function __construct() {
		// update netwok users table on new signup activation.
		add_action( 'wpmu_activate_user', array( $this, 'on_user_activation' ), 10, 3 );
		// update table on add_user_to_blog(when an existing user is added to a network or a new user creates a network).
		add_action( 'add_user_to_blog', array( $this, 'on_add_to_network' ), 10, 3 );
		// update on remove user from blog.
		add_action( 'remove_user_from_blog', array( $this, 'on_removal_from_network' ), 10, 2 );

		// on account deletion.
		add_action( 'deleted_user', array( $this, 'on_account_delete' ) );
		// on mark spam.
		add_action( 'bp_make_spam_user', array( $this, 'on_account_delete' ) );

		// what about when a network is marked as spam?
		//on blog spam?
		//add_action('make_spam_blog','mnetwork_update_on spam_blog');//$blog_id
	}

	/**
	 * Get the singleton instance
	 *
	 * @return BP_Multi_Network_Actions
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add user on account activation.
	 *
	 * @param int    $user_id user id.
	 * @param string $pass password.
	 * @param array  $meta meta.
	 */
	public function on_user_activation( $user_id, $pass, $meta ) {
		$blog_id = get_current_blog_id();
		mnetwork_add_user( $user_id, $blog_id );
	}

	/**
	 * Add user to table when they are added to a network.
	 *
	 * @param int    $user_id user id.
	 * @param string $role role.
	 * @param int    $blog_id blog id.
	 */
	public function on_add_to_network( $user_id, $role, $blog_id ) {
		mnetwork_add_user( $user_id, $blog_id );
	}

	/**
	 * Remove user from table.
	 *
	 * @param int $user_id user id.
	 * @param int $blog_id blog id.
	 */
	public function on_removal_from_network( $user_id, $blog_id ) {
		// should not the user data should be removed for this network?
		// we don't do that currently.
		mnetwork_remove_user( $user_id, $blog_id );
	}

	/**
	 * Remove user from table on account delete.
	 *
	 * @param int $user_id user id.
	 */
	public function on_account_delete( $user_id ) {

		// remove user from all the networks.
		// how about deleting user data from all the networks?
		mnetwork_remove_user( $user_id );
	}
}

// backward compatibility.
class_alias( 'BP_Multi_Network_Actions', 'BPMultiNetworkActions' );

// when  user is deleted by any means,
// we should find all his network and delete all data.
// This needs to be added in future.
// Init.
BP_Multi_Network_Actions::get_instance();
