<?php

function register_job_package_addon_product_type()
{

    class WC_Product_Job_Package_AddOn extends WC_Product
    {

        public function is_purchasable()
        {
            return true;
        }

        public function is_sold_individually()
        {
            return true;
        }

        public function is_virtual()
        {
            return true;
        }

        public function is_downloadable()
        {
            return true;
        }

        public function has_file($download_id = '')
        {
            return false;
        }

        public function __construct($product)
        {

            $this->product_type = 'job_package_addon';

            parent::__construct($product);

        }

        public function add_to_cart_url()
        {
            $url = $this->is_in_stock() ? esc_url(remove_query_arg('added-to-cart', add_query_arg('add-to-cart', $this->id, home_url()))) : get_permalink($this->id);
            return apply_filters('woocommerce_product_add_to_cart_url', $url, $this);
        }

        public function add_to_cart_text()
        {
            $text = __('Purchase', 'noo');
            return apply_filters('woocommerce_product_add_to_cart_text', $text, $this);
        }


    }

}

add_action('init', 'register_job_package_addon_product_type');

if (!class_exists('Noo_Job_Package_AddOn')) :
    class Noo_Job_Package_AddOn
    {
        public function __construct()
        {

            add_action('woocommerce_order_status_completed', array($this, 'job_package_addon_order_paid'));
            add_action('woocommerce_add_to_cart_handler_job_package_addon', array($this, 'woocommerce_add_to_cart_handler'), 100);
            add_action( 'after_switch_theme', array($this,'switch_theme_hook'));

            if (is_admin()) {
                add_filter('woocommerce_product_data_tabs', array($this, 'job_package_addon_hide_data_tabs'));
                add_filter('product_type_selector', array($this, 'job_package_addon_product_type'));
                add_action('woocommerce_product_options_general_product_data', array($this, 'job_package_addon_product_data'));
                add_action('woocommerce_process_product_meta', array($this, 'job_package_addon_product_save_data'));
            } else {
                add_action('pre_get_posts', array($this, 'job_package_addon_pre_get_posts'), 100);
            }
        }
    
        public function switch_theme_hook($newname = '', $newtheme = '')
        {
            if(defined('WOOCOMMERCE_VERSION')){
                if ( ! get_term_by( 'slug', sanitize_title( 'job_package_addon' ), 'product_type' ) ) {
                    wp_insert_term( 'job_package_addon', 'product_type' );
                }
            }
        }

        /**
         * Add to product type drop down.
         */
        public function job_package_addon_product_type($types)
        {
            $types['job_package_addon'] = __('Job Package Add-on');

            return $types;

        }

        /**
         * Hide data panel.
         */
        public function job_package_addon_hide_data_tabs($tabs)
        {
            $tabs['attribute']['class'][] = 'hide_if_job_package_addon';
            $tabs['shipping']['class'][] = 'hide_if_job_package_addon';
            $tabs['linked_product']['class'][] = 'hide_if_job_package_addon';

            return $tabs;

        }

        private function job_package_list()
        {
            $query_args = array(
                'post_type' => 'product',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_type',
                        'field' => 'slug',
                        'terms' => 'job_package',
                    ),
                ),
                'posts_per_page' => -1
            );
            $posts = get_posts($query_args);
            $job_packages = array( 'all' => __('All packages', 'noo') );
            foreach ($posts as $post) {
                $job_packages[$post->ID] = $post->post_title;
            }
            return $job_packages;
        }

        /**
         * Addon data
         */

        public function job_package_addon_product_data()
        {
            global $post;
            ?>
            <div class="options_group show_if_job_package_addon">
                <?php
                noo_wc_wp_select_multiple(
                    array(
                        'id' => '_job_addon_select_package',
                        'label' => __('Select package', 'noo'),
                        'description' => __('This add-on will be assigned to the selected packages', 'noo'),
                        'options' => $this->job_package_list(),
                        'desc_tip' => true,
                    )
                );

                $custom_attributes = get_post_meta($post->ID, '_job_addon_posting_unlimit', true) ? 'disabled' : '';
                woocommerce_wp_text_input(
                    array(
                        'id' => '_job_addon_posting_limit',
                        'label' => __('Job posting limit', 'noo'),
                        'description' => __('The number of jobs an user can post with this package.', 'noo'),
                        'value' => max(get_post_meta($post->ID, '_job_addon_posting_limit', true), 0),
                        'placeholder' => __('No job posting', 'noo'),
                        'type' => 'number',
                        'desc_tip' => true,
                        'custom_attributes' => array('min' => '', 'step' => '1', $custom_attributes => $custom_attributes)
                    )
                );
                woocommerce_wp_checkbox(
                    array(
                        'id' => '_job_addon_posting_unlimit',
                        'label' => '',
                        'value' => get_post_meta($post->ID, '_job_addon_posting_unlimit', true),
                        'description' => __('Unlimited posting?', 'noo'),
                    )
                );
                woocommerce_wp_text_input(
                    array(
                        'id' => '_job_addon_feature_limit',
                        'label' => __('Featured Job limit', 'noo'),
                        'description' => __('The number of featured jobs an employer can set with this package.', 'noo'),
                        'value' => get_post_meta($post->ID, '_job_addon_feature_limit', true),
                        'placeholder' => '',
                        'desc_tip' => true,
                        'type' => 'number',
                        'custom_attributes' => array('min' => '', 'step' => '1')));

                ?>

                <script type="text/javascript">
                    jQuery('.pricing').addClass('show_if_job_package_addon');
                    jQuery(document).ready(function ($) {
                        $("#_job_addon_posting_unlimit").change(function () {
                            if (this.checked) {
                                $('#_job_addon_posting_limit').prop('disabled', true);
                            } else {
                                $('#_job_addon_posting_limit').prop('disabled', false);
                            }
                        });
                    });
                </script>
                <?php
                do_action('noo_job_package_addon_data')
                ?>
            </div>
            <?php

        }

        /**
         * Save data
         */

        public function job_package_addon_product_save_data($post_id)
        {

            $package = !empty($_POST['_job_addon_select_package']) ? $_POST['_job_addon_select_package'] : '';
            $limit = !empty($_POST['_job_addon_posting_limit']) ? $_POST['_job_addon_posting_limit'] : '';
            $feature = !empty($_POST['_job_addon_feature_limit']) ? $_POST['_job_addon_feature_limit'] : '';
            $unlimit = isset($_POST['_job_addon_posting_unlimit']) && !empty($_POST['_job_addon_posting_unlimit']) ? $_POST['_job_addon_posting_unlimit'] : '';

            update_post_meta($post_id, '_job_addon_select_package', serialize($package));
            update_post_meta($post_id, '_job_addon_posting_limit', esc_attr($limit));
            update_post_meta($post_id, '_job_addon_feature_limit', esc_attr($feature));
            update_post_meta($post_id, '_job_addon_posting_unlimit', esc_attr($unlimit));

        }

        public function job_package_addon_pre_get_posts($q)
        {
            global $noo_view_job_package_addon;

            if (!defined('WOOCOMMERCE_VERSION'))
                return;
            if (empty($noo_view_job_package_addon) && $this->is_woo_product_query($q)) {
                $tax_query = array(
                    'taxonomy' => 'product_type',
                    'field' => 'slug',
                    'terms' => array('job_package_addon'),
                    'operator' => 'NOT IN',
                );
                $q->tax_query->queries[] = $tax_query;
                $q->query_vars['tax_query'] = $q->tax_query->queries;
            }
            $noo_view_job_package_addon = false;

        }

        protected function is_woo_product_query($query = null)
        {
            if (empty($query)) return false;
            if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'product')
                return true;
            if (is_post_type_archive('product') || is_product_taxonomy())
                return true;
            return false;

        }

        public function woocommerce_add_to_cart_handler()
        {
            global $woocommerce;
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_REQUEST['add-to-cart']));
            $product = wc_get_product(absint($product_id));
            $quantity = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount($_REQUEST['quantity']);
            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
            if ($product->is_type('job_package_addon') && $passed_validation) {
                // Add the product to the cart
                $woocommerce->cart->empty_cart();
                if ($woocommerce->cart->add_to_cart($product_id, $quantity)) {
                    wp_safe_redirect($woocommerce->cart->get_checkout_url());
                    die;
                }
            }

        }

        /**
         * Order processing.
         */
        public function job_package_addon_order_paid($order_id)
        {
            $order = new WC_Order($order_id);

            foreach ($order->get_items() as $item) {
                $product = wc_get_product($item['product_id']);

                if ($product->is_type('job_package_addon') && $order->customer_user) {
                    $user_id = $order->customer_user;

                    $user = get_user_meta($user_id, '_job_package', true);

                    $current_job_limit = $user['job_limit'];
                    $current_job_featured = $user['job_featured'];

                    $package_addon_limit = get_post_meta($item['product_id'], '_job_addon_posting_limit', true);
                    $package_addon_unlimit = get_post_meta($item['product_id'], '_job_addon_posting_unlimit', true);
                    $package_addon_featured = get_post_meta($item['product_id'], '_job_addon_feature_limit', true);


                    if ($package_addon_unlimit == 'yes') {
                        $job_limit = 99999999;
                    } else {
                        $job_limit = absint($current_job_limit + $package_addon_limit);
                    }

                    $user['job_limit'] = $job_limit;
                    $user['job_featured'] = absint($current_job_featured + $package_addon_featured);

                    update_user_meta($user_id, '_job_package', $user);

                    break;
                }
            }
        }
    }

    new Noo_Job_Package_AddOn();

endif;