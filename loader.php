<?php
class BPMultiNetworkComponent extends BP_Component{
    private static $instance;

    public static function get_instance() {
        if ( !isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct() {

        parent::start(
                'mnetwork',//unique id
                __( 'Network', 'mnetwork' ),
                untrailingslashit( BP_MNETWORK_DIR )//base path
        );
    }
    function setup_globals( $globals = array() ) {
            
        global $bp;
       
        // Define a slug, if necessary
        if ( !defined( 'BP_MNETWORK_SLUG' ) )
            define( 'BP_MNETWORK_SLUG', $this->id );

            
        $global_tables = array(
			'table_network_users' => mnetwork_get_table_name(),//these tables can be accessed from $bp->mnetwork->table_name

		);

                
		//all other globals
                // Note that global_tables is included in this array.
		
        $globals = array(
			'slug'                  => 'network',
			'root_slug'             => isset( $bp->pages->mnetwork->slug ) ? $bp->pages->mnetwork->slug : BP_MNETWORK_SLUG,
			//'notification_callback' => 'mnetwork_format_notifications',
			'search_string'         => __( 'Search Networks...', 'mnetwork' ),
			'global_tables'         => $global_tables,
                        'has_directory'         => false
		);


        parent::setup_globals( $globals );//it will call do_action("bp_gallery_setup_global") after setting up the constants properly




        }//end of setup global

         /**
     * Include files
     */
    function includes( $includes = array() ) {

   	$includes = array();

   }

        //do we really need it ? No, if we don't want to list the networks on user profile
        function setup_nav( $main_nav = array(), $sub_nav = array() ) {
            global $bp;

            //sorry I am not putting it in the initial version, if the community suggests I will be happy to add a My Network Tab
            return false;

            // Add 'Networks' to the user's main navigation
            $main_nav = array(
			'name'                => sprintf( __( 'Networks <span>%d</span>', 'mnetwork' ), 2 ),// bp_get_total_networks_for_user()
			'slug'                => $this->slug,
			'position'            => 86,
			'screen_function'     => 'mnetwork_screen_my_networks',
			'default_subnav_slug' => 'my-galleries',
			'item_css_id'         => $this->id
		);

			$network_link = trailingslashit( $bp->loggedin_user->domain . $this->slug );//with a trailing slash

		// Add the My Groups nav item
            $sub_nav[] = array(
                    'name'            => __( 'My Networks', 'mnetwork' ),
                    'slug'            => 'my-networks',
                    'parent_url'      => $network_link,
                    'parent_slug'     => $this->slug,
                    'screen_function' => 'mnetwork_screen_my_networks',
                    'position'        => 10,
                    'item_css_id'     => 'mnetwork-my-networks'
            );



            //if this is single gallery, add edit gallery link too!
            //

	 parent::setup_nav( $main_nav, $sub_nav );


         do_action( 'mnetwork_setup_nav');
	}
}
