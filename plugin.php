<?php
/*
Plugin Name: Naked Utils
Description: Provides a collection of utility classes/functions. No theme/plugin dependent classes/functions belong here.
Author: Andre Deutmeyer
Version: 0.2
*/


/*** Constant that can be checked by themes and plugins ***/
define( "NAKED_UTILS", 1 );


/*** Plugin Name ***/
define( 'NU_PLUGIN_NAME', 'naked-utils' );


/*** Plugin Version ***/
define( 'NU_PLUGIN_VERSION', '0.1' );


/*** Plugin Path and URL ***/
define( 'NU_URL', plugin_dir_url( __FILE__ ) );
define( 'NU_PATH', trailingslashit( dirname( __FILE__ ) ) );


/*** Plugin Subdirectories ***/
define( 'NU_VIEWS_DIR', NU_PATH . 'views/' );
define( 'NU_CLASSES_DIR', NU_PATH . 'classes/' );


/*** Plugin Javascript Directory and URL ***/
define( 'NU_JS_DIR', NU_PATH . 'js/' );
define( 'NU_JS_URL', NU_URL . 'js/' );

// include vendor files
require_once( NU_PATH . 'vendor/klogger/klogger.php' );
require_once( NU_PATH . 'vendor/wpalchemy/MetaBox.php');

// include classes
require_once( NU_PATH . 'classes/singleton.class.php' );
require_once( NU_PATH . 'classes/settings.class.php' );
require_once( NU_PATH . 'classes/debug.class.php' );
require_once( NU_PATH . 'classes/utils.class.php' );
require_once( NU_PATH . 'classes/media-utils.class.php' );
require_once( NU_PATH . 'classes/rewrite-rules.class.php' );

// include template tags
require_once( NU_PATH . 'template-tags.php' );

add_action( 'init', 'nu_reg_js' );

/** 
 * Register javascript.
 *
 * scripts registered here should be utility plugins and scripts meaning that 
 * they can be useful no matter which plugin(s) or theme(s) are being used
 */
function nu_reg_js()
{
	$bn = 'nu-'; // basename for registration.
	$base_dir = NU_URL . 'js/';

	// generic scripts
	wp_register_script($bn.'log', $base_dir.'log.js', null, 0.1, false );
	wp_register_script($bn.'smartresize', $base_dir.'jquery.smartresize.js', array('jquery'), 0.1, true );

	// admin scripts
	wp_register_script($bn.'admin-form_handler', $base_dir.'admin/jquery.naked_form_handler.js', array('jquery'), 0.1, false );
	wp_register_script($bn.'admin-autosuggest_handler', $base_dir.'admin/jquery.naked_autosuggest_handler.js', array('jquery', 'suggest'), 0.1, false );
}