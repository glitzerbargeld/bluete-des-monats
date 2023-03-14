<?php
/*
Plugin Name: Bluete des Monats
Plugin URI: 
Description: 
Version: 1.0
Author: Your Name
Author URI: 
*/

// Enqueue Scripts
function my_plugin_enqueue_scripts() {
    wp_enqueue_script( 'my-script', plugin_dir_url( __FILE__ ) . 'js/my-script.js' );
}
add_action( 'wp_enqueue_scripts', 'my_plugin_enqueue_scripts' );

// Enqueue Styles
function my_plugin_enqueue_styles() {
    wp_enqueue_style( 'my-styles', plugin_dir_url( __FILE__ ) . 'css/my-styles.css' );
}
add_action( 'wp_enqueue_scripts', 'my_plugin_enqueue_styles' );
