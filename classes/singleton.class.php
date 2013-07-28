<?php

abstract class nu_singleton
{
	private static $_instances = array();

	/**
	 * Create an instance of this class (if it doesn't exist) and
	 * returns it
   *
   * @param $new (bool) force this to create a new instance
	 */
  public static function get_instance( $new = false ) 
  {
    $classname = get_called_class();

    if ( $new || !isset(self::$_instances[$classname]) ) {
      self::$_instances[$classname] = new $classname();

      // nu_debug( 'Creating Class Instance', $classname );
    }
    
    return self::$_instances[$classname];
  }


  public static function delete_instance( $classname )
  {
  	// clear all instances
  	if( $classname == 'all' ){
  		self::$_instances = array();
  		return true;
  	} 
  	else {
	  	if( isset( self::$_instances[$classname] ) ) {
	  		unset( self::$_instances[ $classname] );
	  		return true;
	  	}
  	}

  	// if nothing was deleted
  	return false;
  }


  public static function show_instances()
  {
    echo '<pre>';
    var_dump( self::$_instances );
    echo '</pre>';
  }
}