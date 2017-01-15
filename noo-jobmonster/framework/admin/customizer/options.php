<?php
/**
 * NOO Customizer Package.
 *
 * Register Options
 * This file register options used in NOO-Customizer
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */
// =============================================================================


// 0. Remove Unused WP Customizer Sections
if ( ! function_exists( 'noo_customizer_remove_wp_native_sections' ) ) :
	function noo_customizer_remove_wp_native_sections( $wp_customize ) {
		$wp_customize->remove_section( 'title_tagline' );
		$wp_customize->remove_section( 'colors' );
		// $wp_customize->remove_section( 'background_image' );
		$wp_customize->remove_section( 'nav' );
		// $wp_customize->remove_section( 'static_front_page' );
		// $wp_customize->remove_panel( 'nav_menus' );
		// $wp_customize->remove_panel( 'widgets' );
	}

add_action( 'customize_register', 'noo_customizer_remove_wp_native_sections' );
endif;


//
// Register NOO Customizer Sections and Options
//

// 1. Site Enhancement options.
if ( ! function_exists( 'noo_customizer_register_options_general' ) ) :
	function noo_customizer_register_options_general( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Site Enhancement
		$helper->add_section(
			'noo_customizer_section_site_enhancement',
			__( 'Site Enhancement', 'noo' ),
			__( 'Enable/Disable some features for your site.', 'noo' )
		);

		// Control: Favicon
		$helper->add_control(
			'noo_custom_favicon',
			'noo_image',
			__( 'Custom Favicon', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Back to Top
		$helper->add_control(
			'noo_back_to_top',
			'noo_switch',
			__( 'Back To Top Button', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Enable MailChimp Subscribe
		$helper->add_control(
			'noo_mailchimp',
			'noo_switch',
			__( 'Enable MailChimp Subscribe', 'noo' ),
			1,
			array( 'json' => array( 'on_child_options' => 'noo_mailchimp_api_key' ) ),
			array( 'transport' => 'postMessage' )
		);

		// Control: MailChimp Settings
		$helper->add_control(
			'noo_mailchimp_api_key',
			'mailchimp',
			__( 'MailChimp Settings', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

// 		// Control: Smooth Scrolling
// 		$helper->add_control(
// 			'noo_smooth_scrolling',
// 			'noo_switch',
// 			__( 'Smooth Scrolling', 'noo' ),
// 			0,
// 			array(),
// 			array( 'transport' => 'postMessage' )
// 		);
	}
add_action( 'customize_register', 'noo_customizer_register_options_general' );
endif;

// 2. Design and Layout options.
if ( ! function_exists( 'noo_customizer_register_options_layout' ) ) :
	function noo_customizer_register_options_layout( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Layout
		$helper->add_section(
			'noo_customizer_section_layout',
			__( 'Design and Layout', 'noo' ),
			__( 'Set Style and Layout for your site. Boxed Layout will come with additional setting options for background color and image.', 'noo' )
		);

		// Control: Site Layout
		$helper->add_control(
			'noo_site_layout',
			'noo_radio',
			__( 'Site Layout', 'noo' ),
			'fullwidth',
			array(
				'choices' => array( 'fullwidth' => __( 'Fullwidth', 'noo' ), 'boxed' => __( 'Boxed', 'noo' ) ),
				'json'  => array(
					'child_options' => array(
						'boxed' => 'noo_layout_site_width
									,noo_layout_site_max_width
									,noo_layout_bg_color
                                    ,noo_layout_bg_image_sub_section
                                    ,noo_layout_bg_image
                                    ,noo_layout_bg_repeat
                                    ,noo_layout_bg_align
                                    ,noo_layout_bg_attachment
                                    ,noo_layout_bg_cover'
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		$helper->add_control(
			'noo_enable_parallax',
			'noo_switch',
			__( 'Enable Parallax Background', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Site Width (%)
		$helper->add_control(
			'noo_layout_site_width',
			'ui_slider',
			__( 'Site Width (%)', 'noo' ),
			'90',
			array(
				'json' => array(
					'data_min' => '60',
					'data_max' => '100',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Site Max Width (px)
		$helper->add_control(
			'noo_layout_site_max_width',
			'ui_slider',
			__( 'Site Max Width (px)', 'noo' ),
			'1200',
			array(
				'json' => array(
					'data_min'  => '980',
					'data_max'  => '1600',
					'data_step' => '10',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Background Color
		$helper->add_control(
			'noo_layout_bg_color',
			'color_control',
			__( 'Background Color', 'noo' ),
			'#ffffff',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-section: Background Image
		$helper->add_sub_section(
			'noo_layout_bg_image_sub_section',
			__( 'Background Image', 'noo' ),
			__( 'Upload your background image here, you have various settings for your image:<br/><strong>Repeat Image</strong>: enable repeating your image, you will need it when using patterned background.<br/><strong>Alignment</strong>: Set the position to align your background image.<br/><strong>Attachment</strong>: Make your image scroll with your site or fixed.<br/><strong>Auto resize</strong>: Enable it to ensure your background image always fit the windows.', 'noo' )
		);

		// Control: Background Image
		$helper->add_control(
			'noo_layout_bg_image',
			'noo_image',
			__( 'Background Image', 'noo' ),
			null,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Repeat Image
		$helper->add_control(
			'noo_layout_bg_repeat',
			'radio',
			__( 'Background Repeat', 'noo' ),
			'no-repeat',
			array(
				'choices' => array(
					'repeat' => __( 'Repeat', 'noo' ),
					'no-repeat' => __( 'No Repeat', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Align Image
		$helper->add_control(
			'noo_layout_bg_align',
			'select',
			__( 'BG Image Alignment', 'noo' ),
			'left top',
			array(
				'choices' => array(
					'left top'       => __( 'Left Top', 'noo' ),
					'left center'     => __( 'Left Center', 'noo' ),
					'left bottom'     => __( 'Left Bottom', 'noo' ),
					'center top'     => __( 'Center Top', 'noo' ),
					'center center'     => __( 'Center Center', 'noo' ),
					'center bottom'     => __( 'Center Bottom', 'noo' ),
					'right top'     => __( 'Right Top', 'noo' ),
					'right center'     => __( 'Right Center', 'noo' ),
					'right bottom'     => __( 'Right Bottom', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Enable Scrolling Image
		$helper->add_control(
			'noo_layout_bg_attachment',
			'radio',
			__( 'BG Image Attachment', 'noo' ),
			'fixed',
			array(
				'choices' => array(
					'fixed' => __( 'Fixed Image', 'noo' ),
					'scroll' => __( 'Scroll with Site', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Auto Resize
		$helper->add_control(
			'noo_layout_bg_cover',
			'noo_switch',
			__( 'Auto Resize', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-Section: Links Color
		$helper->add_sub_section(
			'noo_general_sub_section_links_color',
			__( 'Color', 'noo' ),
			__( 'Here you can set the color for links and various elements on your site.', 'noo' )
		);

		// Control: Site Links Color
		$helper->add_control(
			'noo_site_link_color',
			'color_control',
			__( 'Primary Color', 'noo' ),
			noo_default_primary_color(),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// // Control: Site Links Hover Color
		// $helper->add_control(
		// 	'noo_site_link_hover_color',
		// 	'color_control',
		// 	__( 'Links Hover Color', 'noo' ),
		// 	'',
		// 	array(),
		// 	array( 'transport' => 'postMessage' )
		// );
	}
add_action( 'customize_register', 'noo_customizer_register_options_layout' );
endif;

// 3. Typography options.
if ( ! function_exists( 'noo_customizer_register_options_typo' ) ) :
	function noo_customizer_register_options_typo( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Typography
		$helper->add_section(
			'noo_customizer_section_typo',
			__( 'Typography', 'noo' ),
			__( 'Customize your Typography settings. Merito integrated all Google Fonts. See font preview at <a target="_blank" href="http://www.google.com/fonts/">Google Fonts</a>.', 'noo' )
		);

		// Control: Use Custom Fonts
		$helper->add_control(
			'noo_typo_use_custom_fonts',
			'noo_switch',
			__( 'Use Custom Fonts?', 'noo' ),
			0,
			array( 'json' => array( 
				'on_child_options'  => 'noo_typo_headings_font,noo_typo_body_font' 
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Use Custom Font Color
		$helper->add_control(
			'noo_typo_use_custom_fonts_color',
			'noo_switch',
			__( 'Custom Font Color?', 'noo' ),
			0,
			array( 'json' => array(
				'on_child_options'  => 'noo_typo_headings_font_color,noo_typo_body_font_color'
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Sub-Section: Headings
		$helper->add_sub_section(
			'noo_typo_sub_section_headings',
			__( 'Headings', 'noo' )
		);

		// Control: Headings font
		$helper->add_control(
			'noo_typo_headings_font',
			'google_fonts',
			__( 'Headings Font', 'noo' ),
			noo_default_headings_font_family(),
			array(
				'weight' => '700'
				),
			array( 'transport' => 'postMessage' )
		);

		// Control: Headings Font Color
		$helper->add_control(
			'noo_typo_headings_font_color',
			'color_control',
			__( 'Font Color', 'noo' ),
			noo_default_headings_color(),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Headings Font Uppercase
		$helper->add_control(
			'noo_typo_headings_uppercase',
			'checkbox',
			__( 'Transform to Uppercase', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-Section: Body
		$helper->add_sub_section(
			'noo_typo_sub_section_body',
			__( 'Body', 'noo' )
		);

		// Control: Body font
		$helper->add_control(
			'noo_typo_body_font',
			'google_fonts',
			__( 'Body Font', 'noo' ),
			noo_default_font_family(),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Body Font Size
		$helper->add_control(
			'noo_typo_body_font_size',
			'font_size',
			__( 'Font Size (px)', 'noo' ),
			noo_default_font_size(),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Body Font Color
		$helper->add_control(
			'noo_typo_body_font_color',
			'color_control',
			__( 'Font Color', 'noo' ),
			noo_default_text_color(),
			array(),
			array( 'transport' => 'postMessage' )
		);
	}
add_action( 'customize_register', 'noo_customizer_register_options_typo' );
endif;

// // 4. Color options.
// if ( ! function_exists( 'noo_customizer_register_options_color' ) ) :
// 	function noo_customizer_register_options_color( $wp_customize ) {

// 		// declare helper object.
// 		$helper = new NOO_Customizer_Helper( $wp_customize );

// 		// Section: Color
// 		$helper->add_section(
// 			'noo_customizer_section_color',
// 			__( 'Color', 'noo' )
// 		);
// 	}
// add_action( 'customize_register', 'noo_customizer_register_options_color' );
// endif;

// // 5. Buttons options.
// if ( ! function_exists( 'noo_customizer_register_options_buttons' ) ) :
// 	function noo_customizer_register_options_buttons( $wp_customize ) {

// 		// declare helper object.
// 		$helper = new NOO_Customizer_Helper( $wp_customize );

// 		// Section: Buttons
// 		$helper->add_section(
// 			'noo_customizer_section_buttons',
// 			__( 'Buttons', 'noo' )
// 		);
// 	}
// add_action( 'customize_register', 'noo_customizer_register_options_buttons' );
// endif;

// 6. Header options.
if ( ! function_exists( 'noo_customizer_register_options_header' ) ) :
	function noo_customizer_register_options_header( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Header
		$helper->add_section(
			'noo_customizer_section_header',
			__( 'Header', 'noo' ),
			__( 'Customize settings for your Header, including Navigation Bar (Logo and Navigation) and an optional Top Bar.', 'noo' ),
			true
		);

		// Sub-section: General Options
		$helper->add_sub_section(
			'noo_header_sub_section_general',
			__( 'General Options', 'noo' ),
			''
		);

		// Sub-Section: Navigation Bar
		$helper->add_sub_section(
			'noo_header_sub_section_nav',
			__( 'Navigation Bar', 'noo' ),
			__( 'Adjust settings for Navigation Bar. You also can customize some settings for the Toggle Button on Mobile in this section.', 'noo' )
		);

		// Control: NavBar Position
		$helper->add_control(
			'noo_header_nav_position',
			'noo_radio',
			__( 'NavBar Position', 'noo' ),
			'fixed_top', 
			array(
				'choices' => array(
					'static_top'       => __( 'Static Top', 'noo' ),
					'fixed_top'     => __( 'Fixed Top', 'noo' ),
					// 'fixed_left'     => __( 'Fixed Left', 'noo' ),
					// 'fixed_right'     => __( 'Fixed Right', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Show Post Button
		$helper->add_control(
			'noo_header_nav_post_btn',
			'noo_switch',
			__( 'Show Post Job/Resume Button', 'noo' ),
			1,
			array(
				'json' => array( 
					'on_child_options'  => 'noo_header_nav_post_btn_type' 
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Post Button
		$helper->add_control(
			'noo_header_nav_post_btn_type',
			'noo_radio',
			__( 'Use Button for', 'noo' ),
			'post_job', 
			array(
				'choices' => array(
					'post_job'       => __( 'Post Job', 'noo' ),
					'post_resume'     => __( 'Post Resume', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Show User Menu
		$helper->add_control(
			'noo_header_nav_user_menu',
			'noo_switch',
			__( 'Show User Menu', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// if( NOO_WOOCOMMERCE_EXIST ) {
		// 	// Control: Show Cart Icon
		// 	$helper->add_control(
		// 		'noo_header_nav_icon_cart',
		// 		'checkbox',
		// 		__( 'Show Shopping Cart', 'noo' ),
		// 		1,
		// 		array(),
		// 		array( 'transport' => 'postMessage' )
		// 	);
		// }

		// Control: Divider 2
		$helper->add_control( 'noo_header_nav_divider_2', 'divider', '' );

		// Control: Custom NavBar Font
		$helper->add_control(
			'noo_header_custom_nav_font',
			'noo_switch',
			__( 'Use Custom NavBar Font and Color?', 'noo' ),
			0,
			array( 'json' => array( 
				'on_child_options'  => 'noo_header_nav_font,noo_header_nav_link_color,noo_header_nav_link_hover_color' 
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: NavBar font
		$helper->add_control(
			'noo_header_nav_font',
			'google_fonts',
			__( 'NavBar Font', 'noo' ),
			noo_default_font_family(),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: NavBar Font Size
		$helper->add_control(
			'noo_header_nav_font_size',
			'ui_slider',
			__( 'Font Size (px)', 'noo' ),
			'14',
			array(
				'json' => array(
					'data_min' => '9',
					'data_max' => '30',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: NavBar Link Color
		$helper->add_control(
			'noo_header_nav_link_color',
			'color_control',
			__( 'Link Color', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: NavBar Link Hover Color
		$helper->add_control(
			'noo_header_nav_link_hover_color',
			'color_control',
			__( 'Link Hover Color', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: NavBar Font Uppercase
		$helper->add_control(
			'noo_header_nav_uppercase',
			'checkbox',
			__( 'Transform to Uppercase', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-Section: Logo
		$helper->add_sub_section(
			'noo_header_sub_section_logo',
			__( 'Logo', 'noo' ),
			__( 'All the settings for Logo go here. If you do not use Image for Logo, plain text will be used.', 'noo' )
		);

		// Control: Use Image for Logo
		$helper->add_control(
			'noo_header_use_image_logo',
			'noo_switch',
			__( 'Use Image for Logo?', 'noo' ),
			0,
			array(
				'json' => array(
					'on_child_options'   => 'noo_header_logo_image
                                        ,noo_header_logo_retina_image
                                        ,noo_header_logo_image_height',
					'off_child_options'  => 'blogname
										,noo_header_logo_font
                                        ,noo_header_logo_font_size
                                        ,noo_header_logo_font_color
                                        ,noo_header_logo_uppercase'
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Blog Name
		$helper->add_control(
			'blogname',
			'text',
			__( 'Blog Name', 'noo' ),
			get_bloginfo( 'name' ),
			array(),
			array( 'transport' => 'postMessage', 'type' => 'option' )
		);

		// Control: Logo font
		$helper->add_control(
			'noo_header_logo_font',
			'google_fonts',
			__( 'Logo Font', 'noo' ),
			noo_default_logo_font_family(),
			array(
				'weight' => '700'
				),
			array( 'transport' => 'postMessage' )
		);

		// Control: Logo Font Size
		$helper->add_control(
			'noo_header_logo_font_size',
			'ui_slider',
			__( 'Font Size (px)', 'noo' ),
			'30',
			array(
				'json' => array(
					'data_min' => '15',
					'data_max' => '80',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Logo Font Color
		$helper->add_control(
			'noo_header_logo_font_color',
			'color_control',
			__( 'Font Color', 'noo' ),
			noo_default_logo_color(),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Logo Font Uppercase
		$helper->add_control(
			'noo_header_logo_uppercase',
			'checkbox',
			__( 'Transform to Uppercase', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Logo Image
		$helper->add_control(
			'noo_header_logo_image',
			'noo_image',
			__( 'Upload Your Logo', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Logo Mobile Image
		$helper->add_control(
			'noo_header_logo_mobile_image',
			'noo_image',
			__( 'Mobile Logo', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Logo Image Height
		$helper->add_control(
			'noo_header_logo_image_height',
			'ui_slider',
			__( 'Image Height (px)', 'noo' ),
			'50',
			array(
				'json' => array(
					'data_min' => '15',
					'data_max' => '120',
					'data_step' => '5'
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Sub-Section: NavBar Alignment
		$helper->add_sub_section(
			'noo_header_sub_section_alignment',
			__( 'NavBar Alignment', 'noo' ),
			''
		);

		// Control: NavBar Height (px)
		$helper->add_control(
			'noo_header_nav_height',
			'ui_slider',
			__( 'NavBar Height (px)', 'noo' ),
			'70',
			array(
				'json' => array(
					'data_min' => '20',
					'data_max' => '150',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: NavBar Link Spacing (px)
		$helper->add_control(
			'noo_header_nav_link_spacing',
			'ui_slider',
			__( 'NavBar Link Spacing (px)', 'noo' ),
			'20',
			array(
				'json' => array(
					'data_min' => '5',
					'data_max' => '30',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Mobile Icon Size (px)
		$helper->add_control(
			'noo_header_nav_toggle_size',
			'ui_slider',
			__( 'Mobile Icon Size (px)', 'noo' ),
			'25',
			array(
				'json' => array(
					'data_min' => '15',
					'data_max' => '100',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Sub-Section: Top Bar
		$helper->add_sub_section(
			'noo_header_sub_section_top_bar',
			__( 'Top Bar', 'noo' ),
			__( 'Top Bar lays on top of your site, above Navigation Bar. It is suitable for placing contact information and social media link. Enable to control its layout and content.', 'noo' )
		);

		// Control: Header TopBar
		$helper->add_control(
			'noo_header_top_bar',
			'noo_switch',
			__( 'Enable Top Bar', 'noo' ),
			0,
			array(
				'json' => array(
					'on_child_options'  => 'noo_top_bar_social,noo_top_bar_text'
				)
			),
			array( 'transport' => 'postMessage' )
		);


		// Control: Top bar layout
		$helper->add_control(
			'noo_top_bar_layout',
			'select',
			esc_html__('Top Bar Layout', 'noo'),
			'right_social',
			array(
				'choices' => array(
					'right'       => __( 'Socials on the Right', 'noo' ),
					'left'       => __( 'Social on the Left', 'noo' ),
				)
			)
		);

		// Control: Show Top bar text
		$helper->add_control(
			'noo_top_bar_text',
			'text',
			esc_html__('Text', 'noo'),
			'<span class="text-primary">Hotline</span>',
			array(),
			array( 'transport' => 'postMessage' )
		);
		// Control: Show Social List
		$helper->add_control(
			'noo_top_bar_social',
			'checkbox',
			__( 'Show Social List', 'noo' ),
			1,
			array(
				'json' => array(
					'on_child_options'  => 'noo_header_top_facebook_url,noo_header_top_google_plus_url,noo_header_top_twitter_url,noo_header_top_linked_url'
				)
			),
			array( 'transport' => 'postMessage' )
		);

		$helper->add_control(
			'noo_header_top_facebook_url',
			'text',
			esc_html__('Facebook Url', 'noo'),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		$helper->add_control(
			'noo_header_top_google_plus_url',
			'text',
			esc_html__('Google Plus Url', 'noo'),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		$helper->add_control(
			'noo_header_top_twitter_url',
			'text',
			esc_html__('Twitter Url', 'noo'),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		$helper->add_control(
			'noo_header_top_linked_url',
			'text',
			esc_html__('Linked Url', 'noo'),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);
	}
add_action( 'customize_register', 'noo_customizer_register_options_header' );
endif;

// 7. Footer options.
if ( ! function_exists( 'noo_customizer_register_options_footer' ) ) :
	function noo_customizer_register_options_footer( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Footer
		$helper->add_section(
			'noo_customizer_section_footer',
			__( 'Footer', 'noo' )
		);

		// Control: Footer Columns (Widgetized)
		$helper->add_control(
			'noo_footer_widgets',
			'select',
			__( 'Footer Columns (Widgetized)', 'noo' ),
			'3',
			array(
				'choices' => array(
					0       => __( 'None (No Footer Main Content)', 'noo' ),
					1     => __( 'One', 'noo' ),
					2     => __( 'Two', 'noo' ),
					3     => __( 'Three', 'noo' ),
					4     => __( 'Four', 'noo' )
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Show Footer Menu
// 		$helper->add_control(
// 			'noo_bottom_bar_menu',
// 			'checkbox',
// 			__( 'Show Footer Menu', 'noo' ),
// 			0,
// 			array(),
// 			array( 'transport' => 'postMessage' )
// 		);

		// Control: Show Footer Social Icons
// 		$helper->add_control(
// 			'noo_bottom_bar_social',
// 			'checkbox',
// 			__( 'Show Footer Social Icons', 'noo' ),
// 			1,
// 			array(),
// 			array( 'transport' => 'postMessage' )
// 		);

		// Control: Bottom Bar Content
		$helper->add_control(
			'noo_bottom_bar_content',
			'textarea',
			__( 'Bottom Bar Content (HTML)', 'noo' ),
			'&copy; 2016 JobMonster. Designed with <i class="fa fa-heart text-primary"></i> by NooTheme',
			array(),
			array( 'transport' => 'postMessage' )
		);

	}
add_action( 'customize_register', 'noo_customizer_register_options_footer' );
endif;

// 8. WP Sidebar options.
if ( ! function_exists( 'noo_customizer_register_options_sidebar' ) ) :
	function noo_customizer_register_options_sidebar( $wp_customize ) {

		global $wp_version;
		if ( $wp_version >= 4.0 ) {
			// declare helper object.
			$helper = new NOO_Customizer_Helper( $wp_customize );

			// Change the sidebar panel priority
			$widget_panel = $wp_customize->get_panel('widgets');
			if(!empty($widget_panel)) {
				$widget_panel->priority = $helper->get_new_section_priority();
			}
		}
	}
add_action( 'customize_register', 'noo_customizer_register_options_sidebar' );
endif;

// 9. Blog options.
if ( ! function_exists( 'noo_customizer_register_options_blog' ) ) :
	function noo_customizer_register_options_blog( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Blog
		$helper->add_section(
			'noo_customizer_section_blog',
			__( 'Blog', 'noo' ),
			__( 'In this section you have settings for your Blog page, Archive page and Single Post page.', 'noo' ),
			true
		);

		// Sub-section: Blog Page (Index Page)
		$helper->add_sub_section(
			'noo_blog_sub_section_blog_page',
			__( 'Post List', 'noo' ),
			__( 'Choose Layout settings for your Post List', 'noo' )
		);

		// Control: Blog Layout
		$helper->add_control(
			'noo_blog_layout',
			'noo_radio',
			__( 'Blog Layout', 'noo' ),
			'sidebar',
			array(
				'choices' => array(
					'fullwidth'   => __( 'Full-Width', 'noo' ),
					'sidebar'   => __( 'With Right Sidebar', 'noo' ),
					'left_sidebar'   => __( 'With Left Sidebar', 'noo' )
				),
				'json' => array(
					'child_options' => array(
						'fullwidth'   => '',
						'sidebar'   => 'noo_blog_sidebar',
						'left_sidebar'   => 'noo_blog_sidebar'
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Blog Sidebar
		$helper->add_control(
			'noo_blog_sidebar',
			'widgets_select',
			__( 'Blog Sidebar', 'noo' ),
			'sidebar-main',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Title
		$helper->add_control(
			'noo_blog_heading_title',
			'text',
			__( 'Blog Heading Title', 'noo' ),
			__('Blog', 'noo'),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Image
		$helper->add_control(
			'noo_blog_heading_image',
			'noo_image',
			__( 'Blog Heading Background Image', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Divider 1
		$helper->add_control( 'noo_blog_divider_1', 'divider', '' );

		// Control: Show Post Meta
		$helper->add_control(
			'noo_blog_show_post_meta',
			'checkbox',
			__( 'Show Post Meta', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// // Control: Show Post Tag
		// $helper->add_control(
		// 	'noo_blog_show_post_tag',
		// 	'checkbox',
		// 	__( 'Show Post Tags', 'noo' ),
		// 	1,
		// 	array(),
		// 	array( 'transport' => 'postMessage' )
		// );

		// Control: Show Readmore link
		$helper->add_control(
			'noo_blog_show_readmore',
			'checkbox',
			__( 'Show Readmore link', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Excerpt Length
		$helper->add_control(
			'noo_blog_excerpt_length',
			'text',
			__( 'Excerpt Length', 'noo' ),
			'60',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-section: Single Post
		$helper->add_sub_section(
			'noo_blog_sub_section_post',
			__( 'Single Post', 'noo' )
		);

		// Control: Post Layout
		$helper->add_control(
			'noo_blog_post_layout',
			'noo_same_as_radio',
			__( 'Post Layout', 'noo' ),
			'same_as_blog',
			array(
				'choices' => array(
					'same_as_blog'   => __( 'Same as Blog Layout', 'noo' ),
					'fullwidth'   => __( 'Full-Width', 'noo' ),
					'sidebar'   => __( 'With Right Sidebar', 'noo' ),
					'left_sidebar'   => __( 'With Left Sidebar', 'noo' ),
				),
				'json' => array(
					'child_options' => array(
						'fullwidth'   => '',
						'sidebar'   => 'noo_blog_post_sidebar',
						'left_sidebar'   => 'noo_blog_post_sidebar',
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Post Sidebar
		$helper->add_control(
			'noo_blog_post_sidebar',
			'widgets_select',
			__( 'Post Sidebar', 'noo' ),
			'sidebar-main',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Divider 1
		$helper->add_control( 'noo_blog_post_divider_1', 'divider', '' );

		// Control: Show Post Meta
		$helper->add_control(
			'noo_blog_post_show_post_meta',
			'checkbox',
			__( 'Show Post Meta', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// // Control: Show Post Tags
		// $helper->add_control(
		// 	'noo_blog_post_show_post_tag',
		// 	'checkbox',
		// 	__( 'Show Post Tags', 'noo' ),
		// 	1,
		// 	array(),
		// 	array( 'transport' => 'postMessage' )
		// );

		// Control: Show Author Bio
		$helper->add_control(
			'noo_blog_post_author_bio',
			'checkbox',
			__( 'Show Author\'s Avatar', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Divider 2
		$helper->add_control( 'noo_blog_post_divider_2', 'divider', '' );

		// Control: Enable Social Sharing
		$helper->add_control(
			'noo_blog_social',
			'noo_switch',
			__( 'Enable Social Sharing', 'noo' ),
			1,
			array(
				'json' => array( 'on_child_options' => 'noo_blog_social_facebook,
		                                                noo_blog_social_twitter,
		                                                noo_blog_social_google,
		                                                noo_blog_social_pinterest,
		                                                noo_blog_social_linkedin'
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Sharing Title
		$helper->add_control(
			'noo_blog_social_title',
			'text',
			__( 'Sharing Title', 'noo' ),
			__( 'Share This Post', 'noo' ),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Facebook Share
		$helper->add_control(
			'noo_blog_social_facebook',
			'checkbox',
			__( 'Facebook Share', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Twitter Share
		$helper->add_control(
			'noo_blog_social_twitter',
			'checkbox',
			__( 'Twitter Share', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Google+ Share
		$helper->add_control(
			'noo_blog_social_google',
			'checkbox',
			__( 'Google+ Share', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Pinterest Share
		$helper->add_control(
			'noo_blog_social_pinterest',
			'checkbox',
			__( 'Pinterest Share', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: LinkedIn Share
		$helper->add_control(
			'noo_blog_social_linkedin',
			'checkbox',
			__( 'LinkedIn Share', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);
	}
add_action( 'customize_register', 'noo_customizer_register_options_blog' );
endif;

// Job & Company options.
if ( ! function_exists( 'noo_customizer_register_options_job' ) ) :
	function noo_customizer_register_options_job( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Job List
		$helper->add_section(
			'noo_customizer_section_job',
			__( 'Job & Company', 'noo' ),
			__( 'In this section you have settings for your Job List page, Archive page and Single Job page.', 'noo' ),
			true
		);

		// Sub-section: Job List
		$helper->add_sub_section(
			'noo_job_sub_section_job_list',
			__( 'Job List', 'noo' ),
			__( 'Choose Layout settings for your Job List', 'noo' )
		);

		// Control: Job List Layout
		$helper->add_control(
			'noo_jobs_layout',
			'noo_radio',
			__( 'Job List Layout', 'noo' ),
			'sidebar',
			array(
				'choices' => array(
					'fullwidth'   => __( 'Full-Width', 'noo' ),
					'sidebar'   => __( 'With Right Sidebar', 'noo' ),
					'left_sidebar'   => __( 'With Left Sidebar', 'noo' )
				),
				'json' => array(
					'child_options' => array(
						'fullwidth'   => '',
						'sidebar'   => 'noo_jobs_sidebar',
						'left_sidebar'   => 'noo_jobs_sidebar'
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Job List Style
		$helper->add_control(
			'noo_jobs_list_style',
			'noo_radio',
			__( 'Job List Style', 'noo' ),
			'',
			array(
				'choices' => array(
					''      => __( 'Style 1', 'noo' ),
					'two' => __( 'Style 2', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Job List Pagination Style
		$helper->add_control(
			'noo_jobs_list_pagination_style',
			'noo_radio',
			__( 'Job List Pagination Style', 'noo' ),
			'',
			array(
				'choices' => array(
					'' => __( 'Page Numbers', 'noo' ),
					'loadmore'      => __( 'Load More Button', 'noo' ),
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Job List Sidebar
		$helper->add_control(
			'noo_jobs_sidebar',
			'widgets_select',
			__( 'Job List Sidebar', 'noo' ),
			'sidebar-job',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Title
		$helper->add_control(
			'noo_job_heading_title',
			'text',
			__( 'Job Heading Title', 'noo' ),
			__('Job', 'noo'),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Image
		$helper->add_control(
			'noo_job_heading_image',
			'noo_image',
			__( 'Job Heading Background Image', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Enable Featured Jobs
		$helper->add_control(
			'noo_jobs_featured',
			'noo_switch',
			__( 'Show Featured Jobs', 'noo' ),
			0,
			array(
				'json' => array( 'on_child_options' => 'noo_jobs_featured_num, noo_jobs_view_more' )
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Featured Jobs Num
		$helper->add_control(
			'noo_jobs_featured_num',
			'ui_slider',
			__( 'Number of Featured Jobs', 'noo' ),
			'4',
			array(
				'json' => array(
					'data_min' => '2',
					'data_max' => '15',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Enable Order By Featured Jobs
		$helper->add_control(
			'noo_jobs_orderby_featured',
			'noo_switch',
			__( 'Order By Featured Jobs', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Divider 1
		$helper->add_control( 'noo_jobs_divider_1', 'divider', '' );

		// Control: Show Company Logo
		$helper->add_control(
			'noo_jobs_show_company_logo',
			'checkbox',
			__( 'Show Company Logo', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Show Company Name
		$helper->add_control(
			'noo_jobs_show_company_name',
			'checkbox',
			__( 'Show Company Name', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// // Control: Show View more Button
		// $helper->add_control(
		// 	'noo_jobs_view_more',
		// 	'noo_switch',
		// 	__( 'Show View more Button', 'noo' ),
		// 	1,
		// 	array(),
		// 	array( 'transport' => 'postMessage' )
		// );

		// custom field
		$custom_fields = jm_get_job_custom_fields();
		$cf_choices = array( 
			'job_type' => __('Job Type', 'noo'),
			'job_location' => __('Job Location', 'noo'),
			'job_date' => __('Publish Date', 'noo'),
			'_closing' => __('Closing Date', 'noo'),
			'job_category' => __('Job Category', 'noo'),
		);
		foreach( $custom_fields as $field ) {
			if( !isset( $cf_choices[ $field['name'] ] ) && $field['name'] != 'job_tag' ) {
				$cf_choices[ $field['name'] ] = $field['label'];
			}
		}
		$helper->add_control(
			'noo_jobs_list_fields',
			'checkbox_multiple',
			__( 'Show Fields: ', 'noo' ),
			'job_type,job_location,job_date,_closing',
			array(
					'choices' => $cf_choices
				),
			array( 'transport' => 'postMessage' )
		);

		// Sub-section: Single Job
		$helper->add_sub_section(
			'noo_job_sub_section_single_job',
			__( 'Single Job', 'noo' )
		);

		// Control: Single Job Layout
		$helper->add_control(
			'noo_single_jobs_layout',
			'noo_radio',
			__( 'Single Job Layout', 'noo' ),
			'right_company',
			array(
				'choices' => array(
					'right_company'      => __( 'Company Info on the Right', 'noo' ),
					'left_company' => __( 'Company Info on the Left', 'noo' ),
					'sidebar'      => __( 'Right Sidebar', 'noo' ),
					'left_sidebar' => __( 'Left Sidebar', 'noo' ),
					'fullwidth'    => __( 'Full-Width', 'noo' )
				),
				'json' => array(
					'child_options' => array(
						'sidebar'      => 'noo_single_jobs_sidebar,noo_company_info_in_jobs',
						'left_sidebar' => 'noo_single_jobs_sidebar,noo_company_info_in_jobs',
						'fullwidth'    => 'noo_company_info_in_jobs',
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		$helper->add_control(
			'noo_company_info_in_jobs',
			'noo_switch',
			__( 'Show Company Information Below', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Single Job Sidebar
		$helper->add_control(
			'noo_single_jobs_sidebar',
			'widgets_select',
			__( 'Single Job Sidebar', 'noo' ),
			'sidebar',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// // Control: Single Job Layout
		// $helper->add_control(
		// 	'noo_single_jobs_layout',
		// 	'noo_radio',
		// 	__( 'Single Job Layout', 'noo' ),
		// 	'single_sidebar',
		// 	array(
		// 		'choices' => array(
		// 			'fullwidth'    => __( 'Full-Width', 'noo' ),
		// 			'sidebar'      => __( 'With Right Sidebar', 'noo' ),
		// 			'left_sidebar' => __( 'With Left Sidebar', 'noo' )
		// 		),
		// 		'json' => array(
		// 			'child_options' => array(
		// 				'fullwidth'    => '',
		// 				'sidebar'      => 'noo_single_jobs_sidebar, noo_display_box_company',
		// 				'left_sidebar' => 'noo_single_jobs_sidebar, noo_display_box_company',
		// 			)
		// 		)
		// 	),
		// 	array( 'transport' => 'postMessage' )
		// );

		// Control: Enable Job Comment
		$helper->add_control(
			'noo_job_comment',
			'noo_switch',
			__( 'Enable Job Comment', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Enable Related Jobs
		$helper->add_control(
			'noo_job_related',
			'noo_switch',
			__( 'Enable Related Jobs', 'noo' ),
			1,
			array(
				'json' => array( 'on_child_options' => 'noo_job_related_num, noo_job_related_view_more')
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Number of Related Jobs
		$helper->add_control(
			'noo_job_related_num',
			'ui_slider',
			__( 'Number of Related Jobs', 'noo' ),
			'4',
			array(
				'json' => array(
					'data_min' => '2',
					'data_max' => '10',
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Enable Social Sharing
		$helper->add_control(
			'noo_job_social',
			'noo_switch',
			__( 'Enable Social Sharing', 'noo' ),
			1,
			array(
				'json' => array( 'on_child_options' => 'noo_job_social_facebook,
		                                                noo_job_social_twitter,
		                                                noo_job_social_google,
		                                                noo_job_social_pinterest,
		                                                noo_job_social_linkedin,
		                                                noo_job_social_email'
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Facebook Share
		$helper->add_control(
			'noo_job_social_facebook',
			'checkbox',
			__( 'Facebook Share', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Twitter Share
		$helper->add_control(
			'noo_job_social_twitter',
			'checkbox',
			__( 'Twitter Share', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Google+ Share
		$helper->add_control(
			'noo_job_social_google',
			'checkbox',
			__( 'Google+ Share', 'noo' ),
			1,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Pinterest Share
		$helper->add_control(
			'noo_job_social_pinterest',
			'checkbox',
			__( 'Pinterest Share', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: LinkedIn Share
		$helper->add_control(
			'noo_job_social_linkedin',
			'checkbox',
			__( 'LinkedIn Share', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Email Share
		$helper->add_control(
			'noo_job_social_email',
			'checkbox',
			__( 'Email Share', 'noo' ),
			0,
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-section: Companies List
		$helper->add_sub_section(
			'noo_job_sub_section_companies_list',
			__( 'Companies List', 'noo' ),
			__( 'Choose Layout settings for your Companies List', 'noo' )
		);

		// Control: Companies List Layout
		$helper->add_control(
			'noo_companies_layout',
			'noo_radio',
			__( 'Companies List Layout', 'noo' ),
			'fullwidth',
			array(
				'choices' => array(
					'fullwidth'   => __( 'Full-Width', 'noo' ),
					'sidebar'   => __( 'With Right Sidebar', 'noo' ),
					'left_sidebar'   => __( 'With Left Sidebar', 'noo' )
				),
				'json' => array(
					'child_options' => array(
						'fullwidth'   => '',
						'sidebar'   => 'noo_companies_sidebar',
						'left_sidebar'   => 'noo_companies_sidebar'
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Companies List Style
		$helper->add_control(
			'noo_companies_style',
			'noo_radio',
			__( 'Companies List Style', 'noo' ),
			'',
			array(
				'choices' => array(
					''      => __( 'Alphabet', 'noo' ),
					'style2' => __( 'Grid', 'noo' ),
				),
				'json' => array(
					'child_options' => array(
						'style2'   => 'noo_companies_style_count'
					)
				)

			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Companies List Style 2 - Count
		$helper->add_control(
			'noo_companies_style_count',
			'text',
			__( 'Companies Per Page', 'noo' ),
			__('12', 'noo'),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Companies List Sidebar
		$helper->add_control(
			'noo_companies_sidebar',
			'widgets_select',
			__( 'Companies List Sidebar', 'noo' ),
			'sidebar-companies',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Title
		$helper->add_control(
			'noo_companies_heading_title',
			'text',
			__( 'Companies Heading Title', 'noo' ),
			__('Companies', 'noo'),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Image
		$helper->add_control(
			'noo_companies_heading_image',
			'noo_image',
			__( 'Companies Heading Background Image', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Sub-section: Job List
		$helper->add_sub_section(
			'noo_job_sub_section_company_single',
			__( 'Single Company', 'noo' ),
			__( 'Choose Layout settings for your Company', 'noo' )
		);
		// Control: Single Company Layout
		$helper->add_control(
			'noo_single_company_layout',
			'noo_radio',
			__( 'Single Company Layout', 'noo' ),
			'',
			array(
				'choices' => array(
					''      => __( 'Sidebar Information', 'noo' ),
					'two' => __( 'Full Information', 'noo' ),
				),
				'json' => array(
					'child_options' => array(
						''   => '',
						'two'   => 'noo_single_company_contact_form'
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Show Contact Company Form
		$helper->add_control(
			'noo_single_company_contact_form',
			'noo_switch',
			__( 'Show Contact Company Form', 'noo' ),
			0,
			array(
				'json' => array( 'on_child_options' => 'noo_single_captcha_contact_form' )
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Show CaptCha Contact Form
		$helper->add_control(
			'noo_single_captcha_contact_form',
			'noo_switch',
			__( 'Use CaptCha', 'noo' ),
			0,
			array(
				'json' => array( 'on_child_options' => '' )
			),
			array( 'transport' => 'postMessage' )
		);

	}
add_action( 'customize_register', 'noo_customizer_register_options_job' );
endif;

// Resume options.
if ( ! function_exists( 'noo_customizer_register_options_resume' ) ) :
	function noo_customizer_register_options_resume( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Resume List
		$helper->add_section(
			'noo_customizer_section_resume',
			__( 'Resume', 'noo' ),
			__( 'In this section you have settings for your Resume List page.', 'noo' )
		);

		// // Sub-section: Resume List
		// $helper->add_sub_section(
		// 	'noo_resume_sub_section_resume_list',
		// 	__( 'Resume List', 'noo' ),
		// 	__( 'Choose Layout settings for your Resume List', 'noo' )
		// );

		// Control: Resume List Layout
		$helper->add_control(
			'noo_resumes_layout',
			'noo_radio',
			__( 'Resume List Layout', 'noo' ),
			'sidebar',
			array(
				'choices' => array(
					'fullwidth'   => __( 'Full-Width', 'noo' ),
					'sidebar'   => __( 'With Right Sidebar', 'noo' ),
					'left_sidebar'   => __( 'With Left Sidebar', 'noo' )
				),
				'json' => array(
					'child_options' => array(
						'fullwidth'   => '',
						'sidebar'   => 'noo_resumes_sidebar',
						'left_sidebar'   => 'noo_resumes_sidebar'
					)
				)
			),
			array( 'transport' => 'postMessage' )
		);

		// Control: Resume List Sidebar
		$helper->add_control(
			'noo_resumes_sidebar',
			'widgets_select',
			__( 'Resume List Sidebar', 'noo' ),
			'sidebar-resume',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Title
		$helper->add_control(
			'noo_resume_heading_title',
			'text',
			__( 'Resume Heading Title', 'noo' ),
			__('Resume Listing', 'noo'),
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Heading Image
		$helper->add_control(
			'noo_resume_heading_image',
			'noo_image',
			__( 'Resume Heading Background Image', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Resume custom field
		$resume_cf = jm_get_resume_custom_fields();
		$resume_cf_choices = array( 'title' => __( 'Resume Title', 'noo' ) );
		foreach( $resume_cf as $field ) {
			$resume_cf_choices[ $field['name'] ] = $field['label'];
		}
		$helper->add_control(
			'noo_resume_list_fields',
			'checkbox_multiple',
			__( 'Show Fields: ', 'noo' ),
			'title,_job_location,_job_category',
			array(
					'choices' => $resume_cf_choices
				),
			array( 'transport' => 'postMessage' )
		);
	}
add_action( 'customize_register', 'noo_customizer_register_options_resume' );
endif;

// 10. Portfolio options.

// 11. Page options.

//12. WooCommerce options.
if( is_plugin_active( 'woocommerce/woocommerce.php' ) ) :
	if ( ! function_exists( 'noo_customizer_register_options_woocommerce' ) ) :
		function noo_customizer_register_options_woocommerce( $wp_customize ) {

			// declare helper object.
			$helper = new NOO_Customizer_Helper( $wp_customize );

			// Section: Revolution Slider
			$helper->add_section(
				'noo_customizer_section_shop',
				__( 'WooCommerce', 'noo' ),
				'',
				true
			);

			// Sub-section: Shop Page
			$helper->add_sub_section(
				'noo_woocommerce_sub_section_shop_page',
				__( 'Shop Page', 'noo' ),
				__( 'Choose Layout for your Shop Page.', 'noo' )
			);

			// Control: Shop Layout
			$helper->add_control(
				'noo_shop_layout',
				'noo_radio',
				__( 'Shop Layout', 'noo' ),
				'fullwidth',
				array(
					'choices' => array(
						'fullwidth'   => __( 'Full-Width', 'noo' ),
						'sidebar'   => __( 'With Right Sidebar', 'noo' ),
						'left_sidebar'   => __( 'With Left Sidebar', 'noo' )
					),
					'json' => array(
						'child_options' => array(
							'fullwidth'   => '',
							'sidebar'   => 'noo_shop_sidebar',
							'left_sidebar'   => 'noo_shop_sidebar',
						)
					)
				),
				array( 'transport' => 'postMessage' )
			);

			// Control: Shop Sidebar
			$helper->add_control(
				'noo_shop_sidebar',
				'widgets_select',
				__( 'Shop Sidebar', 'noo' ),
				'',
				array(),
				array( 'transport' => 'postMessage' )
			);

			// Control: Number of Product per Page
			$helper->add_control(
				'noo_shop_num',
				'ui_slider',
				__( 'Products Per Page', 'noo' ),
				'12',
				array(
					'json' => array(
						'data_min'  => '4',
						'data_max'  => '50',
						'data_step' => '2'
					)
				),
				array( 'transport' => 'postMessage' )
			);
			// Control: Heading Image
			$helper->add_control(
				'noo_shop_heading_image',
				'noo_image',
				__( 'Shop Heading Background Image', 'noo' ),
				'',
				array(),
				array( 'transport' => 'postMessage' )
			);
			// Sub-section: Single Product
			$helper->add_sub_section(
				'noo_woocommerce_sub_section_product',
				__( 'Single Product', 'noo' )
			);

			// Control: Product Layout
			$helper->add_control(
				'noo_woocommerce_product_layout',
				'noo_same_as_radio',
				__( 'Product Layout', 'noo' ),
				'same_as_shop',
				array(
					'choices' => array(
						'same_as_shop'   => __( 'Same as Shop Layout', 'noo' ),
						'fullwidth'   => __( 'Full-Width', 'noo' ),
						'sidebar'   => __( 'With Right Sidebar', 'noo' ),
						'left_sidebar'   => __( 'With Left Sidebar', 'noo' ),
					),
					'json' => array(
						'child_options' => array(
							'fullwidth'   => '',
							'sidebar'   => 'noo_woocommerce_product_sidebar',
							'left_sidebar'   => 'noo_woocommerce_product_sidebar',
						)
					)
				),
				array( 'transport' => 'postMessage' )
			);

			// Control: Product Sidebar
			$helper->add_control(
				'noo_woocommerce_product_sidebar',
				'widgets_select',
				__( 'Product Sidebar', 'noo' ),
				'',
				array(),
				array( 'transport' => 'postMessage' )
			);

		}
	add_action( 'customize_register', 'noo_customizer_register_options_woocommerce' );
	endif;
endif;

// 13. Custom Code
if ( ! function_exists( 'noo_customizer_register_options_custom_code' ) ) :
	function noo_customizer_register_options_custom_code( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Custom Code
		$helper->add_section(
			'noo_customizer_section_custom_code',
			__( 'Custom Code', 'noo' ),
			__( 'In this section you can add custom JavaScript and CSS to your site.<br/>Your Google analytics tracking code should be added to Custom JavaScript field.', 'noo' )
		);

		// Control: Custom JS (Google Analytics)
		$helper->add_control(
			'noo_custom_javascript',
			'textarea',
			__( 'Custom JavaScript', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);

		// Control: Custom CSS
		$helper->add_control(
			'noo_custom_css',
			'textarea',
			__( 'Custom CSS', 'noo' ),
			'',
			array(),
			array( 'transport' => 'postMessage' )
		);
	}
add_action( 'customize_register', 'noo_customizer_register_options_custom_code' );
endif;

// 16. Import/Export Settings.
if ( ! function_exists( 'noo_customizer_register_options_tools' ) ) :
	function noo_customizer_register_options_tools( $wp_customize ) {

		// declare helper object.
		$helper = new NOO_Customizer_Helper( $wp_customize );

		// Section: Custom Code
		$helper->add_section(
			'noo_customizer_section_tools',
			__( 'Import/Export Settings', 'noo' ),
			__( 'All themes from NooTheme share the same theme setting structure so you can export then import settings from one theme to another conveniently without any problem.', 'noo' )
		);

		// Sub-section: Import Settings
		$helper->add_sub_section(
			'noo_tools_sub_section_import',
			__( 'Import Settings', 'noo' ),
			__( 'Click Upload button then choose a JSON file (.json) from your computer to import settings to this theme.<br/>All the settings will be loaded for preview here and will not be saved until you click button "Save and Publish".', 'noo' )
		);

		// Control: Upload Settings
		$helper->add_control(
			'noo_tools_import',
			'import_settings',
			__( 'Upload', 'noo' )
		);

		// Sub-section: Export Settings
		$helper->add_sub_section(
			'noo_tools_sub_section_export',
			__( 'Export Settings', 'noo' ),
			__( 'Simply click Download button to export all your settings to a JSON file (.json).<br/>You then can use that file to restore theme settings to any theme of NooTheme.', 'noo' )
		);

		// Control: Download Settings
		$helper->add_control(
			'noo_tools_export',
			'export_settings',
			__( 'Download', 'noo' )
		);

	}
add_action( 'customize_register', 'noo_customizer_register_options_tools' );
endif;

