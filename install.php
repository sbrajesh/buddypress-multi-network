<?php
function mnetwork_install() {
    $sql = array();
    global $bp;
    global $wpdb;
    $charset_collate = '';
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    if ( !empty( $wpdb->charset ) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	
        
    $sql[] = "CREATE TABLE IF NOT EXISTS {$wpdb->base_prefix}bp_mnetwork_users (
                                          user_id bigint(20) NOT NULL,
                                          network_id bigint(20) NOT NULL,
                                          is_active tinyint(2) NOT NULL,
                                          UNIQUE KEY user_id (user_id,network_id)
                ) {$charset_collate};";
      
    dbDelta( $sql );
    update_site_option( 'bpmnetwork_db_version', 22 );
}

function mnetwork_check_installed(){
    if( is_network_admin() && is_super_admin() ) {
        //if a super admin is logged in the network admin, let us check for the installation
        $version = get_site_option( 'bpmnetwork_db_version' );
        //if not installed, let us do it now
      if( $version < 22 )
            mnetwork_install();
    }
        
}
add_action( 'admin_init', 'mnetwork_check_installed' );

