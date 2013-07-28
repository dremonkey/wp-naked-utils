<?php

/**
 * Used to improve Wordpress frontend JS handling so that scripts can be added 
 * lazily. Also concatenates and minifies those files using Google Closure
 */

class nu_lazy_load_js extends nu_singleton
{
	private static $debug = false;

	public $scripts = array();

	/**
	 * __construct()
	 */
	protected function __construct()
	{
		add_action( 'admin_init', array( &$this, 'setup') );

		// Setup lazy load stuff - needs to fire after we know what page is going to be loaded
		// add_action( 'get_header', array( &$this, 'setup' ), 1 );
		add_action( 'template_redirect', array( &$this, 'setup' ), 1 );
	}


	public function setup()
	{
		/** 
		 * Add scripts to these arrays to enqueue them. Listed below are 
		 * the 'sections' that come with wordpress out of the box... 
		 * listing them here is not strictly necessary but is used to 
		 * add additional transperency in to how this works.
		 *
		 * To add scripts use the 'nu_load_js' filter and add it to the 
		 * scripts array.
		 * 
		 * For more info on wp conditionals 
		 * @see http://codex.wordpress.org/Conditional_Tags
		 */

		/**
		 * Admin Page Scripts
		 * The 'admin' array is actually a nested array where each of 
		 * the second level keys indicates which admin 'section' that 
		 * the javascript should load on. The admin array should look 
		 * something like this:
		 * -- admin array --
		 * 	|all
		 *		|all.js
		 *		|all2.js
		 *
		 *	|ads
		 *		|-ads.js
		 */

		$scripts["admin"] = array();

		// For any archive page ( categories, tags, etc ) or home
		$scripts["archive"] = array();

		// IE only scripts
		$scripts["ie"] = array();

		// Login page only scripts
		$scripts["login"] = array();

		// For any single post, page, or attachment
		$scripts["singular"] = array(); 

		// Sitewide scripts (except login / profile pages)
		$scripts["sitewide"] = array();

		// To add scripts, just tap into this filter.
		$scripts = (array) apply_filters(  'nu_load_js' , $scripts );

		// save to the class variable
		$this->scripts = $scripts;

		if( self::$debug )
			nu_debug( 'Scripts', $scripts );

		// no matter what scripts will be enqueued the normal wordpress way. if the script is compiled (concatenated and minified) we will remove the enqueued script right before the scripts are to be printed
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ), 10, 1 );
		add_action( 'login_enqueue_scripts', array( &$this, 'enqueue' ), 10, 1 );

		// if $compile run scripts through the nu_compile_js class which will concatenate and minify the scripts
		$compile = nu_get_option( 'compile_js' );
		if( $compile ) {
			require_once( 'compile-js.class.php' );
			new nu_compile_js( $scripts );
		}
	}


	/**
	 * @param $hook
	 *	only set on admin pages to indicate which admin section is currently being
	 *  viewed
	 */
	public function enqueue( $hook = '' )
	{
		$scripts = $this->scripts;

		// Load admin scripts
		if( is_admin() && !empty( $scripts['admin'] ) )
			$this->enqueue_admin_scripts( $scripts['admin'], $hook );

		// Load archive scripts ( includes homepage )
		if( is_archive() || is_home() )
			$this->enqueue_scripts( $scripts['archive'], 'archive' );

		// Load IE scripts
		if ( $this->_is_ie() )
			$this->enqueue_scripts( $scripts['ie'], 'ie' );

		// Load login scripts
		if( nu_is_login() && !empty( $scripts['login'] ) )
			$this->enqueue_scripts( $scripts['login'], 'login' );

		// Load sitewide scripts (if not login or admin)
		if( !nu_is_login() && !is_admin() )
			$this->enqueue_scripts( $scripts['sitewide'], 'sitewide' );

		// Checks default wordpress conditionals and load the scripts
		if( !nu_is_login() && is_singular() )
			$this->enqueue_scripts( $scripts['singular'], 'singular' );

		do_action( 'nu_after_load_js' ); 
	}


	private function _is_ie()
	{
		if( eregi("MSIE", getenv( "HTTP_USER_AGENT" ) )
				|| eregi("Internet Explorer", getenv("HTTP_USER_AGENT" ) ) ) 
		{
			return true;
		}

		return false;
	}


	/**
	 * Determines which admin section/page is currently being viewed 
	 * and enqueues the scripts for that section/page.
	 *
	 * @uses $this->enqueue_scripts
	 */
	private function enqueue_admin_scripts( $scripts, $hook )
	{
		global $post;

		// the page query variable is set for custom admin pages
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		foreach( $scripts as $section=>$script_array ) {

			if( $section == 'all' ) 
				$this->enqueue_scripts( $scripts['all'] );

			if( $section == $hook || $section == $page )
				$this->enqueue_scripts( $script_array );

			if( !empty( $post) && $section == $post->post_type )
				$this->enqueue_scripts( $script_array );
		}
	}


	/**
	 * @uses $wp_enqueue_script
	 *
	 * @param $scripts (array) array of scripts handles to enqueue
	 * @param $section (string) only necessary for debug purposea
	 */
	private function enqueue_scripts( $scripts, $section=null )
	{	
		global $wp_scripts;
		
		if( self::$debug ) {
			nu_debug( "$section || Enqueued Scripts", $scripts );
			nu_debug( 'Scripts', $wp_scripts->registered );
		}



		if( !empty( $scripts ) ) {
			foreach( $scripts as $script ) {

				if ( wp_script_is( $script, $list='registered' ) ) {
					wp_enqueue_script( $script );
				}
			}
		}

		return null;
	}


	/**
	 * Registers JS with wordpress
	 *
	 * For this to work correctly, all admin section only scripts 
	 * should be placed in a directory called 'admin'. This is to 
	 * minimize conflict with frontend scripts that may use the same 
	 * filename.
	 *
	 * @param $basename (string) Arbitrary string to append to the 
	 * 	registration name. Usually a plugin or theme name
	 * 
	 * @return $name (string) The handle name for the registered script
	 */
	public static function reg_js( $path, $deps=NULL, $ver='0.1' ,$in_footer=true, $basename='' )
	{
		$is_fullpath = false;
		if ( false !== strpos($path, 'http') )
			$is_fullpath = true;

		$is_admin = false;
		if ( false !== strpos($path, 'admin/') )
			$is_admin = true;

		$name = self::get_reg_name( $path, $is_admin, $basename );

		// this is a fallback because full path should be used... but if not set the base path to either the parent of child theme (depending on which is being used)
		if ( !$is_fullpath ) {
			if( is_child_theme() )
				$path = get_stylesheet_directory_uri() . $path;
			else
				$path = get_template_directory_uri() . $path;
		}

		// convert $deps to an array if it is not an array
		if ( $deps != NULL and (array) $deps !== $deps )
			$deps = explode(',', preg_replace("/\s/", '', $deps) );

		if( self::$debug ) 
			nu_debug( 'JS Handle', $name );

		// register the script with wordpress
		wp_register_script( $name, $path, $deps, $ver, $in_footer );

		return $name;
	}


	/** 
	 * get_reg_name
	 * 
	 * Create the name for js registration. To create the name we strip out the file type, the version number, and any special characters from the filename. If this file is in the admin directory, we append 'admin-', and if a basename is provided we append the $basename followed by a '-'. For example if the script file name is 'jquery.test-1.1.min.js' the name would be 'jquery_test'.
	 *
	 * @param $path (string) The file path
	 * @param $is_admin (bool) Boolean indicating if this is an admin 
	 *	section only script
	 * @param $basename (string) Arbitrary string to append to the 
	 *	registration name. Usually a plugin or theme name
	 */ 
	public static function get_reg_name( $path, $is_admin=false, $basename='' )
	{
		$pos = strrpos( $path, '/' );
		$pos = $pos === false ? 0 : $pos + 1;
		$name = nu_utils::slice_string( $path, $pos );
		$name = preg_replace( "/((-[0-9\.]+)?(\.min)?(\.js)$)/", '', $name );
		$name = nu_utils::clean_string( $name, 50, '_' );

		if( $is_admin )
			$name = 'admin-' . $name;

		if( !empty($basename) )
			$name = $basename . '-' . $name;

		return $name;
	}
}


/**
 Template Tags
 */
function nu_reg_js( $path, $deps=NULL, $ver='0.1', $in_footer=true, $basename='' )
{
	nu_lazy_load_js::reg_js( $path, $deps, $ver, $in_footer, $basename );
}