<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CCP_Template_Loader {
    public function __construct() {
        add_filter( 'template_include', array( $this, 'load_custom_checkout_template' ), 99 );
        
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    public function load_custom_checkout_template( $template ) {
        if ( is_checkout() && ! is_order_received_page() ) {
            $plugin_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/custom-checkout-page.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        return $template;
    }

    public function enqueue_styles() {
        if ( is_checkout() && ! is_order_received_page() ) {
            wp_enqueue_style( 'custom-checkout-style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/custom-checkout-style.css', array(), '1.0' );
        }
    }
}
