<?php
/*
Plugin Name: Custom Checkout Plugin
Plugin URI: https://yourwebsite.com/
Description: Replaces the standard WooCommerce checkout page with a custom version.
Version: 1.0
Author: MideD
Author URI: https://yourwebsite.com/
License: GPL2
Text Domain: custom-checkout-plugin
Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Require the necessary classes
require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/template-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/checkout-handler.php';

// Activation and deactivation hooks
register_activation_hook( __FILE__, array( 'CCP_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'CCP_Deactivator', 'deactivate' ) );

// Initialize the plugin
function run_custom_checkout_plugin() {
    $template_loader = new CCP_Template_Loader();
    $checkout_handler = new CCP_Checkout_Handler();
}
run_custom_checkout_plugin();
