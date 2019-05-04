<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Clean_WC_Data
 * @subpackage Clean_WC_Data/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Clean_WC_Data
 * @subpackage Clean_WC_Data/admin
 * @author     Your Name <email@example.com>
 */
class Clean_WC_Data_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/clean-wc-data-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/clean-wc-data-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	
	public function add_menu() {
	    add_submenu_page(
            'woocommerce',
            __('Clean woocommerce data', 'clean_wc_data'),
            __('Clean data', 'clean_wc_data'),
            'manage_woocommerce',
            'clean-wc-data',
           array($this, 'my_custom_submenu_page_callback')
         );
	}
	
	public function my_custom_submenu_page_callback() {
	    require( dirname(__FILE__) . "/partials/clean-wc-data-admin-display.php");
    }
    
    public function process_clean_wc_data_orders() {
        if ( !current_user_can( 'manage_woocommerce' ) )
        {
            wp_die( 'You are not allowed to be on this page.' );
        }
        // Check that nonce field
        check_admin_referer( 'clean_wc_data' );

 
        $from = isset( $_POST['from'] ) ? $_POST['from'] : null;
        $to = isset( $_POST['to'] ) ? $_POST['to'] : null;
        
        
        global $wpdb;
        $wpdb->query( 
    	    $wpdb->prepare( 
    		    "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta"
    	   )
        );
        $wpdb->query( 
    	    $wpdb->prepare( 
    		    "DELETE FROM {$wpdb->prefix}woocommerce_order_items"
    	   )
        );
        $wpdb->query( 
    	    $wpdb->prepare( 
    		    "DELETE FROM {$wpdb->prefix}comments WHERE comment_type = 'order_note'"
    	   )
        );
        $wpdb->query( 
    	    $wpdb->prepare( 
    		    "DELETE FROM {$wpdb->prefix}postmeta WHERE post_id IN ( SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order' )"
    	   )
        );
        $wpdb->query( 
    	    $wpdb->prepare( 
    		    "DELETE FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order'"
    	   )
        );
        
        wp_redirect(  admin_url( 'admin.php?page=clean-wc-data&m=1' ) );
        exit;
    }
    
    public function process_clean_wc_data_customers() {
        if ( !current_user_can( 'manage_woocommerce' ) )
        {
            wp_die( 'You are not allowed to be on this page.' );
        }
        // Check that nonce field
        check_admin_referer( 'clean_wc_data' );

 
        $toKeep = isset( $_POST['keep'] ) ? $_POST['kepp'] : '';
        $keepArray = explode(",", $_POST['keep']);
        
        $users = get_users('role=customer');
        
        foreach ($users as $user) {
            if (in_array($user->ID, $keepArray) || in_array($user->user_email, $keepArray)) {
                continue;
            }
            wp_delete_user($user->ID);
        }
        
        wp_redirect(  admin_url( 'admin.php?page=clean-wc-data&m=1' ) );
        exit;
    }
    
    public function process_clean_wc_data_products() {
        if ( !current_user_can( 'manage_woocommerce' ) )
        {
            wp_die( 'You are not allowed to be on this page.' );
        }
        // Check that nonce field
        check_admin_referer( 'clean_wc_data' );

 
        $from = isset( $_POST['from'] ) ? $_POST['from'] : null;
        $to = isset( $_POST['to'] ) ? $_POST['to'] : null;
        $remove_cats = !empty($_POST['chckbx_cats']) ? true : false;
        
        global $wpdb;
        // Current site db prefix
        $pref = $wpdb->prefix;
        //delete all product relationships first
        $wpdb->query( 
	                "DELETE relations.*".( $remove_cats ? ", taxes.* , terms.* " : "" ).
	                "FROM ".$pref."term_relationships AS relations
	                INNER JOIN ".$pref."term_taxonomy AS taxes
	                ON relations.term_taxonomy_id=taxes.term_taxonomy_id
	                INNER JOIN ".$pref."terms AS terms
	                ON taxes.term_id=terms.term_id
	                WHERE object_id IN (SELECT ID FROM ".$pref."posts WHERE post_type='product');"
        );
	       
        //keep taxonomies just fix the link count to be zero
        //taxonomies that need updating include product_tag, product_type and product_cat
        if (!$remove_cats){
            $wpdb->query( 
                "UPDATE ".$pref."term_taxonomy SET count = 0 WHERE taxonomy LIKE 'product%';"
            );
        }
	
        //delete product meta data
        $wpdb->query( 
                "DELETE FROM ".$pref."postmeta WHERE post_id IN (SELECT ID FROM ".$pref."posts WHERE post_type = 'product');"
        );
	       
        //delete variation meta data
        $wpdb->query( 
                "DELETE FROM ".$pref."postmeta WHERE post_id IN (SELECT ID FROM ".$pref."posts WHERE post_type = 'product_variation');"
        );
	       
	        //delete actual products
        $prods_count = $wpdb->query(
                "DELETE FROM ".$pref."posts WHERE post_type = 'product';"
        );
       
        //delete product variations
        $vars_count = $wpdb->query(
                "DELETE FROM ".$pref."posts WHERE post_type = 'product_variation';"
        );
	       
        if (is_numeric($prods_count)){
            if ($prods_count == 0){
                    echo "No products found.";
            }else{
                    echo $prods_count." product(s) successfully removed!";
            }
        }else{
            echo 'Error removing products';
        }
        echo '<br />';
        if (is_numeric($vars_count)){
            if ($vars_count > 0){
                    echo $vars_count." product variation(s) successfully removed!";
                    echo '<br />';
            }
        }
    
        wp_redirect(  admin_url( 'admin.php?page=clean-wc-data&m=1' ) );
        exit;
    }

}