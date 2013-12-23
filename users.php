<?php
/**
 * Manage Users for a network
 */
class BPNetworkUsers{
  /**
   * var $user_id;
   * var $network_id;
   * var $is_active;
   * //sorry I am not going to add a constructor
   */
        
    /**
     * Get the list of users member of this network
     * @param type $network_id
     * @return type 
     */
  public static function get_users( $network_id ) {
        global $wpdb;
        $table_users = mnetwork_get_table_name();
        $query = "SELECT user_id FROM {$table_users} WHERE network_id = %d";
        $users = $wpdb->get_col( $wpdb->prepare( $query, $network_id ) );
        return $users;
        
    }
  public static function get_networks( $user_id ) {
        global $wpdb;
         
        $table_users = mnetwork_get_table_name();
       
        $query = "SELECT DISTINCT(network_id) FROM {$table_users} WHERE user_id = %d";
        $networks = $wpdb->get_col( $wpdb->prepare( $query, $user_id ) );
        return $networks;
    }
    
  public static function get_network_users_count( $network_id ) {
        global $wpdb;
        $table_users = mnetwork_get_table_name();
       
        $query = "SELECT COUNT(DISTINCT(user_id)) FROM {$table_users} WHERE network_id = %d";
        
        $networks = $wpdb->get_var( $wpdb->prepare( $query, $network_id ) );
        
        return $networks;   
  }  
    /**
     * Add user to a network
     * @param type $user_id
     * @param type $network_id 
     */
  public static function add_user( $user_id, $network_id ) {
        if( empty( $user_id ) || empty( $network_id ) )
            return false;
        
        global $wpdb;
       
        $table_users = mnetwork_get_table_name();
       
        $query = "INSERT INTO {$table_users} SET user_id=%d, network_id = %d";
        
        $wpdb->query( $wpdb->prepare( $query, $user_id, $network_id ) );
        return true;
    }
    
  public static function remove_user( $user_id, $network_id = false ) {
        if( empty( $user_id ) && empty( $network_id ) )
            return false;
        global $wpdb;
       
        $table_users = mnetwork_get_table_name();
       
       $where_conditions = array();
       $where_sql = '';
        if( !empty( $user_id ) )
            $where_conditions[] = $wpdb->prepare( "user_id = %d", $user_id );
        
        if( !empty( $network_id ) )
            $where_conditions[] = $wpdb->prepare( "network_id = %d", $network_id );
        
        $where_sql = join(' AND ', $where_conditions );
        
        $query = "DELETE FROM {$table_users} WHERE {$where_sql}";
        
        $wpdb->query( $wpdb->prepare( $query, $user_id, $network_id ) );
        
        return true;
    }  
    
     public static function remove_network( $network_id ) {
        
        if( empty( $network_id ) )
                return false;
        
        global $wpdb;
       
        $table_users = mnetwork_get_table_name();
       
        $query = "DELETE FROM {$table_users} WHERE network_id = %d";
        
        $wpdb->query( $wpdb->prepare( $query, $network_id ) );
        
        return true;
    }
}

function mnetwork_add_user( $user_id, $network_id ) {
   return  BPNetworkUsers::add_user( $user_id, $network_id );
}
function mnetwork_remove_user( $user_id, $network_id = false ) {
   return  BPNetworkUsers::remove_user( $user_id, $network_id );
}
//use it on blog delete/spam/archive? should we ?
function mnetwork_remove_network( $network_id ) {
   return  BPNetworkUsers::remove_network( $network_id );
}
function mnetwork_get_users( $network_id ) {
   return  BPNetworkUsers::get_users( $network_id );
}


function mnetwork_get_networks( $user_id ) {
   return  BPNetworkUsers::get_networks( $user_id );
}

function mnetwork_get_total_users( $network_id ) {
    return  BPNetworkUsers::get_network_users_count( $network_id );
}
