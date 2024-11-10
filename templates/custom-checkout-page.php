<?php
/**
 * Template for the custom checkout page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div class="custom-checkout-container">
    <h1><?php _e( 'Checkout', 'custom-checkout-plugin' ); ?></h1>

    <?php if ( wc_notice_count() > 0 ) : ?>
        <div class="woocommerce-notices-wrapper">
            <?php wc_print_notices(); ?>
        </div>
    <?php endif; ?>

    <div class="custom-checkout-columns">
        
        <div class="custom-checkout-left">
        <form method="post" class="custom-checkout-form">
    <?php wp_nonce_field( 'process_custom_checkout', 'ccp_nonce' ); ?>

    <p>
        <label for="ccp_name"><?php _e( 'Name', 'custom-checkout-plugin' ); ?> <span class="required">*</span></label>
        <input type="text" name="ccp_name" id="ccp_name" required>
    </p>

    <p>
        <label for="ccp_address"><?php _e( 'Address', 'custom-checkout-plugin' ); ?> <span class="required">*</span></label>
        <input type="text" name="ccp_address" id="ccp_address" required>
    </p>

    <p>
        <label for="ccp_email"><?php _e( 'Email', 'custom-checkout-plugin' ); ?> <span class="required">*</span></label>
        <input type="email" name="ccp_email" id="ccp_email" required>
    </p>

    <p>
        <label for="ccp_payment_method"><?php _e( 'Payment Method', 'custom-checkout-plugin' ); ?> <span class="required">*</span></label>
        <select name="ccp_payment_method" id="ccp_payment_method" required>
            <option value=""><?php _e( 'Select a payment method', 'custom-checkout-plugin' ); ?></option>
            <option value="credit_card"><?php _e( 'Credit Card', 'custom-checkout-plugin' ); ?></option>
            <option value="cash"><?php _e( 'Cash', 'custom-checkout-plugin' ); ?></option>
        </select>
    </p>

    <p>
        <label for="ccp_payment_info"><?php _e( 'Payment Information', 'custom-checkout-plugin' ); ?> <span class="required">*</span></label>
        <input type="text" name="ccp_payment_info" id="ccp_payment_info" required>
    </p>

    <p>
        <button type="submit" name="ccp_submit"><?php _e( 'Place Order', 'custom-checkout-plugin' ); ?></button>
    </p>
</form>

        </div>

        <div class="custom-checkout-right">
                <h2><?php _e( 'Your Order', 'custom-checkout-plugin' ); ?></h2>
                <?php
                if ( ! WC()->cart->is_empty() ) {
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = $cart_item['data'];
                        $quantity = $cart_item['quantity'];
                        ?>
                        <div class="cart-item">
                            <p class="product-name"><?php echo $_product->get_name(); ?> &times; <?php echo $quantity; ?></p>
                            <p class="product-total"><?php echo wc_price( $cart_item['line_total'] ); ?></p>
                        </div>
                        <?php
                    }
                    ?>
                    <p class="cart-total"><?php _e( 'Total:', 'custom-checkout-plugin' ); ?> <?php echo WC()->cart->get_cart_total(); ?></p>
                    <?php
                } else {
                    echo '<p>' . __( 'Your cart is empty.', 'custom-checkout-plugin' ) . '</p>';
                }
                ?>
             </div>
        </div>
    </div>

<?php get_footer(); ?>
