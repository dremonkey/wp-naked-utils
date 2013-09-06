<?php

class nu_debug_styles_panel extends Debug_Bar_Panel
{
  public function __construct()
  {
    add_filter( 'debug_bar_panels', array( &$this, 'add_panel' ) );

    // call the parent constructor
    parent::__construct();
  }

  public function init() 
  {
    $this->title( __('Show Styles', 'naked-utils') );
  }

  public function prerender() 
  {
    $this->set_visible( true );
  }

  public function render() 
  {
    global $wp_styles;

    echo "<div id='debug-styles'>";

      echo '<h3>Registered Styles</h3>';
      var_dump($wp_styles->registered);
      echo '<br/><br/>';
      echo '<h3>Queued Styles</h3>'; 
      var_dump($wp_styles->queue);

    echo "</div>";
  }


  public function add_panel( $panels )
  {
    $panels[] = &$this;
    return $panels;
  }
}