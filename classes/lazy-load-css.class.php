<?php

/**
 * Used to improve Wordpress frontend CSS handling so that stylesheets can be added 
 * lazily and compiled
 */

class nu_lazy_load_css extends nu_singleton
{
	private static $debug = false;

	public $styles = array();

	/**
	 * __construct()
	 */
	public function __construct()
	{
		add_action( 'admin_init', array( &$this, 'setup') );

		// Setup lazy load stuff - needs to fire after we know what page is going to be loaded
		// add_action( 'get_header', array( &$this, 'setup' ), 1 );
		add_action( 'template_redirect', array( &$this, 'setup' ), 1 );
	}


	public function setup() 
	{
		/** 
		 * Add styles to these arrays to enqueue them. Listed below are the 'sections'
		 * that come with wordpress out of the box... listing them here is not strictly
		 * necessary but is used to add additional transperency in to how this works.
		 *
		 * To add styles for custom post_types or pages use the 'nu_load_css' filter
		 * and add it to the styles array. The array key should be the same as the
		 * name of your custom post_type (i.e. if you create a post_type called showcase
		 * then to add the styles that will only be called on those pages use the 'nu_load_css'
		 * filter to add a new styles['showcase'] array entry)
		 * 
		 * For more info on wp conditionals see http://codex.wordpress.org/Conditional_Tags
		 */

		/**
		 * For admin pages
		 * The 'admin' array is actually a nested array where each of the second level keys indicates 
		 * which admin 'section' that the javascript should load on. The admin array should look 
		 * something like this:
		 * -- admin array --
		 * 	|all
		 *		|all.css
		 *		|all2.css
		 *
		 *	|ads
		 *		|-ads.css
		 */
		$styles["admin"] = array();

		// Sitewide styles (except login page)
		$styles["sitewide"] = array();

		// IE only styles
		$styles["ie"] = array();

		// Login page
		$styles["login"] = array();

		// To add styles from the controllers, just tap into this filter.
		$styles = (array) apply_filters(  'nu_load_css' , $styles );

		// save to the class variable
		$this->styles = $styles;

		/**
		 * wp_enqueue_scripts action is used because that is what is recommended. 
		 * @see http://wpdevel.wordpress.com/2011/12/12/use-wp_enqueue_scripts-not-wp_print_styles-to-enqueue-scripts-and-styles-for-the-frontend/
		 *
		 * there is no login_enqueue_styles hook so login_enqueue_scripts is used and 
		 * priority is set to 9 to make sure that the styles are loaded before the 
		 * scripts
		 */

		// no matter what styles will be enqueued the normal wordpress way. if the style is compiled (concatenated) we will remove the enqueued style right before the styles are to be printed
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue' ), 10 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue' ), 10 );
		add_action( 'login_enqueue_scripts', array( &$this, 'enqueue' ), 9 );

		// if $compile run scripts through the nu_compile_js class which will concatenate and minify the scripts
		$compile = nu_get_option( 'compile_css' );
		if( $compile ) {
			require_once( 'compile-css.class.php' );
			new nu_compile_css( $styles );
		}
	}


	/**
	 * @param $hook
	 *	only set on admin pages to indicate which admin section is currently being
	 *  viewed
	 */
	public function enqueue( $hook = '' )
	{
		$styles = $this->styles;

		// Load admin styles
		if( is_admin() && isset( $styles['admin'] ) )
			$this->enqueue_admin_styles( $styles['admin'], $hook );

		// Load login styles
		if( nu_is_login() && isset( $styles['login'] ) )
			$this->enqueue_login_styles( $styles['login'] );

		// Load sitewide styles (if not login or admin)
		if( !nu_is_login() && !is_admin() && isset( $styles['sitewide'] ) )
			$this->enqueue_styles( $styles['sitewide'] );

		// Load ie styles
		if( $this->_is_ie() && isset( $styles['ie'] ) )
			$this->enqueue_styles( $styles['ie'] );
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
	 * Determines which admin section/page is currently being viewed and enqueues the styles
	 * for that section/page.
	 *
	 * @uses $this->enqueue_styles
	 */
	private function enqueue_admin_styles( $styles, $hook )
	{
		global $post;

		// the page query variable is set for custom admin pages
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		foreach( $styles as $section=>$style_array ) {
			if( $section == 'all' )
				$this->enqueue_styles( $styles['all'] );

			if( $section == $hook || $section == $page )
				$this->enqueue_styles( $style_array );	

			if( !empty( $post) && $section == $post->post_type )
				$this->enqueue_styles( $style_array );
		}
	}


	/**
	 * @uses wp_admin_css()
	 */
	public function enqueue_login_styles( $styles )
	{
		global $pagenow;

		// if on the wp-login styles need to be added using wp_admin_css
		if( in_array( $pagenow, array( 'wp-login.php' ) ) ) {
			if( !empty( $styles ) ) {
				foreach( $styles as $style ) {
					wp_admin_css( $style, true );
				}
			}
		}
		else {
			$this->enqueue_styles( $styles );
		}
	}


	private function enqueue_styles( $styles )
	{
		if( !empty( $styles ) ) {
			foreach( $styles as $style ) {
				if ( wp_style_is( $style, $list='registered' ) ) {

					if( self::$debug )
						nu_debug::var_dump( $style );

					wp_enqueue_style( $style );
				}
			}
		}

		return null;
	} 


	/**
	 * Registers CSS with wordpress
	 */
	public static function reg_css( $path, $deps=false, $ver='0.1', $media='all', $basename='' )
	{
		$is_fullpath = false;
		if ( strpos( $path, 'http' ) !== false )
			$is_fullpath = true;

		$is_admin = false;
		if ( strpos($path, 'admin/') !== false )
			$is_admin = true;

		$name = self::get_reg_name( $path, $is_admin, $basename );

		// this is a fallback because full path should be used... but if not set the base path
		// to either the parent of child theme (depending on which is being used)
		if ( !$is_fullpath ) {
			if( is_child_theme() )
				$path = get_stylesheet_directory_uri() . $path;
			else
				$path = get_template_directory_uri() . $path;
		}

		// convert $deps to an array if it is not an array
		if ( $deps != NULL and (array) $deps !== $deps )
			$deps = explode(',', preg_replace("/\s/", '', $deps) );

		wp_register_style( $name, $path, $deps, $ver, $media );

		return $name;
	}


	/** 
	 * get_reg_name
	 * 
	 * Create the name for css registration. To create the name we strip out the file
	 * type, directory, and any special characters from the filename. If this
	 * file is in the admin directory, we append 'admin-', and if a basename is provided
	 * we append the $basename followed by a '-'. For example if the css file path is 
	 * 'css/naked-test.admin.css' the name would be 'naked_test_admin'.
	 */ 
	public static function get_reg_name( $path, $is_admin=false, $basename='' )
	{
		$pos = strrpos( $path, '/' );
		$pos = $pos === false ? 0 : $pos + 1;
		$name = nu_utils::slice_string( $path, $pos );
		$name = preg_replace( "/((-[\.]+)?(\.css)$)/", '', $name );
		$name = nu_utils::clean_string( $name, 50, '_' );

		if( $is_admin )
			$name = 'admin-' . $name;

		if( !empty($basename) )
			$name = $basename . '-' . $name;

		if( self::$debug ) 
			nu_debug::var_dump( $name );

		return $name;
	}
}