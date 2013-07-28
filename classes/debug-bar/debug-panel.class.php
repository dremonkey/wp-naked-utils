<?php

class nu_debug_panel extends Debug_Bar_Panel
{
	public function __construct()
	{
		add_filter( 'debug_bar_panels', array( &$this, 'add_panel' ) );

		// call the parent constructor
		parent::__construct();
	}

	public function init() 
	{
		$this->title( __('Dump', 'naked-utils') );
	}

	public function prerender() 
	{
		$this->set_visible( true );
	}

	public function render() 
	{
		global $nu_debug;

		echo "<div id='debug-bar-dump'>";

		if( !empty( $nu_debug ) ) {
			foreach( $nu_debug as $title=>$time ) {
				echo '<h3>'. $title .'</h3>';

				$count = 0;

				foreach( $time as $info ) {

					foreach( $info as $key=>$val ) {
						if ( 'trace' == $key ) {
							foreach( $val as $i=>$trace ) {
								$file = $trace['file'];
								$line = $trace['line'];
								echo "<p class='trace depth-$i' style='font-size:11px;'>";
								echo "Called by $file | line: $line";
								echo "</p>";
							}
						}
						elseif( 'var' == $key ) {
							$count++;
							echo '<pre><strong>';
							var_dump( $val );
							echo '</strong></pre>';
						}
					}
				}

				echo "<p style='margin-bottom: 30px;'><strong>number of variables dumped: $count</strong></p>";
			}
		}

		echo "</div>";
	}


	public function add_panel( $panels )
	{
		$panels[] = &$this;
		return $panels;
	}
}