<?php
/**
 * User related functions.
 *
 * @package    buddypress-multi-network
 * @copyright  Copyright (c) 2019, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Add user to a network
 *
 * @param int $user_id user id.
 * @param int $network_id network id.
 *
 * @return bool
 */
function mnetwork_add_user( $user_id, $network_id ) {
	return BPNetworkUsers::add_user( $user_id, $network_id );
}

/**
 * Remove a user from network.
 *
 * @param int $user_id user id.
 * @param int $network_id network id.
 *
 * @return bool
 */
function mnetwork_remove_user( $user_id, $network_id = false ) {
	return BPNetworkUsers::remove_user( $user_id, $network_id );
}

/**
 * Remove all users from network.
 *
 * @param int $network_id network id.
 *
 * @return bool
 */
function mnetwork_remove_network( $network_id ) {
	return BPNetworkUsers::remove_network( $network_id );
}

/**
 * Get the list of users member of this network
 *
 * @param int $network_id network id.
 *
 * @return array
 */
function mnetwork_get_users( $network_id ) {
	return BPNetworkUsers::get_users( $network_id );
}

/**
 * Get the all networks for a user.
 *
 * @param int $user_id user id.
 *
 * @return array
 */
function mnetwork_get_networks( $user_id ) {
	return BPNetworkUsers::get_networks( $user_id );
}

/**
 * Get total number of users in the given network.
 *
 * @param int $network_id network id.
 *
 * @return int
 */
function mnetwork_get_total_users( $network_id ) {
	return BPNetworkUsers::get_network_users_count( $network_id );
}
