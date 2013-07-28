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
require_once( NU_PATH . 'vendor/closure/php-closure.php' );
require_once( NU_PATH . 'vendor/klogger/klogger.php' );
require_once( NU_PATH . 'vendor/wpalchemy/MetaBox.php');

// include classes
require_once( NU_PATH . 'classes/singleton.class.php' );
require_once( NU_PATH . 'classes/settings.class.php' );
require_once( NU_PATH . 'classes/debug.class.php' );
require_once( NU_PATH . 'classes/utils.class.php' );
require_once( NU_PATH . 'classes/media-utils.class.php' );
require_once( NU_PATH . 'classes/lazy-load-js.class.php' );
require_once( NU_PATH . 'classes/lazy-load-css.class.php' );
require_once( NU_PATH . 'classes/rewrite-rules.class.php' );

// include template tags
require_once( NU_PATH . 'template-tags.php' );

// include options
require_once( NU_PATH . 'settings.php' );

add_action( 'init', 'nu_setup' );
add_action( 'init', 'nu_init_js' );
add_filter( 'nu_load_js', 'nu_load_js' );

function nu_setup()
{
	nu_lazy_load_css::get_instance();
	nu_lazy_load_js::get_instance();
	nu_settings_controller::get_instance();
}

/** 
 * Register javascript.
 *
 * scripts registered here should be utility plugins and scripts meaning that 
 * they can be useful no matter which plugin(s) or theme(s) are being used
 */
function nu_init_js()
{
	$base_dir = NU_URL . 'js/';

	// generic scripts
	nu_lazy_load_js::reg_js( $base_dir . 'log.js', null, NU_PLUGIN_VERSION, false );
	nu_lazy_load_js::reg_js( $base_dir . 'jquery.smartresize.js', array('jquery'), NU_PLUGIN_VERSION, true );


	// register naked utils specific scripts 
	// -------------------------------------------------------------

	// basename for registration.
	$basename = 'nu';

	// registration name will be nu-admin-jquery_naked_form_handler
	nu_lazy_load_js::reg_js( $base_dir . 'admin/jquery.naked_form_handler.js', array('jquery'), NU_PLUGIN_VERSION, false, $basename );

	// registration name will be nu-admin-jquery_naked_autosuggest_handler
	nu_lazy_load_js::reg_js( $base_dir . 'admin/jquery.naked_autosuggest_handler.js',  array('jquery', 'suggest'), NU_PLUGIN_VERSION, false, $basename );
}


/**
 * Load/Enqueue javascript
 *
 * Use this to load true utility scripts only (i.e. scripts that will be useful 
 * everywhere). Everything else should be done through the plugins or themes that 
 * actually need to call the javascript registered here.
 */
function nu_load_js( $scripts )
{
	$scripts['sitewide'][] = 'log';

	return $scripts;
}