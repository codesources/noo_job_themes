<?php

// =============================================================================
// WOOCOMMERCE/LOOP/LOOP-START.PHP
// -----------------------------------------------------------------------------
// @version 2.0.0
// =============================================================================
wp_enqueue_script('vendor-isotope');
?>


<ul class="products <?php echo 'products-'.(noo_get_option('noo_shop_layout', 'fullwidth') === 'fullwidth' ? 'fullwidth' : 'slidebar'); ?>">