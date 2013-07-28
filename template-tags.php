<?php
/**
 * Template Utility Constants and Functions
 */

// ============================================
// = Location of the Setting Fields Directory =
// ============================================
define( "NU_SETTING_FIELDS_DIR", dirname( __FILE__ ) . '/views/setting-fields/' );


function nu_is_login( $wplogin=false )
{
	global $pagenow;

	$is_login = in_array( $pagenow, array( 'wp-login.php' ) );

	if( $wplogin )
		return $is_login;

	$is_login = apply_filters( 'nu_is_login', $is_login );
	
	return $is_login;
}


function nu_is_registration()
{
	global $pagenow;

	$is_reg = in_array( $pagenow, array( 'wp-register.php' ) );

	$is_reg = apply_filters( 'nu_is_registration', $is_reg );
	
	return $is_reg;
}


/**
 * @uses nu_is_login
 */
function nu_is_profile()
{
	$is_profile = false;

	if( nu_is_login() 
		&& isset( $_GET['action'] ) && $_GET['action'] == 'profile' ) {

		$is_profile = true;
	}

	$is_profile = apply_filters( 'nu_is_profile', $is_profile );

	return $is_profile;
}


/**
 * Based on the wordpress get_template_part function
 *
 * @uses locate_template()
 * @uses do_action() Calls 'nu_get_template_part' action.
 *
 * @param $slug (str) The slug name for the generic template.
 * @param $name (str) The name of the specialised template.
 * @param $dir (str) The directory within the current theme folder.
 *
 * @return (str) The template filename if one is located, an empty string if not.
 */
function nu_get_template_part( $slug, $name = null, $dir = '' )
{
	nu_debug( 'NU Get Template Part', "{$dir}/{$slug}" );

	do_action( 'nu_get_template_part', $slug, $name, $dir );
	do_action( "nu_get_template_part_{$slug}", $slug, $name, $dir );

	$slug = trailingslashit( $dir ) . $slug;

	$templates = array();
	if( isset( $name ) )
		$templates[] = "{$slug}-{$name}.php";

	$templates[] = "{$slug}.php";

	return locate_template( $templates, true, false );
}