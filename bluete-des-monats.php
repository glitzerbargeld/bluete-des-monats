<?php
/*
Plugin Name: Bluete des Monats
Plugin URI: 
Description: 
Version: 1.0
Author: Torben Jäckel
Author URI: 
*/

// Enqueue Scripts
function my_plugin_enqueue_scripts()
{
    wp_enqueue_script('my-script', plugin_dir_url(__FILE__) . 'js/my-script.js');
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_scripts');

// Enqueue Styles
function my_plugin_enqueue_styles()
{
    wp_enqueue_style('my-styles', plugin_dir_url(__FILE__) . 'css/my-styles.css');
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_styles');


add_action('admin_menu', 'add_product_actions_menu');

function add_product_actions_menu()
{
    add_submenu_page(
        'edit.php?post_type=product',
        __('Aktionen', 'woocommerce'),
        __('Aktionen', 'woocommerce'),
        'manage_options',
        'product_actions',
        'product_actions_page'
    );
}

function product_actions_page()
{
    settings_errors();

    ?>
        <div class="wrap">
            <h1><?php _e('Rabattaktionen', 'woocommerce'); ?></h1>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php
                settings_fields('product_actions_settings');
                do_settings_sections('product_actions_settings');
                ?>
                <input type="hidden" name="action" value="apply_discount">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e('Blüte des Monats', 'woocommerce'); ?></th>
                        <td style="display: flex; flex-wrap: wrap; gap: 30px; margin-top:20px;">
                            <?php
                            $product_of_the_month = get_option('product_of_the_month');
                            $products = wc_get_products(array(
                                'status' => 'publish',
                                'limit' => -1,
                                'category' => 'CBD-Blüten'
                            ));
                            ?>
                            <div>
                                <input type="radio" name="product_of_the_month" value="" <?php checked($product_of_the_month, ''); ?>>
                                <label><?php _e('None', 'woocommerce'); ?></label>
                            </div>
                            <?php foreach ($products as $product) : ?>
                                <div>
                                    <input type="radio" name="product_of_the_month" value="<?php echo $product->get_id(); ?>" <?php checked($product_of_the_month, $product->get_id()); ?>>
                                    <label><?php echo $product->get_name(); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Speichern', 'woocommerce'), 'primary', 'apply_discount', true); ?>
            </form>
        </div>

    <?php

}



add_action('admin_init', 'register_product_actions_settings');

function register_product_actions_settings()
{
    register_setting(
        'product_actions_settings',
        'product_of_the_month',
        array(
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0
        )
    );
}

function add_discount_to_variants($old_product_id, $new_product_id) {
    if ($old_product_id) {
        $old_product = wc_get_product($old_product_id);
        delete_post_meta($old_product_id, 'bluete_des_monats');
        $old_variations = $old_product->get_children();
        foreach ($old_variations as $old_variation_id) {
            $old_variation = wc_get_product($old_variation_id);
            $old_variation->set_sale_price('');
            $old_variation->save();
        }
    }

    if ($new_product_id) {
        $new_product = wc_get_product($new_product_id);
        update_post_meta($new_product_id, 'bluete_des_monats', 'yes');
        $new_variations = $new_product->get_children();
        foreach ($new_variations as $new_variation_id) {
            $new_variation = wc_get_product($new_variation_id);
            $regular_price = $new_variation->get_regular_price();
            $sale_price = $regular_price * 0.8;
            $new_variation->set_sale_price($sale_price);
            $new_variation->save();
        }
    }
}

function apply_discount_to_product_of_the_month() {
    error_log('apply_discount_to_product_of_the_month function called');
    $old_product_id = get_option('product_of_the_month');
    $new_product_id = $_POST['product_of_the_month'];
    error_log('new_product_id: ' . $new_product_id);
    if (!empty($new_product_id)) {
        add_discount_to_variants($old_product_id, $new_product_id);
        update_option('product_of_the_month', $new_product_id);
        error_log('Discount applied.');
    } else {
        add_discount_to_variants($old_product_id, 0);
        delete_option('product_of_the_month');
        error_log('No product selected.');
    }
    error_log('Redirecting...');
    wp_redirect(admin_url('edit.php?post_type=product&page=product_actions&discount_applied=1'));
    exit();
}

add_action('admin_post_apply_discount', 'apply_discount_to_product_of_the_month');

function show_discount_applied_notice() {
    if (isset($_GET['discount_applied']) && $_GET['discount_applied'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Blüte des Monats wurde gesetzt.', 'woocommerce') . '</p></div>';
    }
}
add_action('admin_notices', 'show_discount_applied_notice');

function your_plugin_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'your-plugin-blocks-editor',
        plugin_dir_url(__FILE__) . 'build/block.js',
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'build/block.js')
    );
}

add_action('enqueue_block_editor_assets', 'your_plugin_enqueue_block_editor_assets');

function your_plugin_enqueue_block_assets()
{
    wp_enqueue_style(
        'your-plugin-blocks',
        plugin_dir_url(__FILE__) . 'blocks/style.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/product-of-the-month/style.css')
    );
}
add_action('enqueue_block_assets', 'your_plugin_enqueue_block_assets');


function render_product_of_the_month_block( $attributes ) {
    $product_id = get_option('product_of_the_month');
    if ( !$product_id ) {
        return ''; // No product of the month is set, so return an empty string.
    }

    $product = wc_get_product( $product_id );
    if ( !$product ) {
        return ''; // The product doesn't exist, so return an empty string.
    }

    $product_title = $product->get_name();
    $product_image_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
    $product_link = get_permalink( $product_id );

    ob_start();
    ?>
    <div id="bluete-des-monats">
        <img src="<?php echo esc_url( $product_image_url ); ?>" alt="<?php echo esc_attr( $product_title ); ?>" />
        <div class="text-wrapper">
        <h2>Spare 20% mit unserer Blüte des Monats!</h2>
        <a href="<?php echo esc_url( $product_link ); ?>" class="button">Jetzt Shoppen</a>
        </div>
       
        
    </div>
    <?php
    return ob_get_clean();
}


register_block_type( 'bluete-des-monats/product-of-the-month', array(
    'render_callback' => 'render_product_of_the_month_block',
) );
