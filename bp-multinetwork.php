<?php
/**
 * Plugin Name: BP Multi Network
 * Plugin URI: http://buddydev.com/plugins/buddypress-multi-network/
 * Version:1.0.1
 * Author:Brajesh Singh
 * Author URI: http://buddydev.com
 * Description: Helps you to Build multiple BuddyPress network on a WordPress Multisite/BuddyPress Install
 * License: GPL
 */

define( 'BP_MNETWORK_DIR',  plugin_dir_path( __FILE__ ) );

class BPMultiNetworkHelper{
    
    private static $instance;
    
    public static function get_instance() {
        if( !isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {
        //if MULTIBLOG is not enabled, dont do anything fancy
       
        add_action( 'bp_loaded', array( $this, 'network_init' ), 1 );
        include_once( BP_MNETWORK_DIR . 'hooks.php' );
        include_once( BP_MNETWORK_DIR . 'users.php' );
        
      //to install and create required tables
       if( is_network_admin() )
            include_once BP_MNETWORK_DIR . 'install.php';
    }
    
    public function network_init(){
        $bp = buddypress();
        include_once BP_MNETWORK_DIR . 'loader.php';
        
        $bp->mnetwork = BPMultiNetworkComponent::get_instance();
        
    }
    
    public function get_table_name(){
         global $wpdb;
         return  $wpdb->base_prefix . 'bp_mnetwork_users';
    
    }
 }
 
BPMultiNetworkHelper::get_instance();

/**
 * Get the multinetwork plugin table name where we associate user to network
 * @return type
 */
function mnetwork_get_table_name() {
    
    $helper = BPMultiNetworkHelper::get_instance();
    return $helper->get_table_name();
}
