<?php
/**
 * Schema structure for your site.
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if (!function_exists('noo_site_schema')):
	function noo_site_schema() {
		$schema = apply_filters( 'noo_site_schema', array() );
		if( !empty( $schema ) && count( $schema ) > 0 ) {
			foreach ($schema as $key => $value) {
				echo ' ' . $key . '="' . $value . '"';
			}
		}
	}
endif;

if (!function_exists('noo_header_schema')):
	function noo_header_schema() {
		$schema = apply_filters( 'noo_header_schema', array() );
		if( !empty( $schema ) && count( $schema ) > 0 ) {
			foreach ($schema as $key => $value) {
				echo ' ' . $key . '="' . $value . '"';
			}
		}
	}
endif;

if (!function_exists('noo_main_content_schema')):
	function noo_main_content_schema() {
		$schema = apply_filters( 'noo_content_schema', array() );
		if( !empty( $schema ) && count( $schema ) > 0 ) {
			foreach ($schema as $key => $value) {
				echo ' ' . $key . '="' . $value . '"';
			}
		}
	}
endif;

if (!function_exists('noo_page_title_schema')):
	function noo_page_title_schema() {
		$schema = apply_filters( 'noo_page_title_schema', array() );
		if( !empty( $schema ) && count( $schema ) > 0 ) {
			foreach ($schema as $key => $value) {
				echo ' ' . $key . '="' . $value . '"';
			}
		}
	}
endif;
