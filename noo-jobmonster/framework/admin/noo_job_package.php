<?php

if( defined('WOOCOMMERCE_VERSION') && !class_exists('WC_Product_Job_Package') ) :
class WC_Product_Job_Package extends WC_Product {
	
	public function __construct( $product ) {
		$this->product_type = 'job_package';
		parent::__construct( $product );
	}
	
	public function is_purchasable() {
		return true;
	}
	
	public function is_sold_individually() {
		return true;
	}
	
	public function is_virtual() {
		return true;
	}

	public function is_downloadable() {
		return true;
	}

	public function has_file( $download_id = '' ) {
		return false;
	}

	public function is_unlimited_job_posting() {
		return (bool) $this->job_posting_unlimit;
	}
	
	public function get_post_job_limit(){

		if($this->is_unlimited_job_posting()){
			return 99999999;
		}
		
		if($this->job_posting_limit){
			return absint( $this->job_posting_limit );
		}
		return 0;
	}
	
	public function get_job_feature_limit(){
		if($this->job_feature_limit){
			return absint( $this->job_feature_limit );
		}
		return 0;
	}
	
	public function get_can_view_resume(){
		return $this->can_view_resume;
	}
	
	public function get_job_display_duration(){
		if($this->job_display_duration){
			return $this->job_display_duration;
		}
		return 1;
	}
	
	public function get_package_interval(){
		if(isset($this->job_package_interval)){
			return $this->job_package_interval;
		}
		return '';
	}
	
	public function get_package_interval_unit(){
		if($this->job_package_interval_unit){
			return $this->job_package_interval_unit;
		}
		return 'day';
	}
	
	public function get_company_featured(){
		if($this->company_featured){
			return (bool) $this->company_featured;
		}
		return false;
	}
	
	public function add_to_cart_url() {
		$url = $this->is_in_stock() ? esc_url( remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id, home_url() ) ) ) : get_permalink( $this->id );
		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}
	
	public function add_to_cart_text() {
		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Select', 'noo' ) : __( 'Read More', 'noo' );
		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}
}
endif;

if( !class_exists('Noo_Job_Package') ) :
class Noo_Job_Package {
	
	public function __construct(){
		add_action('init', array($this,'init'));
		add_shortcode('noo_job_package_list', array($this,'noo_job_package_list_shortcode'));
		add_action('woocommerce_add_to_cart_handler_job_package', array($this,'woocommerce_add_to_cart_handler'),100);

		add_action( 'woocommerce_order_status_completed', array( $this, 'order_paid' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'order_changed' ), 10, 3 );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'checkout_fields_job_meta' ) );

		// Expired package
		add_action( 'noo-job-package-expired', array( 'Noo_Job_Package', 'reset_job_package' ) );
	
		if(is_admin()){
			add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_data_tabs' ) );

			add_action('admin_init', array($this,'admin_init'));
			add_filter('noo_job_settings_tabs_array', array($this,'add_seting_job_package_tab'));
			add_action('noo_job_setting_job_package', array($this,'setting_page'));
			add_filter( 'admin_enqueue_scripts', array($this,'wc_admin_scripts') );
		}else{
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ),100);
		}
	}
	
	public function init(){
		// if(!defined('WOOCOMMERCE_VERSION'))
		// 	return ;
		
		add_action( 'after_switch_theme', array($this,'switch_theme_hook'));
		if(is_admin()){
			add_filter( 'product_type_selector' , array($this, 'product_type_selector'));
			add_action( 'woocommerce_product_options_general_product_data', array( $this, 'job_package_product_data' ) );
			add_action( 'woocommerce_process_product_meta', array( $this,'save_product_data' ) );
		}
	}
	
	public function pre_get_posts($q){
		global $noo_view_job_package;
	
		if(!defined('WOOCOMMERCE_VERSION'))
			return ;
		if(empty($noo_view_job_package) && $this->is_woo_product_query($q))
		{
			$tax_query = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array( 'job_package' ),
				'operator' => 'NOT IN',
			);
			$q->tax_query->queries[] = $tax_query;
			$q->query_vars['tax_query'] = $q->tax_query->queries;
		}
		$noo_view_job_package = false;
	
	}
	
	protected function is_woo_product_query($query = null){
		if( empty( $query ) ) return false;
		if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'product' )
			return true;
		if(is_post_type_archive( 'product' ) || is_product_taxonomy() )
			return true;
		return false;
	
	}

	public function checkout_fields_job_meta( $order_id ) {
		global $woocommerce;

		/* -------------------------------------------------------
		 * Create order create fields _job_id for storing job that need to activate
		 * ------------------------------------------------------- */
			foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $cart_item ) {
				if ( isset( $cart_item['_job_id'] ) && is_numeric( $cart_item['_job_id'] ) ) :

				    update_post_meta( $order_id, '_job_id', sanitize_text_field( $cart_item['_job_id'] ) );

				endif;
		   }
	}
	
	public function order_paid($order_id){
		$order = new WC_Order( $order_id );
		if ( get_post_meta( $order_id, 'job_package_processed', true ) ) {
			return;
		}
		foreach ( $order->get_items() as $item ) {
			$product = wc_get_product( $item['product_id'] );

			if ($product->is_type( 'job_package' ) && $order->customer_user ) {
				$user_id = $order->customer_user;

				$package_interval = absint($product->get_package_interval());
				$package_interval_unit = $product->get_package_interval_unit();
				$package_data = array(
					'product_id'   => $product->id,
					'order_id'	   => $order_id,
					'created'      => current_time('mysql'),
					'package_interval' => $package_interval,
					'package_interval_unit' => $package_interval_unit,
					'job_duration' => absint($product->get_job_display_duration()),
					'job_limit'    => absint($product->get_post_job_limit()),
					'job_featured' => absint($product->get_job_feature_limit()),
					'company_featured' => $product->get_company_featured(),
				);

				$package_data = apply_filters( 'jm_job_package_user_data', $package_data, $product );

				if( !self::is_purchased_free_package( $user_id ) || $product->get_price() > 0 ) {
					if( $product->get_company_featured() ) {
						$company_id = jm_get_employer_company( $user_id );
						if( $company_id ) {
							update_post_meta( $company_id, '_company_featured', 'yes' );
						}
					}
					if( !empty( $package_interval ) ) {
						$expired = strtotime( "+{$package_interval} {$package_interval_unit}" );
						$package_data['expired'] = $expired;
						Noo_Job_Package::set_expired_package_schedule( $user_id, $package_data );
					}
					update_user_meta( $user_id, '_job_package', $package_data );
					update_user_meta( $user_id, '_job_added', '0' );
					update_user_meta( $user_id, '_job_featured', '0' );
					
					$job_id = noo_get_post_meta( $order_id, '_job_id', '' );
					if ( !empty( $job_id ) && is_numeric( $job_id ) ) {
						$job = get_post( $job_id );
						if ( $job->post_type == 'noo_job' ){
							jm_increase_job_posting_count( $user_id );
							$job_need_approve = jm_get_job_setting( 'job_approve','yes' ) == 'yes';
							if( !$job_need_approve ) {
								wp_update_post(array(
									'ID'=>$job_id,
									'post_status'=>'publish',
									'post_date'		=> current_time( 'mysql' ),
									'post_date_gmt'	=> current_time( 'mysql' , 1 )
								));
								jm_set_job_expired( $job_id );
							} else {
								wp_update_post(array(
									'ID'=>$job_id,
									'post_status'=>'pending'
								));
								update_post_meta($job_id, '_in_review', 1);
							}

							Noo_Job::send_notification($job_id, $user_id);
						}
					}

					if( $product->get_price() <= 0 ) {
						update_user_meta( $user_id, '_free_package_bought', 1 );
					}

					if( $product->is_unlimited_job_posting() ) {
						// TODO: add something for the unlimited package.
					}

					do_action( 'jm_job_package_order_completed', $product, $user_id );
				}

				break;
			}
		}
		update_post_meta( $order_id, 'job_package_processed', true );
	}

	public function order_changed( $order_id, $old_status, $new_status ){
		if ( get_post_meta( $order_id, 'job_package_processed', true ) ) {

			// Check if order is changing from completed to not completed
			if( $old_status == 'completed' && $new_status != 'completed' ) {
				$order = new WC_Order( $order_id );
				foreach ( $order->get_items() as $item ) {
					$product = wc_get_product( $item['product_id'] );

					// Check if there's job package in this order
					if ($product->is_type( 'job_package' ) && $order->customer_user ) {
						$user_id = $order->customer_user;

						$user_package = jm_get_job_posting_info( $user_id );

						// Check if user is currently active with this order
						if( !empty( $user_package ) && isset( $user_package['order_id'] ) && absint( $order_id ) == absint( $user_package['order_id'] ) ) {

							self::reset_job_package( $user_id );
	
							// Reset the processed status so that it can update if the order is reseted.
							update_post_meta( $order_id, 'job_package_processed', false );
						}

						break;
					}
				}
			}
		}
	}
	
	public function woocommerce_add_to_cart_handler(){
		global $woocommerce;
		$product_id          = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
		$product 			= wc_get_product( absint($product_id) );
		$quantity 			= empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
		$passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		if ( $product->is_type( 'job_package' ) && $passed_validation ) {
			// Add the product to the cart
			$woocommerce->cart->empty_cart();
			if (  $woocommerce->cart->add_to_cart( $product_id, $quantity ) ) {
				//woocommerce_add_to_cart_message( $product_id );
				wp_safe_redirect($woocommerce->cart->get_checkout_url());
				die;
			}
		}
		
	}
	
	public function admin_init(){
		register_setting('job_package','job_package');
	}
	
	public static function get_setting($id = null ,$default = null){
		global $job_package_setting;
		if(!isset($job_package_setting) || empty($job_package_setting)){
			$job_package_setting = get_option('job_package');
		}
		if (isset($job_package_setting[$id])) {
			return $job_package_setting[$id];
		}
		return $default;
	}
	
	public function switch_theme_hook($newname = '', $newtheme = ''){
		if(defined('WOOCOMMERCE_VERSION')){
			if ( ! get_term_by( 'slug', sanitize_title( 'job_package' ), 'product_type' ) ) {
				wp_insert_term( 'job_package', 'product_type' );
			}
		}
	}
	
	public function product_type_selector($types){
		$types[ 'job_package' ] = __( 'Job Package', 'noo' );
		return $types;
	}

	public function wc_admin_scripts() {
		if(get_post_type() === 'product' ){
			wp_enqueue_style( 'noo-package-product', NOO_FRAMEWORK_ADMIN_URI . '/assets/css/noo_package_product.css');
		}
	}
	
	public function job_package_product_data(){
		global $post;
		?>
		<div class="options_group show_if_job_package">
		<?php 
		
		noo_wc_wp_time_interval( 
			array( 
				'id' => '_job_package_interval', 
				'label' => __( 'Expired After', 'noo' ), 
				'description' => __( 'The time that buyer can use this package. Use zero for unlimited time.', 'noo' ), 
				'value' => get_post_meta( $post->ID, '_job_package_interval', true ),
				'std' => 30,
				'placeholder' => '', 
				'desc_tip' => true, 
				'custom_attributes' => array( 'min' => '', 'step' => '1' ) ) );
		$custom_attributes = get_post_meta( $post->ID, '_job_posting_unlimit', true ) ? 'disabled' : '';
		woocommerce_wp_text_input( 
			array( 
				'id' => '_job_posting_limit', 
				'label' => __( 'Job posting limit', 'noo' ), 
				'description' => __( 'The number of jobs an user can post with this package.', 'noo' ), 
				'value' => max( get_post_meta( $post->ID, '_job_posting_limit', true ), 0 ), 
				'placeholder' => __( 'No job posting', 'noo' ), 
				'type' => 'number', 
				'desc_tip' => true, 
				'custom_attributes' => array( 'min' => '', 'step' => '1', $custom_attributes => $custom_attributes ) 
			) 
		);
		woocommerce_wp_checkbox(
			array(
				'id' => '_job_posting_unlimit', 
				'label' => '',
				'value' => get_post_meta( $post->ID, '_job_posting_unlimit', true ),
				'description' => __( 'Unlimited posting?', 'noo' ), 
			)
		);
		woocommerce_wp_text_input( 
			array( 
				'id' => '_job_feature_limit', 
				'label' => __( 'Featured Job limit', 'noo' ), 
				'description' => __( 'The number of featured jobs an employer can set with this package.', 'noo' ), 
				'value' => max( get_post_meta( $post->ID, '_job_feature_limit', true ), 0 ), 
				'placeholder' => '', 
				'desc_tip' => true, 
				'type' => 'number', 
				'custom_attributes' => array( 'min' => '', 'step' => '1' ) ) );
		woocommerce_wp_text_input( 
			array( 
				'id' => '_job_display_duration', 
				'label' => __( 'Job display duration', 'noo' ), 
				'description' => __( 'The number of days that the job listing will be displayed.', 'noo' ), 
				'value' => get_post_meta( $post->ID, '_job_display_duration', true ), 
				'std' => 30, 
				'placeholder' => '', 
				'desc_tip' => true, 
				'type' => 'number', 
				'custom_attributes' => array( 'min' => '', 'step' => '1' ) ) );
		woocommerce_wp_checkbox(
			array(
				'id' => '_company_featured', 
				'label' => __( 'Featured Company', 'noo' ),
				'value' => get_post_meta( $post->ID, '_company_featured', true ),
				'description' => __( 'Company ( Employer ) with this package will be featured.', 'noo' ), 
			)
		);

			?>
		
			<script type="text/javascript">
				jQuery('.pricing').addClass( 'show_if_job_package' );
				jQuery(document).ready(function($) {
					$("#_job_posting_unlimit").change(function() {
						if(this.checked) {
							$('#_job_posting_limit').prop('disabled', true);
						} else {
							$('#_job_posting_limit').prop('disabled', false);
						}
					});
				});
			</script>
			<?php 
			do_action('noo_job_package_data')
			?>
		</div>
		<?php
	}
	
	public function save_product_data($post_id){
		// Save meta
		$fields = array(
			'_job_package_interval'			=> '',
			'_job_package_interval_unit'	=> '',
			'_job_posting_limit'			=> 'int',
			'_job_feature_limit'			=> 'int',
			'_job_posting_unlimit'			=> '',
			'_job_display_duration'			=> '',
			'_company_featured'				=> '',
		);

		$fields = apply_filters( 'noo_job_package_fields_data', $fields );
		foreach ( $fields as $key => $type ) {
			$value = isset( $_POST[ $key ] ) ? $_POST[ $key ] : '';
			switch ( $type ) {
				case 'int' :
					$value = absint( $value );
					break;
				case 'float' :
					$value = floatval( $value );
					break;
				default :
					$value = sanitize_text_field( $value );
			}
			update_post_meta( $post_id, $key, $value );
		}
		
		do_action('noo_job_package_save_data', $post_id );
	}

	public function product_data_tabs( $product_data_tabs = array() ) {
		if( empty( $product_data_tabs ) ) return;

		if( isset( $product_data_tabs['shipping'] ) && isset( $product_data_tabs['shipping']['class'] ) ) {
			$product_data_tabs['shipping']['class'][] = 'hide_if_job_package';
		}
		if( isset( $product_data_tabs['linked_product'] ) && isset( $product_data_tabs['linked_product']['class'] ) ) {
			$product_data_tabs['linked_product']['class'][] = 'hide_if_job_package';
		}
		if( isset( $product_data_tabs['attribute'] ) && isset( $product_data_tabs['attribute']['class'] ) ) {
			$product_data_tabs['attribute']['class'][] = 'hide_if_job_package';
		}

		return $product_data_tabs;
	}
	
	public function add_seting_job_package_tab($tabs){
		$temp1 = array_slice($tabs, 0, 2);
		$temp2 = array_slice($tabs, 2);

		$job_package_tab = array( 'job_package' => __('Packages','noo') );
		return array_merge($temp1, $job_package_tab, $temp2);
	}
	
	public function noo_job_package_list_shortcode($atts, $content = null){
		extract(shortcode_atts(array(
				'product_cat' =>'',
				'columns' => ''
			), $atts));

		ob_start();
		include(locate_template("layouts/job_package.php"));
		return ob_get_clean();
	}
	
	public function setting_page(){
		?>
			<?php settings_fields('job_package'); ?>
			<h3><?php echo __('Job Package Options','noo')?></h3>
			<table class="form-table" cellspacing="0">
				<tbody>
					<?php if(defined('WOOCOMMERCE_VERSION')) : ?>
						<tr>
							<th>
								<?php esc_html_e('Job Package Page','noo')?>
							</th>
							<td>
								<?php 
								$args = array(
									'name'             => 'job_package[package_page_id]',
									'id'               => 'package_page_id',
									'show_option_none' => ' ',
									'class'            => '',
									'echo'             => false,
									'selected'         => self::get_setting('package_page_id')
								);
								?>
								<?php echo str_replace(' id=', " data-placeholder='" . __( 'Select a page&hellip;', 'noo' ) .  "' id=", wp_dropdown_pages( $args ) ); ?>
								<p><small><?php _e('Select a page with shortcode [noo_job_package_list]', 'noo'); ?></small></p>
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e('Allow re-purchase free package','noo')?>
							</th>
							<td>
								<?php $repurchase_free = self::get_setting('repurchase_free',''); ?>
								<input type="hidden" name="job_package[repurchase_free]" value="">
								<input type="checkbox" <?php checked( $repurchase_free, '1' ); ?> name="job_package[repurchase_free]" value="1">
								<p><small><?php echo __('Enable this option if you allow employer to purchase the free package more than one time.','noo') ?></small></p>
							</td>
						</tr>
					<?php else : ?>
						<tr>
							<th>
								<?php esc_html_e('WooCommerce Missing','noo')?>
							</th>
							<td>
								<p><?php echo sprintf( __('You need to install <a href="%s" target="_blank">WooCommerce</a> plugin to start creating and using Job Packages', 'noo'), 'https://wordpress.org/plugins/woocommerce/' ); ?></p>
							</td>
						</tr>
					<?php endif; ?>
					<?php do_action( 'noo_setting_job_package_fields' ); ?>
				</tbody>
			</table>
		<?php 
	}

	public static function is_purchased_free_package( $user_id = '' ) {
		if( empty( $user_id ) ) return false;

		if( self::get_setting('repurchase_free','') ) return false;

		return (bool) get_user_meta( $user_id, '_free_package_bought', true );
	}
	
	public static function get_package_interval_text( $interval = '', $unit = 'day' ) {
		if( $interval === '0' ) return __('Unlimited', 'noo');
		elseif( empty( $interval ) ) return '';

		switch ($unit) {
			case 'day':
				return sprintf( _n( '%d Day', '%d Days', $interval, 'noo' ), $interval );
			case 'week':
				return sprintf( _n( '%d Week', '%d Weeks', $interval, 'noo' ), $interval );
			case 'month':
				return sprintf( _n( '%d Month', '%d Months', $interval, 'noo' ), $interval );
			case 'year':
				return sprintf( _n( '%d Year', '%d Years', $interval, 'noo' ), $interval );
			case '0':
				return __('Unlimited', 'noo');
		}

		return '';
	}

	public static function reset_job_package( $user_id = '' ) {
		if( empty( $user_id ) ) return;
		
		$package = get_user_meta( $user_id, '_job_package', true );
		if( isset( $package['product_id'] ) ) {
			$product = wc_get_product( $package['product_id'] );
			if( $product && $product->get_company_featured() ) {
				// Reset company featured
				$company_id = jm_get_employer_company( $user_id );
				update_post_meta( $company_id, '_company_featured', 'no' );
			}
		}

		update_user_meta( $user_id, '_job_package', false );
		// update_user_meta( $user_id, '_job_added', '0' );
		// update_user_meta( $user_id, '_job_featured', '0' );
	}

	public static function set_expired_package_schedule( $user_id = '', $package_data = array() ) {
		if( empty( $user_id ) ) {
			return;
		}
		if( empty( $package_data ) ) {
			$package_data = jm_get_job_posting_info( $user_id );
		}

		wp_clear_scheduled_hook( 'noo-job-package-expired', array( $user_id ) );

		if( isset( $package_data['expired'] ) && !empty( $package_data['expired'] ) ) {
			wp_schedule_single_event( $package_data['expired'], 'noo-job-package-expired', array( $user_id ) );
		}
	}
}

new Noo_Job_Package();
endif;