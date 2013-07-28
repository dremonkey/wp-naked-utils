<?php

/**
 * Abstract class to be extended by other plugins in order to simplify the creation
 * of a 'settings' page for the plugin and avoid duplicate code.
 */

abstract class nu_settings extends nu_singleton
{
	// make sure all of these class variables are set in the child class
	public $options_page_key; // used to create the page slug
	public $options_group;		// ... can't remember ... - Andre
	public $options_key;			// var name used to store the options in the db
	public $cap_level;				// the user capability level required for access
	public $page_title; 			// the settings page title
	public $menu_title;				// the settings page menu title


	/**
	 * The minimum required to create the settings page
	 *
	 * Should be called from the constructor
	 *
	 * @uses self::set_class_vars
	 * @uses self::add_options_menu
	 * @uses self::reg_settings
	 */
	public function init_settings()
	{
		$this->set_class_vars();

		add_action( 'admin_menu', array( &$this, 'add_options_menu' ) );
		add_action( 'admin_init', array( &$this, 'reg_settings' ) );
	}


	/**
	 * Sets the values of all class variables. Called by self::init_menu().
	 */
	abstract function set_class_vars();


	/**
	 * Adds the option page to the settings menu on the wordpress admin page.
	 */
	public function add_options_menu()
	{
		$page_title = $this->page_title;
		$menu_title = $this->menu_title;
		$cap 				= $this->cap_level;
		$menu_slug 	= $this->options_page_key;
		$callback 	= array( &$this, 'get_options_page' );

  	add_options_page( $page_title, $menu_title, $cap, $menu_slug, $callback );
	}


	public function get_options_page() 
	{
		// setup some template vars
		$page_title 		= $this->page_title;
		$page_key 			= $this->options_page_key;
		$options_group 	= $this->options_group;

		// grab the settings template
		$views_dir = NU_PATH . 'views/';
    $tpl_path = $views_dir . 'settings.php';

		include( $tpl_path );
	}


	/**
	 * Register the settings.
	 *
	 * @uses admin_init hook
	 */
	public function reg_settings()
	{
		// Register the settings. All settings will be stored in one options field as an array.
        register_setting( $this->options_group, $this->options_key );

        $this->reg_setting_sections();

        $this->reg_setting_fields();
	}


	/**
	 * Sets up the setting page sections
	 */
	public function reg_setting_sections()
	{
		$sections = $this->get_setting_sections();

		foreach( $sections as $id=>$section ) {
    	extract( $section );
    	add_settings_section( $id, $title, $callback, $page );	
    }
	}


	public function reg_setting_fields()
	{
		$fields = $this->get_setting_fields();

		foreach( $fields as $id=>$field ) {
			extract( $field );
    	add_settings_field( $id, $title, $callback, $page, $section, $args );
    }
	}


	/**
	 * Called by self::reg_setting_sections().
	 *
	 * @return (array) A list of setting sections.
	 */
	abstract function get_setting_sections();


	/**
	 * Called by self::reg_setting_fields().
	 *
	 * @return (array) A list of setting fields
	 */
	abstract function get_setting_fields();


	/**
	 * @return (str) the section description
	 */
	abstract function get_section_desc( $args );


	public function build_form_fields( $args ) 
	{
		extract( $args );

		// prepare the template variables
		$name 					= $this->options_key . "[$id]";
		$saved_options 	= $this->get_options();

		switch ($type) {
			case 'multi-checkbox-horizontal':
			case 'multi-checkbox-vertical':
				// the saved option values for the current checkbox
				$saved_options = $saved_options[$id];
				break;
			
			default:
				// the saved value for the current setting
				if( 'button' != $type )
					$value = esc_attr( $saved_options[$id] );
				break;
		}

		// include the field template
		$file = $type . '.inc';
		$tpl_path = NU_VIEWS_DIR . 'setting-fields/' . $file;
		include( $tpl_path );
	}


	/**
 	 Helper functions
	 */

	/**
	 * @return (array) the default options values 
	 */
	abstract function get_default_option_values();
	

 	/**
	 * Returns an array of all theme options. If an option has been previously set,
	 * the stored option value will be return. If not, then the default option value 
	 * will be returned.
	 *
	 * @uses get_option()
	 */
	public function get_options()
	{
		$options = (array) get_option( $this->options_key );

	  // Merge with defaults
	  $options = array_merge( $this->get_default_option_values(), $options );

   	return $options;
	}


	/**
	 * Retrieves a single option value. If an option has been previously set, the 
	 * stored option value will be return. If not, then the default option value will 
	 * be returned.
	 */
	public function get_option_value( $option )
	{
		$options = $this->get_options();
		$value = isset( $options[ $option ] ) ? $options[ $option ] : null;
		return $value;
	}
}