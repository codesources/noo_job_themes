<?php
/**
 * Register NOO testimonial.
 * This file register Item and Category for NOO testimonial.
 *
 * @package    NOO Framework
 * @subpackage NOO testimonial
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if ( ! function_exists('noo_init_testimonial')) :
    function noo_init_testimonial() {

        // Text for NOO testimonial.
        $testimonial_labels = array(
            'name' => __('Testimonial', 'noo') ,
            'singular_name' => __('Testimonial', 'noo') ,
            'menu_name' => __('Testimonial', 'noo') ,
            'add_new' => __('Add New', 'noo') ,
            'add_new_item' => __('Add New testimonial Item', 'noo') ,
            'edit_item' => __('Edit testimonial Item', 'noo') ,
            'new_item' => __('Add New testimonial Item', 'noo') ,
            'view_item' => __('View testimonial', 'noo') ,
            'search_items' => __('Search testimonial', 'noo') ,
            'not_found' => __('No testimonial items found', 'noo') ,
            'not_found_in_trash' => __('No testimonial items found in trash', 'noo') ,
            'parent_item_colon' => ''
        );

        $admin_icon = NOO_FRAMEWORK_ADMIN_URI . '/assets/images/noo20x20.png';
        if ( floatval( get_bloginfo( 'version' ) ) >= 3.8 ) {
            $admin_icon = 'dashicons-testimonial';
        }

        $testimonial_page = noo_get_option('noo_testimonial_page', '');
        $testimonial_slug = !empty($testimonial_page) ? get_post( $testimonial_page )->post_name : 'testimonials';

        // Options
        $testimonial_args = array(
            'labels' => $testimonial_labels,
            'public' => false,
            // 'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'menu_icon' => $admin_icon,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array(
                'title',
                'editor',
               // 'excerpt',
               // 'thumbnail',
                // 'comments',
                // 'custom-fields',
                'revisions'
            ) ,
            'has_archive' => true,
            'rewrite' => array(
                'slug' => $testimonial_slug,
                'with_front' => false
            )
        );

        register_post_type('testimonial', $testimonial_args);

        // Register a taxonomy for Project Categories.
        $category_labels = array(
            'name' => __('Testimonial Categories', 'noo') ,
            'singular_name' => __('Testimonial Category', 'noo') ,
            'menu_name' => __('Testimonial Categories', 'noo') ,
            'all_items' => __('All Testimonial Categories', 'noo') ,
            'edit_item' => __('Edit Testimonial Category', 'noo') ,
            'view_item' => __('View Testimonial Category', 'noo') ,
            'update_item' => __('Update Testimonial Category', 'noo') ,
            'add_new_item' => __('Add New Testimonial Category', 'noo') ,
            'new_item_name' => __('New Testimonial Category Name', 'noo') ,
            'parent_item' => __('Parent Testimonial Category', 'noo') ,
            'parent_item_colon' => __('Parent Testimonial Category:', 'noo') ,
            'search_items' => __('Search Testimonial Categories', 'noo') ,
            'popular_items' => __('Popular Testimonial Categories', 'noo') ,
            'separate_items_with_commas' => __('Separate Testimonial Categories with commas', 'noo') ,
            'add_or_remove_items' => __('Add or remove Testimonial Categories', 'noo') ,
            'choose_from_most_used' => __('Choose from the most used Testimonial Categories', 'noo') ,
            'not_found' => __('No Testimonial Categories found', 'noo') ,
        );

        $category_args = array(
            'labels' => $category_labels,
            'public' => false,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'testimonial_category',
                'with_front' => false
            ) ,
        );

        register_taxonomy('testimonial_category', array(
            'testimonial'
        ) , $category_args);

    }
endif;

add_action('init', 'noo_init_testimonial');

if( !function_exists('noo_shortcode_testimonial') ){
    function noo_shortcode_testimonial($atts){
        wp_enqueue_script( 'vendor-carouFredSel' );
        extract(shortcode_atts(array(
            // 'title'          => '',
            'image_per_page'    => '3',
            'style'             => 1,
            'autoplay'          => 'true',
            'slider_speed'      => '800',
            'hidden_pagination' => 'true'
        ), $atts));

        ob_start();
            $args = array(
                'post_type'         =>  'testimonial',
                'posts_per_page'    =>   '-1',
            );
           
            $query = new WP_Query( $args );
            if ( $style == 2 ) $image_per_page = 1;
            $options = array(
                'id'                => 'testimonial',
                'show'              => 'testimonial',
                'style'             => $style,
                'max'               => $image_per_page,
                'autoplay'          => $autoplay,
                'slider_speed'      => $slider_speed,
                'hidden_pagination' => $hidden_pagination
            );
            noo_caroufredsel_slider( $query, $options );

        $testimonial = ob_get_contents();
        ob_end_clean();
        return $testimonial;
    }
    add_shortcode('noo_testimonial','noo_shortcode_testimonial');
}