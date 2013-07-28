<?php

class nu_media_utils
{
	private static $debug = false;

	/**
	 * get_img_src()
	 * 
	 * Retrieves the image source for a content object using a post id (can be either a post
	 * or attachment id).
	 *  
	 * This function first checks to see if a post thumbnail has been set, if not it looks 
	 * at all the images attached to the post and retrieves the source for the first image 
	 * that matches size
	 *
	 * @param $post_id (int)
	 * 	The object id of a content item ( post_id or other )
	 * @param $size ( string or array of sizes )
	 *	The string keyword indicating which size is to be returned. By default wordpress
	 * 	creates 'thumbnail', 'medium', 'large' or 'full' sizes. If custom sizes have been
	 * 	set up then those can be passed in as well. If an array is passed in then the source 
	 *  for all the sizes will be returned
	 *
	 * @return array
	 *	Returns an array containing the image id and the sizes requested
	 *	| id  - (int)
	 *		| sizes - (array of sizenames)
	 *			| $sizename - (array)
	 *				| src - img source
	 *				| width - img width
	 *				| height - img height
	 */
	public static function get_img_src( $id=null, $size='thumbnail' )
	{
		$img = null; // the return object
		$post_id = null;
		$img_id  = null;
		
		if ( !$id ) {
			global $post;
			$post_id = $post->ID;
		}
		else {
			// check to see if the id passed in was the img_id or a post_id
			$post_type = get_post_type( $id ); 

			if( 'attachment' == $post_type )
				$img_id = $id;
			else 
				$post_id = $id;	
		}

		// attempt to get the grab the data from object cache
		$the_id = $post_id ? $post_id : $img_id; // one or the other should be set at this point.
		$size_str = is_array($size) ? implode('',$size) : $size;
		$cache_key = $the_id . '-' . $size_str;
		$cache_group = 'media_utils:get_img_src';
		$img = wp_cache_get( $cache_key, $cache_group );

		if (!$img) {

			// try getting the featured image post thumbnail if the post thumbnails are
			// enabled for the theme
			if( function_exists('get_post_thumbnail_id') && $post_id )
				$img_id = get_post_thumbnail_id( $post_id );

			if( self::$debug ) {
				nu_debug::var_dump( $post_id );
				nu_debug::var_dump( $img_id );
			}
			
			/** 
			 * if the featured image or an image id was passed in retrieve the image source(s)
			 * for the request sizes
			 */

			$sizes = array(); // array of src, width, and height to return
			if ( $img_id ) {
				if( is_array( $size ) ) {
					foreach( $size as $s ) {

						$img = wp_get_attachment_image_src( $img_id, $s );

						$sizes[ $s ] = array( 
							'src' 	 => $img[0],
							'width'  => $img[1],
							'height' => $img[2],
						);
					}
				}
				else {
					$s = $size;

					$img = wp_get_attachment_image_src( $img_id, $s );

					$sizes[ $s ] = array( 
						'src' 	 => $img[0],
						'width'  => $img[1],
						'height' => $img[2],
					);
				}
			}
			// if there is no featured image set 
			else {
				// retrieve all the images for a post
				$args = array( 
					'post_parent' => $post_id, 
					'post_type' => 'attachment', 
					'post_mime_type' => 'image' 
				);

				$images = get_children( $args );

				if( self::$debug )
					nu_debug::var_dump( $images );

				// if images exist, grab the first one that has the size that we need
				if ( $images ) {

					foreach ( $images as $image ) {

	        	$meta = wp_get_attachment_metadata( $image->ID );

	        	// array of all thumbnail sizes for the image
	        	$all_sizes = $meta['sizes'];

	        	if( is_array( $size ) ) {
	        		if( self::_has_all_image_sizes( $size, $all_sizes ) ) {

	        			$img_id = $image->ID;

	        			foreach( $size as $s ) {
	        				
									$img = wp_get_attachment_image_src( $img_id, $s );

	        				$sizes[ $s ] = array( 
										'src' 	 => $img[0],
										'width'  => $img[1],
										'height' => $img[2],
									);
	        			}

	        			if( self::$debug ) {
									nu_debug::var_dump( $post_id );
									nu_debug::var_dump( $img_id );
								}

	        			// to speed up future calls if featured images is enabled for the theme 
		        		// then set the featured image
		        		//
		        		// @todo set the largest attached image (as the featured image)
		        		if( function_exists( 'set_post_thumbnail' ) )
		        			set_post_thumbnail( $post_id, $img_id );

		        		// stop the loop once we have found the image
		        		break;
	        		}
	        	}
	        	else {
	    			$s = $size;
		        	if( $all_sizes[ $s ] ) {
		        		$img_id = $image->ID;
		        		
								$img = wp_get_attachment_image_src( $img_id, $s );

		        		$sizes[ $s ] = array( 
									'src' 	 => $img[0],
									'width'  => $img[1],
									'height' => $img[2],
								);

								if( self::$debug ) {
									nu_debug::var_dump( $post_id );
									nu_debug::var_dump( $img_id );
								}

		        		// to speed up future calls if featured images is enabled for the theme 
		        		// then set the featured image. this is basically the equivalent of caching
		        		// the image
		        		if( function_exists( 'set_post_thumbnail' ) )
		        			set_post_thumbnail( $post_id, $img_id );

		        		break;
		        	}
	        	}
					}
				} // end if( $images )
			}

			// grab the image alt tag 
			if ($img_id) {
				$alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
				if (!$alt) $alt = esc_attr(get_the_title($img_id));
			}
			
			if( self::$debug ) {
				nu_debug::var_dump( $img_id );
				nu_debug::var_dump( $sizes );
			}

			// set return object
			if( $img_id && $sizes && $alt ) {
				$img = array(
					'id'  	=> $img_id,
					'alt'		=> $alt,
					'sizes' => $sizes
				);
			}

			wp_cache_set( $cache_key, $img, $cache_group );
		}

		// allow theme and plugins to change the returned img. this is especially useful for
		// custom post types that do not have a featured image or any attached images
		$img = apply_filters( 'nu_get_img_src', $img, $id, $sizes );

		return $img;
	}


	/**
	 * Helper function used to determine if all the image sizes that are being
	 * requested are available.
	 *
	 * @return bool
	 */
	private static function _has_all_image_sizes( $size, $all_sizes )
	{
		// var_dump( $size );
		// var_dump( $all_sizes );

		// remove full from the values to check
		if( $key = array_search( 'full', $size ) )
			unset( $size[ $key ] );

		$all_sizes = array_keys( $all_sizes );

		$diff = array_diff( $size, $all_sizes );

		// if $diff is empty then the thumbnail has all the sizes
		// we need
		if( empty( $diff ) )
			return true;

		return false;
	}


	/**
	 * Copy a featured image as an attachment to a post if not already attached.
	 * This should also probably be attached to the save_post action within the theme. 
	 *
	 * Called by get_img_src()
	 */
	public static function attach_featured_image_to_post( $post_id ) 
	{
		global $wpdb;

		// if featured image is not enabled stop!
		if( !function_exists( 'set_post_thumbnail' ) )
			return;
		
		// If a wrong post is passed or the post does not have a featured image attached
		if( ! is_numeric($post_id) ||  0 === (int) $post_id )
			return -1;
		elseif ( ! has_post_thumbnail( $post_id ) )
			return -1;
		
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		$attachment = get_post($post_thumbnail_id);
		
		// if it's already attach to the post then exit as nothing is needed
		if( $post_id === $attachment->post_parent )
			return -1;

		$attachment->metadata      = get_post_meta($attachment->ID, '_wp_attachment_metadata', true);
		$attachment->attached_file = get_post_meta($attachment->ID, '_wp_attached_file', true);

		unset($attachment->ID);
		
		// copy main attachment data
		$attachment_id = wp_insert_attachment( $attachment, false, $post_id );
		
		// copy attachment custom fields
		$acf = get_post_custom($post_thumbnail_id);
		
		foreach( $acf as $key => $val )
		{
			foreach( $val as $v )
			{
				add_post_meta($attachment_id, $key, $v);
			}
		}
		
		// other meta values	
		update_post_meta( $attachment_id, '_wp_attached_file',  $attachment->attached_file );
		update_post_meta( $attachment_id, '_wp_attachment_metadata', $attachment->metadata );

		/* copies and originals */

		// if we're duplicating a copy, set duplicate's "_is_copy_of" value to original's ID
		if( $is_a_copy = get_post_meta($post_thumbnail_id, '_is_copy_of', true) )
			$post_thumbnail_id = $is_a_copy;
		
		update_post_meta($attachment_id, '_is_copy_of', $post_thumbnail_id);
		
		// meta for the original attachment (array holding ids of its copies)
		$has_copies   = get_post_meta($post_thumbnail_id, '_has_copies', true);
		$has_copies[] = $attachment_id;
		$has_copies = array_unique($has_copies);
		
		update_post_meta($post_thumbnail_id, '_has_copies',  $has_copies);
		
		/*  / copies and originals */

		// copy media tags
		if ( taxonomy_exists('media-tags') ) {

			$media_tags = wp_get_object_terms( array($post_thumbnail_id), 'media-tags' );

			$tags = array();
			
			foreach( $media_tags as $mt )
			{
				$tags[] = $mt->name;
			}
			
			wp_set_object_terms($attachment_id, $tags, 'media-tags');
		}
		
		// Delete current post thumbnail 
		delete_post_thumbnail( $post_id );

		// Finally set the post thumbnail to the duplicate
		set_post_thumbnail( $post_id, $attachment_id );

		return $attachment_id;
	}


	/**
   Grabs and external image, uploads it to the wp_upload_dir, and attaches it to a
   given post_id
	 */

	public static function save_external_image( $url, $post_id )
	{
		$uploads = wp_upload_dir();

		$path 			= $uploads['path'];
		$basename 		= basename( $url );
		$filename 		= wp_unique_filename( $path, $basename, null );
		$wp_filetype 	= wp_check_filetype( $filename, null );
		$fullfilename 	= $uploads['path'] . "/" . $filename;

		try {
			if ( !substr_count( $wp_filetype['type'], "image" ) ) {
				$msg = $basename . ' is not a valid image. ' . $wp_filetype['type'];
				throw new Exception( $msg );
			}
		
			$image_string = self::_fetch_image( $url );

			$fileSaved = file_put_contents( $fullfilename, $image_string );

			if ( !$fileSaved ) {
				throw new Exception("The file cannot be saved.");
			}

			// set the attachment title to the title of the post
			$title = sprintf( __( '%s Video Thumbnail', 'naked' ), get_the_title( $post_id ) );
			
			$attachment = array(
				 'post_mime_type' => $wp_filetype['type'],
				 'post_title' => $title,
				 'post_content' => '',
				 'post_status' => 'inherit',
				 'guid' => $uploads['url'] . "/" . $filename
			);

			$attach_id = wp_insert_attachment( $attachment, $fullfilename, $post_id );

			if ( !$attach_id ) {
				throw new Exception("Failed to save record into database.");
			}
			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );

			$attach_data = wp_generate_attachment_metadata( $attach_id, $fullfilename );
			wp_update_attachment_metadata( $attach_id,  $attach_data );

			return $attach_id;
		} 
		catch (Exception $e) {
			nu_debug::var_dump( $e );
		}
	}

	private static function _fetch_image( $url )
	{
		if ( function_exists( "curl_init" ) ) {
			$img = self::_curl_fetch_image( $url );
		} elseif ( ini_get( "allow_url_fopen" ) ) {
			$img = self::_fopen_fetch_image( $url );
		}

		return $img;
	}

	private static function _curl_fetch_image( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$image = curl_exec( $ch );
		curl_close( $ch );
		return $image;
	}

	private static function _fopen_fetch_image( $url ) {
		$image = file_get_contents( $url, false, $context );
		return $image;
	}
}

/**
 Template Tags
 */

function nu_get_img_src( $id=null, $size='thumbnail' )
{
	return nu_media_utils::get_img_src( $id, $size );
}


/**
 * Retrieves Images
 */
function nu_get_images( $args = '' )
{
	$defaults = array(
		'posts_per_page' 	=> '-1',
		'orderby' 			=> 'date',			
		'order' 			=> 'DESC',
		'offset' 			=> '0',
		'post_type'			=> 'attachment',
		'post_status'		=> 'inherit',
		'size' 				=> 'medium',
		'nopaging'			=> false
	);

	$args = wp_parse_args( $args, $defaults );

	// run the query. it may be necessary to call wp_reset_query() because of this
	unset( $GLOBALS['wp_query'] );
	$GLOBALS['wp_query'] = $query = new WP_Query( $args );

	// grab the thumbnails and attach to the $posts object
	$posts 	= $query->posts;
	
	// prepare the $size
	if( is_string( $args['size']) ) {
		$size = explode( ',', $args['size'] );
	}
	else {
		$size = (array) $args['size'];
	}
	
	foreach( $posts as $post ) {
		$post->thumbs = nu_media_utils::get_img_src( $post->ID, $size );
	}

	// nu_debug( 'Get Images', $query );

	return $posts;
}


/**
 * I asked for this function to be included in the Media Tags Plugin.
 * Until it is included, this will be necessary - Andre
 *
 * It may be necessary to call wp_reset_query() after this
 */
function nu_get_posts_by_media_tags( $args = '' )
{
	if( !class_exists( 'MediaTags' ) ) {
		echo 'No media returned because the Media Tags plugin is not installed';
		return array();
	}

	$defaults = array(
		'media_tags' => '', 
		'media_types' => null,
		'count' => '-1',
		'orderby' => 'date',			
		'order' => 'DESC',
		'offset' => '0',
		'post_type'	=> 'attachment',
		'search_by' => 'slug',
		'size' => 'medium',
		'operator' => 'IN',
		'nopaging'	=> false
	);
	
	$temp = wp_parse_args( $args, $defaults );
	
	$args = array(
		'order' 			=> $temp['order'],
		'orderby'			=> $temp['orderby'],
		'offset' 			=> $temp['offset'],
		'posts_per_page' 	=> $temp['count'],
		'post_type'			=> $temp['post_type'],
		'nopaging'			=> $temp['nopaging'],
		'post_status'		=> 'publish',
		'size'				=> $temp['size'],
		'tax_query' => array(
			array( 
				'taxonomy' 	=> MEDIA_TAGS_TAXONOMY,
				'field'		=> $temp['search_by'],
				'terms' 	=> explode( ',', $temp['media_tags'] ),
				'operator'	=> $temp['operator']
			)
		)
	);

	// run the query. it may be necessary to call wp_reset_query() because of this
	unset( $GLOBALS['wp_query'] );
	$GLOBALS['wp_query'] = $query = new WP_Query( $args );

	// grab the thumbnails and attach to the $posts object
	$posts 	= $query->posts;
	
	// prepare the $size
	if( is_string( $args['size']) ) {
		$size = explode( ',', $args['size'] );
	}
	else {
		$size = (array) $args['size'];
	}
	
	foreach( $posts as $post ) {
		$post->thumbs = nu_media_utils::get_img_src( $post->ID, $size );
	}

	// nu_debug( 'Get Posts By Media Tags', $posts );

	return $posts;
}