<?php

class nu_debug_hook_panel extends Debug_Bar_Panel
{
	public function __construct()
	{
		add_filter( 'debug_bar_panels', array( &$this, 'add_panel' ) );

		// call the parent constructor
		parent::__construct();
	}

	public function init() 
	{
		$this->title( __('Show Hooks', 'naked-utils') );
	}

	public function prerender() 
	{
		$this->set_visible( true );
	}

	public function render() 
	{
		global $wp_filter;

		echo "<div id='debug-hooks'>";

			echo '<h3>Filters</h3>';
			echo '<pre>';
			foreach( $wp_filter as $tag=>$priority ) {
				
  				echo "&gt;&gt;&gt;&gt;&gt;\t<strong>$tag</strong><br />";
  				ksort($priority);
  				
  				foreach( $priority as $priority => $function ){
  					echo $priority;
  				
  					foreach( $function as $name => $properties ) 
  						echo "\t$name<br />";
  				}

  				echo '<br/>';
 			}
 			echo '</pre>';

		echo "</div>";
	}


	public function add_panel( $panels )
	{
		$panels[] = &$this;
		return $panels;
	}
}