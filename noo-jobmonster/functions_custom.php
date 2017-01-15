<?php


/* =============================================================================
 *
 * Function for specific theme, remember to keep all the functions
 * specified for this theme inside this file.
 *
 * ============================================================================*/

// Define theme specific constant
if (!defined('NOO_THEME_NAME'))
{
  define('NOO_THEME_NAME', 'noo-jobmonster');
}

if (!defined('NOO_THEME_VERSION'))
{
  define('NOO_THEME_VERSION', '0.0.1');
}
function noo_relative_time(){

    return human_time_diff(get_comment_time('U'), current_time('timestamp'));
}
function noo_excerpt_read_more( $more ) {
	return '';
}
add_filter( 'excerpt_more', 'noo_excerpt_read_more' );

function noo_content_read_more( $more ) {
	return '';
}

add_filter( 'the_content_more_link', 'noo_content_read_more' );


//// Include specific widgets
// require_once( $widget_path . '/<widgets_name>.php');

