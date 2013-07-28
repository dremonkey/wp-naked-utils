<?php

class nu_utils 
{
	/**
	 * clean_string()
	 *
	 * Creates machine friendly strings
	 */
	public static function clean_string( $phrase, $max_length=50, $sub='-' ) 
	{
    $result = strtolower($phrase);
    $result = preg_replace("/[^a-z0-9\s-\.]/", $sub, $result);
    $result = trim(preg_replace("/[\s-\.]+/", " ", $result));
    $result = trim(substr($result, 0, $max_length));
    $result = preg_replace("/\s/", $sub, $result);

    return $result;
	}


	/**
	 * slice_string
	 *
	 * The start of the range is inclusive and the end is exclusive.
	 * Based on the python slice function
	 *
	 * @param input (str)
	 *	the string to be sliced up
	 *
	 * @param slice (mixed) 
	 *	Can be a single character index, or a range separated by a colon. If it is a single
	 *	character index then that will be used as the start position, with the end position 
	 *	being the end of the string
	 */

	public static function slice_string($input, $slice) {

		$start = 0;
		$end = strlen($input);

		if ( is_int($slice) ) {
			// If it is only a number then this is the start point
			$start = $slice;

			// return a new string from that point until the end. 
			return substr($input, $start);
		}  else { 

			// If it is a colon delimited string then the first number is
			// the start point and the second number is the end point
			$args = explode(':', $slice);

			// If the first value is not empty, set the start val
			if ( !trim($args[0]) == '' ) $start = intval($args[0]);
			
			// If the second value is not empty set the end val
			if ( !trim($args[1]) == '' ) $end = intval($args[1]);

			// If end is less than zero, we need to adjust
			if ($end < 0) $end += $end;
		}
	    
	    return substr($input, $start, $end - $start);
	}


	/** 
	 * self_uri()
	 * @return (str) the URL of the current page 
	 */
	public static function self_uri() 
	{
    $url = 'http';
    $script_name = '';

    if (isset($_SERVER['REQUEST_URI'])) {
    	$script_name = $_SERVER['REQUEST_URI'];
    }
    else {
    	$script_name = $_SERVER['PHP_SELF'];

      if ($_SERVER['QUERY_STRING'] > ' ')
          $script_name .= '?' . $_SERVER['QUERY_STRING'];
    }

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    	$url .= 's';

    $url .= '://';

    if ($_SERVER['SERVER_PORT'] != '80')
    	$url .= $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . $script_name;
    else
    	$url .= $_SERVER['HTTP_HOST'] . $script_name;
	
    return $url;
	}


	/**
	 * substr_utf8()
	 * @return (str) subset of the string passed in with length of $len characters
	 */
	public static function substr_utf8( $str, $len, $tail = '...')
	{
		$str = str_replace("&quot;", '"', $str);
		$str_len_before = strlen( $str );
		$str = mb_substr( $str, 0, $len, 'utf-8' );
		$str_len_after = strlen( $str );

		// if no trimming was required, then do not add the tail
		if( $str_len_before != $str_len_after ) {
			$str .= $tail;
		}

		return $str;
	}


	public static function get_file_content( $file )
  {
    if( file_exists( $file ) ) {	
      ob_start();
      include( $file );
      return ob_get_clean();
    }

    return false;
  }
}


/**
 * nu_twitter_date()
 * 
 * Twitter style post dates // 트위터 스타일의 날짜 표시법
 * i.e. instead of 'Posted 13 Jan 2011 at 7:03' it displays '3 hours ago'
 * 
 * @param $date (mixed) 	str|integer representing the time. If it is a string
 * 	this function will attempt to converter it to its int time equivalent
 * @return (arr) First value is the date, and second is boolean telling us 
 * whether or not it has been converted to twitter style
 */
function nu_twitter_date( $date, $format=null ) 
{
	if( is_null( $date ) ) 
		return;

	if( is_numeric( $date ) ) {
		$t = (int) $date;
	}
	else if ( is_string( $date ) ) {
		$t = strtotime( $date );
	}

	// date_i18n("U") is wordpress date (in tstamp) based on 
	// timezone setting. time() - server time - must be gmt
	$offset = date_i18n("U") - time();

	$tmptime = time() - $t + $offset;

	if($tmptime < 0)
		$time_str = "1s ago";
	else if ($tmptime < 3600)
		$time_str = sprintf( __( '%dm ago' , THEME_NAME ), (int)($tmptime/60) );
	else if ($tmptime < 7200)
		$time_str = sprintf( __( '%dh ago' , THEME_NAME ), (int)($tmptime/3600) );
	else if ($tmptime < 86400)
		$time_str = sprintf( __( '%dh ago' , THEME_NAME ), (int)($tmptime/3600) );
	else if ($tmptime < 172800)
		$time_str = sprintf( __( '%dd ago' , THEME_NAME ), (int)($tmptime/86400) );
	else if ($tmptime < 604800)
		$time_str = sprintf( __( '%dd ago' , THEME_NAME ), (int)($tmptime/86400) );
	else {

		if( !$format ) 
			$format = get_option('date_format');
		
		$time_str = date( $format, $date );
	}

	$date = $time_str;
	return $date;
}


function nu_time_since( $original, $do_more = 0 ) 
{
	// array of time period chunks
	$chunks = array(
		array( 60 * 60 * 24 * 365 , 'year' ),
		array( 60 * 60 * 24 * 30 , 'month' ),
		array( 60 * 60 * 24 * 7, 'week' ),
		array( 60 * 60 * 24 , 'day' ),
		array( 60 * 60 , 'hour' ),
		array( 60 , 'minute' ),
	);

	$today = time();
	$since =  $today - $original;

	for ( $i = 0, $j = count( $chunks ); $i < $j; $i++ ) {
		$seconds = $chunks[$i][0];
		$name = $chunks[$i][1];

		if ( ( $count = floor( $since / $seconds ) ) != 0 )
			break;
	}

	$print = ( $count == 1 ) ? '1 ' . $name : $count . ' ' . $name . 's';

	if ( $i + 1 < $j ) {
		$seconds2 = $chunks[$i + 1][0];
		$name2 = $chunks[$i + 1][1];

		// add second item if it's greater than 0
		if ( ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2 ) ) != 0 ) && $do_more )
			$print .= ( $count2 == 1 ) ? ', 1 ' . $name2 : ", $count2 {$name2}s";
	}

	return $print;
}