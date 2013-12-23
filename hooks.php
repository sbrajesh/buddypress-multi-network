<?php

/**
 * Various hooks to manipulate the install
 */
//filter 
class BPMultiNetworkFilter {

    private static $instance;

    private function __construct() {

        //scope tables
        add_filter( 'bp_core_get_table_prefix', array( $this, 'filter_bp_table_prefix' ) );

        //scope the user meta key
        add_filter( 'bp_get_user_meta_key', array( $this, 'filter_user_meta_key' ) );
        //use update_option instead of update_site_option for the bpdb version
        add_filter( 'pre_update_site_option_bp-db-version', array( $this, 'filter_bpdb_update_version' ), 10, 3 );
        //use get_option instead of get_site_option for bpdb version
        add_filter( 'site_option_bp-db-version', array( $this, 'filter_bpdb_get_version' ) );

        //now let us scope users to a network/blog
        //filter total users sql
       // add_filter('bp_core_get_total_users_sql', array($this, 'filter_total_users_sql'), 10, 2);

        //filter get users queries
        //filter query for get users/total users
        //add_filter('bp_core_get_paged_users_sql', array($this, 'filter_paged_users_sql'), 10, 2);
        //add_filter('bp_core_get_total_member_count',array($this,'total_member_count'));
        //BuddyPress 1.7+
        add_action( 'bp_pre_user_query', array( $this, 'users_filter' ) );
        add_filter( 'bp_core_get_active_member_count', array( $this, 'filter_total_user_count' ) );
    }

    public static function get_instance() {
        if ( !isset( self::$instance ) )
            self::$instance = new self();

        return self::$instance;
    }

    function filter_user_meta_key( $key ) {
       
        $network_id = get_current_blog_id();
        $key_prefix = "network_{$network_id}_";
        return $key_prefix . $key;
    }

//filter bp-db-version and use get_option insdead of get_site_option
//this will force bp to consider each blog as having their own db
    function filter_bpdb_get_version( $val ) {

        $version = get_option( 'bp-db-version' );
        return $version;
    }

//filter update site option to save the bp-db-version in blog meta and not in the site meta, it will make it per blog instead of per MS install
    function filter_bpdb_update_version( $value, $oldvalue ) {
        update_option( 'bp-db-version', $value );
        return $value;
    }

    function filter_bp_table_prefix( $prefix ) {

        global $wpdb;
        return $wpdb->prefix; //return current blog database prefix instead of site prefix
    }

    /**
     * Filter total users sql
     * An extra IN {user list} will not cause any har incase the $include/$friends is specified
     * @global type $current_blog
     * @param type $sql
     * @param array $sql_array
     * @return type 
     */
    function filter_total_users_sql( $sql, $sql_array ) {
        
        //if you want to filter on the main site too, please comment the next two line
         if( is_main_site() )
            return $sql;
         
       
        $blog_id = get_current_blog_id();
        $users = mnetwork_get_users( $blog_id );
        
        $list = "(" . join(',', $users) . ")";

        $order_by = array_pop( $sql_array ); //since $type will be always passed to get users, we can safely assume this


        $sql_array['where_network'] = " AND u.ID IN {$list}";
        array_push( $sql_array, $order_by );
        $sql = join( ' ', $sql_array );
        
        return $sql;
    }
    //bp1.7+ total user count
    function filter_total_user_count( $count ) {
        if( is_main_site() )
            return $count;//on main site, we don't need to worry about the count change
        
         $blog_id = get_current_blog_id();
         
         $count = mnetwork_get_total_users( $blog_id );
         return $count;
         //get the total users count for current buddypress network
         
        
    }
    /** for 1.7+ user filtering*/
    
    public function users_filter( $query_obj ) {
         
        if( is_main_site() )
             return ;
         
        $uid_where = $query_obj->uid_clauses['where'];
        
        $blog_id = get_current_blog_id();
        
        $users = mnetwork_get_users( $blog_id );
        
        if( empty( $users ) ) {
            //if no users found, let us fake it
            $users = array( 0 => 0 );
        }
            
         
         $list = "(" . join( ',', $users ) . ")";

         if( $uid_where )
             $uid_where .= " AND u.{$query_obj->uid_name} IN {$list}";
        else
            $uid_where = "WHERE u.{$query_obj->uid_name} IN {$list}";//we are treading a hard line here

         $query_obj->uid_clauses['where'] = $uid_where;   
    }
    //pre 1.7
    function filter_paged_users_sql( $sql, $sql_array ) {
        //if u want to scope on main site, please comment the next 2 lines
        if( is_main_site() )
            return $sql;//do not filter user list on amin site
        //if on sub network site, let us filter users
        
        $blog_id = get_current_blog_id();
        $users = mnetwork_get_users( $blog_id );
      
        if( empty( $users ) ) {
            //if no users found, let us fake it
            $users = array( 0 => 0 );
        }
        
        $list = '(' . join( ',', $users ) . ')';
        
        if ( !empty( $sql_array['pagination'] ) )
            $pagination = array_pop( $sql_array );

        $order_by = array_pop( $sql_array ); //since $type will be always passed to get users, we can safely assume this


        $sql_array['where_network'] = " AND u.ID IN {$list}";
        
        array_push( $sql_array, $order_by );
        
        if ( !empty( $pagination ) )
            array_push( $sql_array, $pagination );
        
        $sql = join( ' ', $sql_array );
        //echo $sql;
        return $sql;
    }
    //pre 1.7
    function total_member_count( $count ) {
        if( is_main_site() )
            return $count;
          //  return bp_core_number_format( bp_core_get_total_member_count() );//on main site we have no issues sir
        //otherwise
        global $wpdb;
        $blog_id = get_current_blog_id();
        
	if ( !$count = wp_cache_get( 'bp_total_member_count_' . $blog_id, 'bp' ) ) {
		$status_sql = bp_core_get_status_sql();
                $list_users = mnetwork_get_users( $blog_id );
                $list = '(' . join( ',', $list_users ) . ')';
		
                $count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users WHERE {$status_sql} and ID IN {$list}"  );
		wp_cache_set( 'bp_total_member_count_'.$blog_id, $count, 'bp' );
	}

	return $count ;
    }

}
//initialize filters
BPMultiNetworkFilter::get_instance();
/**
 * If you are wondering why I use singleton patttern for most of the classe, here is a little details.
 * Using singletone assures that the actions/filters will not be applied more than once in any circumstances
 */
class BPMultiNetworkActions{
    private static $instance;
    
    private function __construct(){
        //update netwok users table on new signup activation
        add_action( 'wpmu_activate_user', array( $this, 'on_user_activation' ), 10, 3 );
        //update table on add_user_to_blog(when an existing user is added to a network or a new user creates a network)
        add_action( 'add_user_to_blog', array( $this, 'on_add_to_network' ), 10, 3 );
        //update on remove user from blog
        add_action( 'remove_user_from_blog', array( $this, 'on_removal_from_network' ), 10, 2 );
        
        //on account deletion
               
        add_action( 'deleted_user', array( $this, 'on_account_delete' ) );
        //on mark spam
        add_action( 'bp_make_spam_user', array( $this, 'on_account_delete' ) );

        //what about when a network is marked as spam?
        
        
        //on blog spam?
        //add_action('make_spam_blog','mnetwork_update_on spam_blog');//$blog_id


    }
    
    public static function get_instance() {
        if ( !isset( self::$instance ) )
            self::$instance = new self();

        return self::$instance;
    }
    //update table on user activation
    function on_user_activation( $user_id, $pass, $meta ) {
        

        $blog_id = get_current_blog_id();
        mnetwork_add_user( $user_id, $blog_id );
    }

    
 
    function on_add_to_network( $user_id, $role, $blog_id ) {
        
        mnetwork_add_user( $user_id, $blog_id );
      
    }
    
    function on_removal_from_network( $user_id, $blog_id ) {
            mnetwork_remove_user( $user_id, $blog_id );
            //should not the user data sould be removed for this network?
        }
     function on_account_delete( $user_id ) {

        mnetwork_remove_user( $user_id );//remove user from all the networks
        //how about deleteing user data from all the networks?
    }
    
    
} 


BPMultiNetworkActions::get_instance();
//when  user is deleted by any means, we should find all his network and delete all data, should we ? ok next version

