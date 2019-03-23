<?php
/**
 * Various hooks to manipulate the install
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Filters.
 */
class BPMultiNetworkFilter {

	/**
	 * Singleton.
	 *
	 * @var BPMultiNetworkFilter
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {

		// scope tables.
		add_filter( 'bp_core_get_table_prefix', array( $this, 'filter_bp_table_prefix' ) );

		// scope the user meta key.
		add_filter( 'bp_get_user_meta_key', array( $this, 'filter_user_meta_key' ) );
		// use update_option instead of update_site_option for the bpdb version.
		add_filter( 'pre_update_site_option_bp-db-version', array( $this, 'filter_bpdb_update_version' ), 10, 3 );
		// use get_option instead of get_site_option for bpdb version.
		add_filter( 'site_option_bp-db-version', array( $this, 'filter_bpdb_get_version' ) );

		// now let us scope users to a network/blog.
		// filter total users sql.
		// add_filter('bp_core_get_total_users_sql', array($this, 'filter_total_users_sql'), 10, 2);
		// filter get users queries
		// filter query for get users/total users
		// add_filter('bp_core_get_paged_users_sql', array($this, 'filter_paged_users_sql'), 10, 2);
		// add_filter('bp_core_get_total_member_count',array($this,'total_member_count'));
		// BuddyPress 1.7+.
		add_action( 'bp_pre_user_query', array( $this, 'users_filter' ) );
		add_filter( 'bp_core_get_active_member_count', array( $this, 'filter_total_user_count' ) );
	}

	/**
	 * Get the singleton.
	 *
	 * @return BPMultiNetworkFilter
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Filter user meta key and prefix it to make network specific.
	 *
	 * @param string $key key.
	 *
	 * @return string
	 */
	public function filter_user_meta_key( $key ) {

		$network_id = get_current_blog_id();
		$key_prefix = "network_{$network_id}_";

		return $key_prefix . $key;
	}

	/**
	 * Filter bp-db-version and use get_option instead of get_site_option
	 * this will force bp to consider each blog as having their own db
	 *
	 * @param string $val version.
	 *
	 * @return string
	 */
	public function filter_bpdb_get_version( $val ) {

		$version = get_option( 'bp-db-version' );

		return $version;
	}


	/**
	 * Filter update site option to save the bp-db-version in blog meta and not in the site meta, it will make it per blog instead of per MS install
	 *
	 * @param string $value new version.
	 * @param string $oldvalue old version.
	 *
	 * @return mixed
	 */
	public function filter_bpdb_update_version( $value, $oldvalue ) {
		update_option( 'bp-db-version', $value );

		return $value;
	}

	/**
	 * Filter table prefix.
	 *
	 * @param string $prefix prefix.
	 *
	 * @return string
	 */
	public function filter_bp_table_prefix( $prefix ) {

		global $wpdb;

		// return current blog database prefix instead of site prefix.
		return $wpdb->prefix;
	}

	/**
	 * Filter total users sql
	 * An extra IN {user list} will not cause any har in case the $include/$friends is specified
	 *
	 * @param string $sql query.
	 * @param array  $sql_array sql array.
	 *
	 * @return string
	 */
	public function filter_total_users_sql( $sql, $sql_array ) {

		// if you want to filter on the main site too, please comment the next two line.
		if ( is_main_site() ) {
			return $sql;
		}


		$blog_id = get_current_blog_id();
		$users   = mnetwork_get_users( $blog_id );

		$list = '(' . join( ',', $users ) . ')';

		// since $type will be always passed to get users, we can safely assume this.
		$order_by = array_pop( $sql_array );


		$sql_array['where_network'] = " AND u.ID IN {$list}";
		array_push( $sql_array, $order_by );
		$sql = join( ' ', $sql_array );

		return $sql;
	}

	/**
	 * Filter total user count.
	 *
	 * @param int $count count.
	 *
	 * @return int
	 */
	public function filter_total_user_count( $count ) {
		if ( is_main_site() ) {
			return $count;
		}//on main site, we don't need to worry about the count change

		$blog_id = get_current_blog_id();

		$count = mnetwork_get_total_users( $blog_id );

		// get the total users count for current buddypress network.
		return $count;

	}

	/**
	 * User list filters.
	 *
	 * @param \BP_User_Query $query_obj query object.
	 */
	public function users_filter( $query_obj ) {

		if ( is_main_site() ) {
			return;
		}

		$uid_where = $query_obj->uid_clauses['where'];

		$blog_id = get_current_blog_id();

		$users = mnetwork_get_users( $blog_id );

		if ( empty( $users ) ) {
			// if no users found, let us fake it.
			$users = array( 0 => 0 );
		}


		$list = '(' . join( ',', $users ) . ')';

		if ( $uid_where ) {
			$uid_where .= " AND u.{$query_obj->uid_name} IN {$list}";
		} else {
			$uid_where = "WHERE u.{$query_obj->uid_name} IN {$list}";
		}//we are treading a hard line here

		$query_obj->uid_clauses['where'] = $uid_where;
	}

	/**
	 * Pre BP 1.7 filter.
	 *
	 * @param string $sql clause.
	 * @param array  $sql_array clause array.
	 *
	 * @return string
	 */
	public function filter_paged_users_sql( $sql, $sql_array ) {
		// if u want to scope on main site, please comment the next 2 lines.
		if ( is_main_site() ) {
			return $sql;
		}
		// do not filter user list on amin site
		// if on sub network site, let us filter users.
		$blog_id = get_current_blog_id();
		$users   = mnetwork_get_users( $blog_id );

		if ( empty( $users ) ) {
			// if no users found, let us fake it.
			$users = array( 0 => 0 );
		}

		$list = '(' . join( ',', $users ) . ')';

		if ( ! empty( $sql_array['pagination'] ) ) {
			$pagination = array_pop( $sql_array );
		}

		// since $type will be always passed to get users, we can safely assume this.
		$order_by = array_pop( $sql_array );


		$sql_array['where_network'] = " AND u.ID IN {$list}";

		array_push( $sql_array, $order_by );

		if ( ! empty( $pagination ) ) {
			array_push( $sql_array, $pagination );
		}

		$sql = join( ' ', $sql_array );

		return $sql;
	}

	/**
	 * Pre BP 1.7 total number filter.
	 *
	 * @param int $count count.
	 *
	 * @return int
	 */
	public function total_member_count( $count ) {
		if ( is_main_site() ) {
			return $count;
		}
		global $wpdb;
		$blog_id = get_current_blog_id();

		if ( ! $count = wp_cache_get( 'bp_total_member_count_' . $blog_id, 'bp' ) ) {
			$status_sql = bp_core_get_status_sql();
			$list_users = mnetwork_get_users( $blog_id );
			$list       = '(' . join( ',', $list_users ) . ')';

			$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users WHERE {$status_sql} and ID IN {$list}" );
			wp_cache_set( 'bp_total_member_count_' . $blog_id, $count, 'bp' );
		}

		return $count;
	}

}

// initialize filters.
BPMultiNetworkFilter::get_instance();

/**
 * User attachment handler.
 */
class BPMultiNetworkActions {

	/**
	 * Singleton.
	 *
	 * @var BPMultiNetworkActions
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
	 * @return BPMultiNetworkActions
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


// when  user is deleted by any means,
// we should find all his network and delete all data.
// This needs to be added in future.
// Init.
BPMultiNetworkActions::get_instance();

