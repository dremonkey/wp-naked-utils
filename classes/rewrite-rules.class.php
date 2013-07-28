<?php
/** 
 * If you are having problems making the rules appear, call flush_rules().
 * For a better understanding of how to use rewrite rules and what they do:
 *
 * @see http://codex.wordpress.org/Class_Reference/WP_Rewrite
 * @see http://www.wpinsideout.com/adding-query-vars-and-rewrite-rules
 * 
 */
class nu_rewrite_rules 
{
	private static $debug = false;

	private $query_vars; // query_vars we want to add
	private $rules; //rewrite rules to add... these are regexs
	private $force_flush; //set to true to force wp to flush the rewrite rules
	
	public function __construct( $options = NULL ) 
	{
		if (!is_null($options)) {
			$this->init($options);
		}
	}
	
	/**
	 * @param $options (associative array)
	 */
	public function init( $options )
	{
		foreach($options as $key=>$value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * add_query_vars()
	 *
	 * filter function to be used with add_filter() with the wp filter "query_vars"
	 * @param $query_vars (array) pre-existing query_vars that we are adding our 
	 * query_vars to
	 */
	public function add_query_vars( $qv ) 
	{
		$new_query_vars = $this->query_vars;
		
		if ($new_query_vars)
			foreach($new_query_vars as $var) {
				$qv[] = $var;
			}

		$this->debug( 'Query Vars', $qv );

		return $qv;
	}


	/**
	 * add_rules
	 *
	 * Add the rewrite rules. This will only run if the rule has not yet
	 * been added (or if you flush the rules).
	 *
	 * @uses the wp rewrite_rules_array filter
	 */ 
	public function add_rules( $rewrite_rules ) 
	{
		// add our new rewrite rules to front of the existing list
		$rewrite_rules = $this->rules + $rewrite_rules;

		$this->debug( 'Adding Rewrite Rules', $this->rules );

		return $rewrite_rules;
	}

	
	/**
	 * rules_exist()
	 * 
	 * Adding rewrite rules is laborious so check if we need to add them
	 *
	 * @return (bool) true if rules exist, false if they don't
	 */
	public function rules_exist()
	{
		$rules 			= get_option( 'rewrite_rules' );
		$rules_regex 	= array_keys( $rules );
		
		$has_rules = true;
		if ( $this->rules ) {
			foreach( $this->rules as $regex=>$rule ) {
				if( !$rules ) 
					break;

				if( !in_array( $rule, $rules ) || !in_array( $regex, $rules_regex ) ){
					$has_rules = false;
				}
			}
		}

		$this->debug( 'Rewrite Rules Exist?', array( $this->rules, $has_rules ) );
		
		return $has_rules;
	}
	
	/**
	 * flush_rules()
	 * 
	 * Add the new rules if they don't exist
	 *
	 * @return (bool) true if rules exist, false if they don't
	 */
	public function flush_rules()
	{
		global $wp_rewrite;

		if( ( is_multisite() && current_user_can( 'manage_network' ) )
			|| !is_multisite() && current_user_can( 'activate_plugins' ) ) {
			// Display a reminder to remove the force
			if ( $this->force_flush ) {
				echo '<br/>****************************************************<br/>';
				echo 'You are forcing the rewrite rules to flush. This is a laborious task so remember to turn this off before you deploy.';
				echo '<br/>****************************************************<br/>';
			}

			if( !$this->rules_exist() || $this->force_flush ){
				$wp_rewrite->flush_rules(); 

				// failsafe... this extra check is done again because if memcache is configured incorrectly, flush_rules() does not delete the rewrite_rules data from memcache even though it is deleted from the wp_options table. In this event, the rewrite_rules() method is triggered from here to force the rules to update.
				// if( !$this->rules_exist() ) {
				// 	$wp_rewrite->rewrite_rules();
					
				// 	$this->debug( 'Flush Rules - Memcache Problem', $wp_rewrite->rules );
					
				// 	update_option( 'rewrite_rules', $wp_rewrite->rules );
				// }
			}
		}
	}


	private function debug( $title, $var )
	{
		if( self::$debug )
			nu_debug( $title, $var );
	}
}

?>