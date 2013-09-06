<?php

add_action( 'debug_bar_enqueue_scripts', 'init_nu_debug' );

function init_nu_debug()
{
	$dir = dirname( __FILE__ );
	
	// check to see if the debug_bar plugin is installed and active
	if( $GLOBALS['debug_bar'] ) {
		require_once( 'debug-bar/debug-hook-panel.class.php' );
		require_once( 'debug-bar/debug-styles-panel.class.php' );
		require_once( 'debug-bar/debug-panel.class.php' );
		
		// instantiate
		new nu_debug_styles_panel();
		new nu_debug_hook_panel();
		new nu_debug_panel();
	}
}


class nu_debug extends nu_singleton
{

	/**
	 * Deprecated. Use nu_debug() instead
	 */
	public static function var_dump( $var, $depth=1 )
	{
		$trace = debug_backtrace();

		$file = $trace[0]['file'];

		nu_debug( 'Deprecated: Use nu_debug() || ' . $file, $var );

		// echo '<pre>';

		// var_dump( $var );

		// Trace where the call came from. The output is filtered because
		// we usually don't need all the information debug_backtrace gives us.
		// $trace = debug_backtrace();
		// foreach ( $trace as $i=>$t ){
		// 	if ( $i == $depth ) break;

		// 	$file = $t['file'];
		// 	$line = $t['line'];
		// 	echo "<p class='trace'>debug called by: file: $file | line: $line</p>";
		// }

		// echo '=====================================================<br/><br/>';
		// echo '</pre>';
	}
}


/**
 Template Tag
 */
function nu_debug( $title, $var, $depth=1 ) 
{
	global $nu_debug;

	$trace = debug_backtrace();

	$mt = microtime();

	for( $i=0; $i<$depth; $i++ ) {
		$nu_debug[$title][$mt]['trace'][$i] = $trace[$i];
	}

	$nu_debug[$title][$mt]['var'] = $var;

	return;
}