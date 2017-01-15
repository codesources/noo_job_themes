<?php 
	$add_to_cart = jm_is_resume_posting_page() ? false : true;
?>
<div class="job-package clearfix">
	<?php
	global $noo_view_resume_package;
	$noo_view_resume_package = true;
	$product_args = array(
		'post_type'        => 'product',
		'posts_per_page'   => 6,
		'suppress_filters' => false,
		'tax_query'        => array(
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array( 'resume_package' )
			)
		),
		'orderby'   => 'menu_order title',
		'order'     => 'ASC',
		'suppress_filters' => false
	);
	if( isset( $product_cat ) && !empty( $product_cat ) ) {
		$product_args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => explode(',', $product_cat)
		);
	}
	$packages                = get_posts( $product_args );
	$noo_view_resume_package    = false;
	$user_id                 = get_current_user_id();
	$purchased_free_package  = Noo_Resume_Package::is_purchased_free_package( $user_id );
	$columns = !isset( $columns ) || empty( $columns ) ? min( count( $packages ), 4 ) : absint( $columns );
	?>
	<?php if($packages): ?>
		<?php do_action( 'noo_resume_package_before' ); ?>
		<div class="noo-pricing-table classic row package-pricing">
			<?php foreach ($packages as $package):?>
				<?php 
					$product = wc_get_product($package->ID);
					$checkout_url          = $add_to_cart ? Noo_Member::get_checkout_url( $product->id ) : add_query_arg('package_id',$product->id);
					$redirect_package_free = $add_to_cart ? Noo_Member::get_endpoint_url('manage-plan') : add_query_arg('package_id',$product->id);

                    $package_interval = $product->get_package_interval();
                    $package_interval_unit = $product->get_package_interval_unit();
                    $package_interval_text = Noo_Job_Package::get_package_interval_text( $package_interval, $package_interval_unit );
					$is_unlimited    = $product->is_unlimited_resume_posting();
					$resume_limit    = $product->get_post_resume_limit();

					$resume_limit_text = $is_unlimited ? __( 'Unlimited resume posting', 'noo' ) : sprintf( _n( '%s resume posting', '%s resumes posting', $resume_limit, 'noo' ), $resume_limit );
					$columns_class = ($columns == 5) ? 'noo-5' : (12 / $columns);
				?>
				<div class="noo-pricing-column <?php echo 'col-sm-6 col-md-' . $columns_class; ?> <?php echo ( $product->is_featured() ? 'featured' : '' ); ?>">
				    <div class="pricing-content">
				        <div class="pricing-header">
				            <h2 class="pricing-title"><?php echo esc_html($product->get_title())?></h2>
				            <h3 class="pricing-value"><span class="noo-price"><?php echo wp_kses_post($product->get_price_html())?></span></h3>
				        </div>
				        <div class="pricing-info">
				            <ul class="noo-ul-icon fa-ul">
				                <?php if( !empty( $package_interval_text ) ) : ?>
				                	<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo sprintf( __('%s Membership', 'noo'), $package_interval_text ); ?></li>
				                <?php endif; ?>
				                <?php if( $is_unlimited || $resume_limit > 0 ) : ?>
				                	<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo $resume_limit_text;?></li>
				                <?php else : ?>
				                	<li class="noo-li-icon"><i class="fa fa-times-circle-o not-good"></i> <?php echo __('No resume posting', 'noo');?></li>
				                <?php endif; ?>
				                <?php do_action('jm_resume_package_features_list', $product ); ?>
				            </ul>
				            <?php if( !empty( $package->post_excerpt ) ) : ?> 
				            	<div class="short-desc">
				            	<?php echo apply_filters( 'woocommerce_short_description', $package->post_excerpt ); ?>
				            	</div>
				            <?php endif; ?>
				            <?php if( !empty( $package->post_content ) ) : ?> 
				            	<a href="javascript:void(0)" class="readmore package-modal" data-toggle="modal" data-target="#package-content-<?php echo $package->ID; ?>"><i class="fa fa-arrow-circle-right"></i><?php echo __('More info', 'noo'); ?></a>
				            <?php endif; ?>
				        </div>
				        <div class="pricing-footer">
				        	<a class="btn btn-lg btn-primary <?php echo ( ($purchased_free_package && $product->get_price() <= 0 ) ? 'disabled' : ''); ?> <?php echo ($product->get_price() == 0 && is_user_logged_in() ) ? ' auto_create_order_free' : ''; ?>" data-id="<?php echo get_current_user_id(); ?>"<?php echo ($product->get_price() == 0 && is_user_logged_in() ) ? ' data-security="' . wp_create_nonce( 'noo-free-package' ) . '" data-url-package="' . $redirect_package_free . '"' : ' href="' . esc_url($checkout_url) .'"'; ?> data-package="<?php echo $product->id ?>"><?php echo wp_kses_post($product->add_to_cart_text())?></a>
				        </div>
				        <?php if( !empty( $package->post_content ) ) : ?> 
					        <div id="package-content-<?php echo $package->ID; ?>" class="package-content modal fade" tabindex="-1" role="dialog" aria-labelledby="package-content-<?php echo $package->ID; ?>Label" aria-hidden="true">
					        	<div class="modal-dialog package-modal">
					        		<div class="modal-content">
					        			<div class="modal-header">
					        				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					        				<h2 class="modal-title"><?php echo esc_html($product->get_title())?></h2>
					        			</div>
					        			<div class="modal-body">
					        				<div class="row">
					        					<div class="col-md-5 pricing-header">
									            	<h3 class="pricing-value"><span class="noo-price"><?php echo wp_kses_post($product->get_price_html())?></span></h3>
									            </div>
									            <div class="col-md-7 pull-right pricing-info">
									            	<ul class="noo-ul-icon fa-ul">
										                <?php if( !empty( $package_interval_text ) ) : ?>
										                	<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo sprintf( __('%s Membership', 'noo'), $package_interval_text ); ?></li>
										                <?php endif; ?>
										                <?php if( $is_unlimited || $resume_limit > 0 ) : ?>
										                	<li class="noo-li-icon"><i class="fa fa-check-circle"></i> <?php echo $resume_limit_text;?></li>
										                <?php else : ?>
										                	<li class="noo-li-icon"><i class="fa fa-times-circle-o not-good"></i> <?php echo __('No resume posting', 'noo');?></li>
										                <?php endif; ?>
				                						<?php do_action('jm_resume_package_features_list', $product ); ?>
										            </ul>
									            </div>
									            <div class="col-md-12 package-content">
					        						<?php echo apply_filters( 'noo_package_content', $package->post_content ); ?>
					        					</div>
									        </div>
					        			</div>
					        			<div class="modal-footer">
						        			<a class="btn btn-lg btn-primary <?php echo ( ($purchased_free_package && $product->get_price() <= 0 ) ? 'disabled' : ''); ?> <?php echo ($product->get_price() == 0 && is_user_logged_in() ) ? ' auto_create_order_free' : ''; ?>" data-id="<?php echo get_current_user_id(); ?>"<?php echo ($product->get_price() == 0 && is_user_logged_in() ) ? ' data-security="' . wp_create_nonce( 'noo-free-package' ) . '" data-url-package="' . $redirect_package_free . '"' : ' href="' . esc_url($checkout_url) .'"'; ?> data-package="<?php echo $product->id ?>"><?php echo wp_kses_post($product->add_to_cart_text())?></a>
					        			</div>
					        		</div>
					        	</div>
					        </div>
					    <?php endif; ?>
				    </div>
				</div>
			<?php endforeach;?>
			<script>
			</script>
		</div>
	<?php endif;?>
</div>
