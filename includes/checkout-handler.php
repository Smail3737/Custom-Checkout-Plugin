<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CCP_Checkout_Handler {
    public function __construct() {
        // Process the custom checkout form
        add_action( 'wp', array( $this, 'process_custom_checkout' ) );

        // Register display action for order details
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_custom_payment_information' ) );

        // Filter payment gateways based on custom logic
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'filter_payment_gateways' ) );

        // Display selected payment method in admin
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_selected_payment_method_in_admin' ), 10, 1 );
    }

    public function filter_payment_gateways( $available_gateways ) {
        // Set the available payment methods
        $allowed_gateways = array( 'bacs', 'cheque', 'cod' );
    
        foreach ( $available_gateways as $gateway_id => $gateway ) {
            if ( ! in_array( $gateway_id, $allowed_gateways ) ) {
                unset( $available_gateways[ $gateway_id ] );
            }
        }
        return $available_gateways;
    }

    public function display_selected_payment_method_in_admin( $order ) {
        // Get the header of the selected payment method
        $payment_method_title = $order->get_payment_method_title();
        
        if ( $payment_method_title ) {
            echo '<p><strong>' . __( 'Selected Payment Method:', 'custom-checkout-plugin' ) . '</strong> ' . esc_html( $payment_method_title ) . '</p>';
        }
    }

    public function process_custom_checkout() {
        if ( isset( $_POST['ccp_submit'] ) ) {

            // Security check
            if ( ! isset( $_POST['ccp_nonce'] ) || ! wp_verify_nonce( $_POST['ccp_nonce'], 'process_custom_checkout' ) ) {
                wc_add_notice( 'Security error. Please try again.', 'error' );
                return;
            }

            $name = isset( $_POST['ccp_name'] ) ? sanitize_text_field( $_POST['ccp_name'] ) : '';
            $address = isset( $_POST['ccp_address'] ) ? sanitize_text_field( $_POST['ccp_address'] ) : '';
            $email = isset( $_POST['ccp_email'] ) ? sanitize_email( $_POST['ccp_email'] ) : '';
            $payment_method = isset( $_POST['ccp_payment_method'] ) ? sanitize_text_field( $_POST['ccp_payment_method'] ) : '';
            $payment_info = isset( $_POST['ccp_payment_info'] ) ? sanitize_text_field( $_POST['ccp_payment_info'] ) : '';

            // Validate fields
            $errors = false;
            if ( empty( $name ) ) {
                wc_add_notice( 'Please enter your name.', 'error' );
                $errors = true;
            }
            if ( empty( $address ) ) {
                wc_add_notice( 'Please enter your address.', 'error' );
                $errors = true;
            }
            if ( empty( $email ) || ! is_email( $email ) ) {
                wc_add_notice( 'Please enter a valid email address.', 'error' );
                $errors = true;
            }
            if ( empty( $payment_method ) ) {
                wc_add_notice( 'Please select a payment method.', 'error' );
                $errors = true;
            }
            if ( empty( $payment_info ) ) {
                wc_add_notice( 'Please enter your payment information.', 'error' );
                $errors = true;
            }

            if ( $errors ) {
                return;
            }

            // Create order
            $order = wc_create_order();

            // Add products
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $order->add_product( $product, $quantity );
            }

             // Set Fields in Woo admin panel
            $order->set_address( array(
                'first_name' => 'Name: ' . $name,
                'email'      => $email,
            ), 'billing' );

            $order->set_address( array(
                'address_1' => 'Address: ' . $address,
            ), 'shipping' );

            // Save payment information as order meta in billing
            if ( ! empty( $payment_info ) ) {
                $order->update_meta_data( '_payment_information', $payment_info );
            }

            // Set the payment method in the order
            $order->set_payment_method( $payment_method );

            // Calculate order totals
            $order->calculate_totals();

            // Update order status
            $order->update_status( 'processing', 'Order created through custom checkout page.' );

            // Clear the cart
            WC()->cart->empty_cart();

            // Redirect to the order received page
            wp_redirect( $order->get_checkout_order_received_url() );
            exit;
        }
    }

    // Display the payment information in the billing section of the admin order details
    public function display_custom_payment_information( $order ) {
        $payment_info = $order->get_meta( '_payment_information' );
        if ( ! empty( $payment_info ) ) {
            echo '<p><strong>' . __( 'Payment Information:', 'custom-checkout-plugin' ) . '</strong> ' . esc_html( $payment_info ) . '</p>';
        }
    }
}
