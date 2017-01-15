<?php
/**
 * NOO Meta Boxes Package
 *
 * Setup NOO Meta Boxes for Page
 * This file add Meta Boxes to WP Page edit page.
 *
 * @package    NOO Framework
 * @subpackage NOO Meta Boxes
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if ( NOO_WOOCOMMERCE_EXIST ) :
	if (!function_exists('noo_product_meta_boxes')):
		function noo_product_meta_boxes() {
			// Declare helper object
			$prefix = '_noo_woo_product';
			$helper = new NOO_Meta_Boxes_Helper($prefix, array(
				'page' => 'product'
			));

			// Page Settings
			$meta_box = array(
				'id' => "{$prefix}_meta_box_page",
				'title' => __('Page Settings: Single Product', 'noo'),
				'description' => __('Choose various setting for your Page.', 'noo'),
				'fields' => array(
					array(
						'label' => __('Body Custom CSS Class', 'noo'),
						'id' => "_noo_body_css",
						'type' => 'text',
					),
					array(
						'type' => 'divider',
					)
				)
			);
			
			$helper->add_meta_box($meta_box);
		}
		
		add_action('add_meta_boxes', 'noo_product_meta_boxes');
	endif;
endif;
