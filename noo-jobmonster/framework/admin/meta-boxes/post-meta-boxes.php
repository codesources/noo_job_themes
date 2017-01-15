<?php
/**
 * NOO Meta Boxes Package
 *
 * Setup NOO Meta Boxes for Post
 * This file add Meta Boxes to WP Post edit page.
 *
 * @package    NOO Framework
 * @subpackage NOO Meta Boxes
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if (!function_exists('noo_post_meta_boxes')):
	function noo_post_meta_boxes() {
		// Declare helper object
		$prefix = '_noo_wp_post';
		$helper = new NOO_Meta_Boxes_Helper($prefix, array(
			'page' => 'post'
		));

		// Post type: Gallery
		$meta_box = array(
			'id' => "{$prefix}_meta_box_gallery",
			'title' => __('Gallery Settings', 'noo'),
			'fields' => array(
				array(
					'id' => "{$prefix}_gallery",
					// 'label' => __( 'Your Gallery', 'noo' ),
					'type' => 'gallery',
				),
				array(
					'type' => 'divider',
				),
				array(
					'id' => "{$prefix}_gallery_preview",
					'label' => __('Preview Content', 'noo'),
					'type' => 'radio',
					'std' => 'featured',
					'options' => array(
						array(
							'label' => __('Featured Image', 'noo'),
							'value' => 'featured',
						),
						array(
							'label' => __('First Image on Gallery', 'noo'),
							'value' => 'first_image',
						),
						array(
							'label' => __('Image Slideshow', 'noo'),
							'value' => 'slideshow',
						),
					)
				)
			)
		);

		$helper->add_meta_box($meta_box);

		// Post type: Video
		$meta_box = array(
			'id' => "{$prefix}_meta_box_video",
			'title' => __('Video Settings', 'noo'),
			'fields' => array(
				array(
					'id' => "{$prefix}_video_embed",
					'label' => __('Embedded Video Code', 'noo'),
					'desc' => __('If you are using videos from online sharing sites (YouTube, Vimeo, etc.) paste the embedded code here. This field will override the above settings.', 'noo'),
					'type' => 'textarea',
					'std' => ''
				),
				array(
					'id' => "{$prefix}_video_ratio",
					'label' => __('Video Aspect Ratio', 'noo'),
					'desc' => __('Choose the aspect ratio for your video.', 'noo'),
					'type' => 'select',
					'std' => '16:9',
					'options' => array(
						array('value'=>'16:9','label'=>'16:9'),
						array('value'=>'5:3','label'=>'5:3'),
						array('value'=>'5:4','label'=>'5:4'),
						array('value'=>'4:3','label'=>'4:3'),
						array('value'=>'3:2','label'=>'3:2')
					)
				),
				array(
					'type' => 'divider',
				),
				array(
					'label' => __('Preview Content', 'noo'),
					'id' => "{$prefix}_video_preview",
					'type' => 'radio',
					'std' => 'both',
					'options' => array(
						array(
							'label' => __('Featured Image', 'noo'),
							'value' => 'featured',
						),
						array(
							'label' => __('Video', 'noo'),
							'value' => 'video',
						),
						array(
							'label' => __('Both (use Featured Image as Thumbnail for Video)', 'noo'),
							'value' => 'both',
						),
					)
				)
			)
		);
		
		$helper->add_meta_box($meta_box);

		// Post type: Audio
		$meta_box = array(
			'id' => "{$prefix}_meta_box_audio",
			'title' => __('Audio Settings', 'noo'),
			'fields' => array(
				// array(
				// 	'id' => "{$prefix}_audio_mp3",
				// 	'label' => __('MP3 File URL', 'noo'),
				// 	'desc' => __('Place the URL to your .mp3 audio file here.', 'noo'),
				// 	'type' => 'text',
				// ),
				// array(
				// 	'id' => "{$prefix}_audio_oga",
				// 	'label' => __('OGA File URL', 'noo'),
				// 	'desc' => __('Place the URL to your .oga audio file here.', 'noo'),
				// 	'type' => 'text',
				// ),
				array(
					'id' => "{$prefix}_audio_embed",
					'label' => __('Embedded Audio Code', 'noo'),
					'desc' => __('If you are using videos from online sharing sites (like Soundcloud) paste the embedded code here. This field will override above settings.', 'noo'),
					'type' => 'textarea',
					'std' => ''
				)
			)
		);

		$helper->add_meta_box($meta_box);

		// Page Settings: Single Post
		$meta_box = array(
			'id' => "{$prefix}_meta_box_single_page",
			'title' => __('Page Settings: Single Post', 'noo'),
			'description' => __('Choose various setting for your Single Post page.', 'noo'),
			'fields' => array(
				array(
					'label' => __('Body Custom CSS Class', 'noo'),
					'id' => "_noo_body_css",
					'type' => 'text',
				),
				array(
					'type' => 'divider'
				),
				array(
					'label' => __('Page Layout', 'noo'),
					'id' => "{$prefix}_global_setting",
					'type' => 'page_layout',
				),
				array(
					'label' => __('Override Global Settings?', 'noo'),
					'id' => "{$prefix}_override_layout",
					'type' => 'checkbox',
					'child-fields' => array(
						'on' => "{$prefix}_layout,{$prefix}_sidebar"
					),
				),
				array(
					'label' => __('Page Layout', 'noo'),
					'id' => "{$prefix}_layout",
					'type' => 'radio',
					'std' => 'sidebar',
					'options' => array(
						'fullwidth' => array(
							'label' => __('Full-Width', 'noo'),
							'value' => 'fullwidth',
						),
						'sidebar' => array(
							'label' => __('With Right Sidebar', 'noo'),
							'value' => 'sidebar',
						),
						'left_sidebar' => array(
							'label' => __('With Left Sidebar', 'noo'),
							'value' => 'left_sidebar',
						),
					),
				),
				array(
					'label' => __('Post Sidebar', 'noo'),
					'id' => "{$prefix}_sidebar",
					'type' => 'sidebars',
					'std' => 'sidebar-main'
				),
			)
		);

		$helper->add_meta_box( $meta_box );
	}
	
endif;

add_action('add_meta_boxes', 'noo_post_meta_boxes');
