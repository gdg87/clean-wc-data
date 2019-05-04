<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Clean_WC_Data
 * @subpackage Clean_WC_Data/admin/partials
 */

$active_tab = 'orders';
if( isset( $_GET[ 'tab' ] ) ) {
    $active_tab = $_GET[ 'tab' ];
} // end if

?>
<div class="wrap">
  <h2><?php echo __("Clean Woocomerce Data", 'clean_wc_data')?></h2>
    <?php if ((int)$_GET['m'] === 1): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Done!', 'clean_wc_data' ); ?></p>
        </div>
    <?php endif ?>
    <div class="nav-tab-wrapper wp-clearfix">
        <a href="?page=clean-wc-data&tab=orders" class="nav-tab <?php echo $active_tab == 'orders' ? 'nav-tab-active' : ''; ?>"><?php echo __('Orders', 'clean_wc_data') ?></a>
        <a href="?page=clean-wc-data&tab=customers" class="nav-tab <?php echo $active_tab == 'customers' ? 'nav-tab-active' : ''; ?>"><?php echo __('Customers', 'clean_wc_data') ?></a>
        <a href="?page=clean-wc-data&tab=products" class="nav-tab <?php echo $active_tab == 'products' ? 'nav-tab-active' : ''; ?>"><?php echo __('Products', 'clean_wc_data') ?></a>
    </div>
    <div class="clean-wc-data-tab-container">
        <form method="post" action="admin-post.php">
            <input type="hidden" name="action" value="clean_wc_data_products" />
            <?php wp_nonce_field( 'clean_wc_data' ); ?>
            
            <?php
                switch($active_tab) {
                    case 'orders':
            ?>
                        <p><?php echo __("By clicking the button below you will remove all the orders and related data from database", 'clean_wc_data' )?></p>
            <?php
                        break;
                    case 'customers':
            ?>
                        <p><?php echo __("Specify ID or email of users to keep into woocommerce separated by a comma", 'clean_wc_data' )?> (,).</p>
                        <input type="text" name="keep" size="100"/>
            <?php
                        break;
                case 'products':
            ?>
                        <input type="checkbox" name="chckbx_cats" id="chckbx_cats" value="chckbx_cats">Also remove related categories, tags, and taxonomies</p>
            <?php
                        break;
                }
            ?>
            <br />
            <p><strong><?php echo __("Caution", 'clean_wc_data'); ?></strong>:&nbsp;<?php echo __("Execute a database backup before run the operation", 'clean_wc_data' )?></p>
            <br />
            <input type="submit" value="<?php echo __("Clean", 'clean_wc_data') ?>" class="button-primary"/>
        </form>
    </div>
</div>