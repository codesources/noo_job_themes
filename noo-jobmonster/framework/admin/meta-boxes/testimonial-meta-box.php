<?php
    if( !function_exists('noo_testimonial_meta_boxs') ):
        function noo_testimonial_meta_boxs(){
            // Declare helper object
            $prefix = '_noo_wp_post';
            $helper = new NOO_Meta_Boxes_Helper($prefix, array(
                'page' => 'testimonial'
            ));
            // Post type: Gallery
            $meta_box = array(
                'id' => "{$prefix}_meta_box_testimonial",
                'title' => __('Testimonial options', 'noo'),
                'fields' => array(
                    array(
                        'id' => "{$prefix}_image",
                         'label' => __( 'Your Image', 'noo' ),
                        'type' => 'image',
                    ),
                    array(
                        'id' => "{$prefix}_name",
                         'label' => __( 'Your Name', 'noo' ),
                        'type' => 'text',
                    ),
                    array(
                        'id' => "{$prefix}_position",
                         'label' => __( 'Your Position', 'noo' ),
                        'type' => 'text',
                    ),
                )
            );

            $helper->add_meta_box($meta_box);
        }
        add_action('add_meta_boxes', 'noo_testimonial_meta_boxs');
    endif;
?>