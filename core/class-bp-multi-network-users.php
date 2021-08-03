<?php

/**
 * Manage Users for a network
 */
class BP_Multi_Network_Users {

	/**
	 * Checks if the user exists in the given network.
	 *
	 * @param int $user_id user id.
	 * @param int $network_id network id.
	 *
	 * @return bool
	 */
	public static function exists( $user_id, $network_id ) {
		global $wpdb;
		$table_users = mnetwork_get_table_name();

		$query = "SELECT COUNT('*') FROM {$table_users} WHERE user_id = %d AND network_id = %d";

		return (bool) $wpdb->get_var( $wpdb->prepare( $query, $user_id, $network_id ) );
	}


	/**
	 * Retrieves the list of users who are member of this network
	 *
	 * @param int $network_id network id.
	 *
	 * @return array
	 */
	public static function get_users( $network_id ) {
		global $wpdb;
		$table_users = mnetwork_get_table_name();
		$query       = "SELECT user_id FROM {$table_users} WHERE network_id = %d";
		$users       = $wpdb->get_col( $wpdb->prepare( $query, $network_id ) );

		return $users;
	}

	/**
	 * Retrieves networks for the user.
	 *
	 * @param int $user_id user id.
	 *
	 * @return array
	 */
	public static function get_networks( $user_id ) {
		global $wpdb;

		$table_users = mnetwork_get_table_name();

		$query    = "SELECT DISTINCT(network_id) FROM {$table_users} WHERE user_id = %d";
		$networks = $wpdb->get_col( $wpdb->prepare( $query, $user_id ) );

		return $networks;
	}

	/**
	 * Retrieves the total number of users in the given network.
	 *
	 * @param int $network_id network id.
	 *
	 * @return int
	 */
	public static function get_network_users_count( $network_id ) {
		global $wpdb;
		$table_users = mnetwork_get_table_name();

		$query = "SELECT COUNT(DISTINCT(user_id)) FROM {$table_users} WHERE network_id = %d";

		$networks = $wpdb->get_var( $wpdb->prepare( $query, $network_id ) );

		return $networks;
	}

	/**
	 * Adds user to a network
	 *
	 * @param int $user_id user id.
	 * @param int $network_id network id.
	 *
	 * @return bool
	 */
	public static function add_user( $user_id, $network_id ) {
		if ( empty( $user_id ) || empty( $network_id ) ) {
			return false;
		}

		global $wpdb;

		$table_users = mnetwork_get_table_name();

		$query = "INSERT INTO {$table_users} SET user_id=%d, network_id = %d";

		$wpdb->query( $wpdb->prepare( $query, $user_id, $network_id ) );

		return true;
	}

	/**
	 * Removes a user from network.
	 *
	 * @param int $user_id user id.
	 * @param int $network_id network id.
	 *
	 * @return bool
	 */
	public static function remove_user( $user_id, $network_id = 0 ) {
		if ( empty( $user_id ) && empty( $network_id ) ) {
			return false;
		}
		global $wpdb;

		$table_users = mnetwork_get_table_name();

		$where_conditions = array();
		$where_sql        = '';
		if ( ! empty( $user_id ) ) {
			$where_conditions[] = $wpdb->prepare( 'user_id = %d', $user_id );
		}

		if ( ! empty( $network_id ) ) {
			$where_conditions[] = $wpdb->prepare( 'network_id = %d', $network_id );
		}

		$where_sql = join( ' AND ', $where_conditions );

		$query = "DELETE FROM {$table_users} WHERE {$where_sql}";

		$wpdb->query( $wpdb->prepare( $query, $user_id, $network_id ) );

		return true;
	}

	/**
	 * Removes users from a network.
	 *
	 * @param int $network_id network id.
	 *
	 * @return bool
	 */
	public static function remove_network( $network_id ) {

		if ( empty( $network_id ) ) {
			return false;
		}

		global $wpdb;

		$table_users = mnetwork_get_table_name();

		$query = "DELETE FROM {$table_users} WHERE network_id = %d";

		$wpdb->query( $wpdb->prepare( $query, $network_id ) );

		return true;
	}
}

// for backward compatibility, alias.
class_alias( 'BP_Multi_Network_Users', 'BPNetworkUsers' );
