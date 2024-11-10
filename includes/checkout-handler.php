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

            // Set billing address
            $order->set_address( array(
                'first_name' => $name,
                'email'      => $email,
            ), 'billing' );

            // Set shipping address
            $order->set_address( array(
                'address_1' => $address,
            ), 'shipping' );

            // Save payment information as order meta in billing
            if ( ! empty( $payment_info ) ) {
                $order->update_meta_data( '_payment_information', $payment_info );
            }

            // Set the payment method
            if ( $payment_method === 'credit_card' ) {
                $order->set_payment_method_title( 'Credit Card' );
                $order->set_payment_method( 'custom_credit_card' );
            } elseif ( $payment_method === 'cash' ) {
                $order->set_payment_method_title( 'Cash' );
                $order->set_payment_method( 'custom_cash' );
            } else {
                $order->set_payment_method_title( 'Unknown' );
                $order->set_payment_method( 'unknown' );
            }

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
