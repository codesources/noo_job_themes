<?php

function job_package_addon_checkout()
{
    $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
    $product = wc_get_product($product_id);

    if ($product->is_type('job_package_addon')) {

        $user_id = get_current_user_id();
        $user = get_user_meta($user_id, '_job_package', true);
        $user_package_id = $user['product_id'];

        if (!empty($user_package_id)) {
            $package_addon = unserialize(get_post_meta($product_id, '_job_addon_select_package', true));

            if (in_array($user_package_id, $package_addon) or in_array('all', $package_addon)) {
                wp_safe_redirect($product->add_to_cart_url());
                exit();
            } else {
                noo_message_add(__('This add-on is not included in this package', 'noo'), 'error');
                wp_safe_redirect(Noo_Member::get_member_page_url());
                exit();
            }
        } else {
            noo_message_add(__('You are not using any packages', 'noo'), 'error');
            wp_safe_redirect(Noo_Member::get_member_page_url());
            exit();
        }
    }
}


function job_package_addon_show(){
if (!Noo_Member::is_employer()) return;

$user_id = get_current_user_id();
$user = get_user_meta($user_id, '_job_package', true);

if (empty($user['product_id'])) return;
$user_package_id = $user['product_id'];

global $noo_view_job_package_addon;
$noo_view_job_package_addon = true;


$query_args = array(
    'post_type' => 'product',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field' => 'slug',
            'terms' => 'job_package_addon',
        )
    ),
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => '_job_addon_select_package',
            'value' => serialize(strval($user_package_id)),
            'compare' => 'LIKE'
        ),
        array(
            'key' => '_job_addon_select_package',
            'value' => 'all',
            'compare' => 'LIKE'
        )
    ),
    'suppress_filters' => false
);

$packages = get_posts($query_args);
if (!empty($packages)):
    ?>
    <hr/>
    <div class="package-add-ons container-boxed">
        <div class="txt-addon">
            <h3><?php _e('Package Add-ons', 'noo') ?></h3>
            <p><?php _e('Additional Add-ons to boost your package', 'noo') ?></p>
        </div>
        <div class="noo-vc-row row">
            <div class="noo-vc-col col-md-12 ">
                <div class="noo-text-block">
                    <div class="job-package clearfix">
                        <div class="noo-pricing-table classic pricing-4-col package-pricing">
                            <?php

                            foreach ($packages as $package):?>
                                <?php
                                $product = wc_get_product($package->ID);
                                $checkout_url = Noo_Member::get_checkout_url($product->id);
                                ?>
                                <div
                                    class="noo-pricing-column <?php echo($product->is_featured() ? 'featured' : ''); ?>">
                                    <div class="pricing-content">
                                        <div class="pricing-header">
                                            <h2 class="pricing-title"><?php echo esc_html($product->get_title()) ?></h2>
                                            <h3 class="pricing-value"><span
                                                    class="noo-price"><?php echo wp_kses_post($product->get_price_html()) ?></span>
                                            </h3>
                                        </div>
                                        <div class="pricing-info">
                                            <ul class="noo-ul-icon fa-ul">
                                                <?php
                                                $package_addon_limit = get_post_meta($product->id, '_job_addon_posting_limit', true);
                                                $package_addon_unlimit = get_post_meta($product->id, '_job_addon_posting_unlimit', true);
                                                $package_addon_featured = get_post_meta($product->id, '_job_addon_feature_limit', true);
                                                $job_posting_text = $package_addon_unlimit == 'yes' ? __('Unlimited job posting', 'noo') : sprintf( _n('%s job posting', '%s jobs posting', $package_addon_limit, 'noo'), $package_addon_limit );
                                                $job_featured_text = sprintf( _n('%s featured job', '%s featured jobs', $package_addon_featured, 'noo'), $package_addon_featured );
                                                ?>
                                                <?php if ($package_addon_unlimit || $package_addon_limit > 0) : ?>
                                                    <li class="noo-li-icon"><i
                                                            class="fa fa-check-circle"></i> <?php echo $job_posting_text; ?>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if ($package_addon_featured > 0) : ?>
                                                    <li class="noo-li-icon"><i
                                                            class="fa fa-check-circle"></i> <?php echo $job_featured_text; ?>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                            <?php if (!empty($package->post_excerpt)) : ?>
                                                <div class="short-desc">
                                                    <?php echo apply_filters('woocommerce_short_description', $package->post_excerpt); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="pricing-footer">
                                            <a class="btn btn-lg btn-primary"
                                               href="<?php echo esc_url($checkout_url); ?>"
                                               data-package="<?php echo $product->id ?>"><?php echo wp_kses_post($product->add_to_cart_text()) ?></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
endif;
}

add_action('before_package_checkout', 'job_package_addon_checkout');
add_action('after_manage_plan', 'job_package_addon_show');