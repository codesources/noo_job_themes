<?php
if(!class_exists('Noo_Company')):
	if( !class_exists('Noo_CPT') ) {
		require_once dirname(__FILE__) . '/noo_cpt.php';
	}
	class Noo_Company extends Noo_CPT {

		static $instance = false;
		static $employers = array();
		static $companies = array();

		public function __construct(){

			$this->post_type = 'noo_company';
			$this->slug = 'companies';
			$this->prefix = 'company';
			$this->option_key = 'noo_company';

			add_action( 'init', array( $this, 'register_post_type' ),0 );
			add_filter( 'template_include', array( $this, 'template_loader' ) );
			add_action( 'pre_get_posts', array ($this, 'pre_get_posts'), 1 );
			add_filter( 'posts_search', array ($this, 'search_by_title_only' ), 500, 2 );
			add_shortcode( 'noo_companies', array ($this, 'noo_companies_shortcode'), 2 );
			add_shortcode( 'noo_company_feature', array ($this, 'noo_company_feature_shortcode'), 3 );
			add_filter( 'redirect_canonical', array ($this, 'custom_disable_redirect_canonical' ) );

			if( is_admin() ) {
				add_action('admin_init', array($this,'admin_init'));
				add_action( 'wp_ajax_jm_set_company_featured', array($this, 'ajax_set_company_featured') );

				add_filter( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 2 );
				add_filter( 'display_post_states', array( $this, 'admin_page_state' ), 10, 2 );
				add_action( 'add_meta_boxes', array( $this, 'companies_page_notice' ), 10, 2 );
				add_action( 'add_meta_boxes', array($this, 'remove_meta_boxes' ), 20 );
				add_action( 'add_meta_boxes', array($this, 'add_meta_boxes' ), 30 );
				add_action( 'add_meta_boxes', array($this, 'add_meta_boxes_layouts' ), 40 );
				add_action( 'add_meta_boxes', array($this, 'add_meta_boxes_membership' ), 40 );
				add_filter( 'enter_title_here', array ($this,'custom_enter_title') );

				add_filter('manage_edit-' . $this->post_type . '_columns', array($this, 'manage_edit_columns'));
				add_action('manage_posts_custom_column', array($this, 'manage_posts_custom_column'));


				add_filter('noo_job_settings_tabs_array', array($this,'add_seting_company_tab'));
				add_action('noo_job_setting_company', array($this,'setting_page'));

				add_filter('wp_insert_post', array($this,'default_company_data'), 10, 3);

				// add_filter('months_dropdown_results', '__return_empty_array');
				// add_action( 'restrict_manage_posts', array($this, 'restrict_manage_posts') );
				// add_filter( 'parse_query', array($this, 'posts_filter') );
			}
		}

		public static function get_setting($id = null ,$default = null){
			global $noo_company_setting;
			if(!isset($noo_company_setting) || empty($noo_company_setting)){
				$noo_company_setting = get_option('noo_company');
			}
			if (isset($noo_company_setting[$id])) {
				return $noo_company_setting[$id];
			}
			return $default;
		}
	
		public function admin_init(){
			register_setting('noo_company','noo_company');
		}

		public function template_loader($template){
			if(is_post_type_archive( $this->post_type ) ){
				$template       = locate_template( "archive-{$this->post_type}.php" );
			}
			return $template;
		}

		public function register_post_type() {
			// Sample register post type
			$archive_slug = self::get_setting('archive_slug', 'companies');
			$archive_slug = empty( $archive_slug ) ? 'companies' : $archive_slug;

			register_post_type( 
				$this->post_type,
				array( 
					'labels' => array( 
						'name' => __( 'Companies', 'noo' ), 
						'singular_name' => __( 'Company', 'noo' ), 
						'menu_name' => __( 'Companies', 'noo' ),
						'all_items' => __( 'Companies', 'noo' ),
						'add_new' => __( 'Add New', 'noo' ),
						'add_new_item' => __( 'Add Company', 'noo' ),
						'edit' => __( 'Edit', 'noo' ), 
						'edit_item' => __( 'Edit Company', 'noo' ), 
						'new_item' => __( 'New Company', 'noo' ), 
						'view' => __( 'View', 'noo' ), 
						'view_item' => __( 'View Company', 'noo' ), 
						'search_items' => __( 'Search Company', 'noo' ), 
						'not_found' => __( 'No Companies found', 'noo' ), 
						'not_found_in_trash' => __( 'No Companies found in Trash', 'noo' )
					), 
					'public' => true,
					'has_archive' => true,
					'show_in_menu' => 'edit.php?post_type=noo_job',
					'rewrite' => array( 'slug' => $archive_slug, 'with_front' => false ), 
					'supports' => array(
						'title',
						'editor'
					),
				)
			);
		}

		public function admin_page_state( $states = array(), $post = null ) {
			if( !empty( $post ) && is_object( $post ) ) {
				$archive_slug = self::get_setting('archive_slug', 'companies');
				if( !empty( $archive_slug ) && $archive_slug == $post->post_name ) {
					$states['companies_page'] = __('Companies Page', 'noo');
				}
			}

			return $states;
		}

		public function companies_page_notice( $post_type = '', $post = null ) {
			if( !empty( $post ) && is_object( $post ) ) {
				$archive_slug = self::get_setting('archive_slug', 'companies');
				if ( !empty( $archive_slug ) && $archive_slug == $post->post_name && empty( $post->post_content ) ) {
					add_action( 'edit_form_after_title', array( $this, '_companies_page_notice' ) );
					remove_post_type_support( $post_type, 'editor' );
				}
			}
		}

		public function _companies_page_notice() {
			echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the page that shows all your companies.', 'noo' ) . '</p></div>';
		}

		public function admin_enqueue_scripts() {
			if(get_post_type() === 'noo_company' ){
				wp_enqueue_style( 'noo-job', NOO_FRAMEWORK_ADMIN_URI . '/assets/css/noo_company.css');
			}
		}

		public function remove_meta_boxes() {
			// Remove slug and revolution slider
			remove_meta_box( 'mymetabox_revslider_0', $this->post_type, 'normal' );
		}

		public function add_meta_boxes() {
			// Declare helper object
			$prefix = '';
			$helper = new NOO_Meta_Boxes_Helper( $prefix, array( 'page' => $this->post_type ) );

			// General Info
			$meta_box = array(
				'id'           => '_general_info',
				'title'        => __( 'Company Information', 'noo' ),
				'context'      => 'normal',
				'priority'     => 'core',
				'description'  => '',
				'fields'       => array(),
			);

			$fields = jm_get_company_custom_fields();
			if($fields){
				foreach ($fields as $field){
					$id = jm_company_custom_fields_name($field['name'], $field);

					$new_field = noo_custom_field_to_meta_box( $field, $id );

					if( $field['name'] == '_address' ) {
						$new_field['type'] = 'select';
						$job_locations = array();
						// $job_locations[] = array('value'=>'','label'=>__('- Select a location -','noo'));
						$job_locations_terms = (array) get_terms('job_location', array('hide_empty'=>0));

						if( !empty( $job_locations_terms ) ) {
							foreach ($job_locations_terms as $location){
								$job_locations[] = array('value'=>$location->term_id,'label'=>$location->name);
							}
						}

						$new_field['options'] = $job_locations;
					}

					$meta_box['fields'][] = $new_field;
				}
			}

			$all_socials = noo_get_social_fields();
			$socials = jm_get_company_socials();

			if( $socials ) {

				foreach ($socials as $social) {
					if( !isset( $all_socials[$social] ) ) continue;

					$new_field = array(
						'label' => $all_socials[$social]['label'],
						'id' => $prefix . '_' . $social,
						'type' => 'text',
						'std' => ''
					);
					$meta_box['fields'][] = $new_field;
				}
			}

			$helper->add_meta_box($meta_box);
		}

		public function add_meta_boxes_layouts()
		{
			// Declare helper object
			$prefix = '';
			$helper = new NOO_Meta_Boxes_Helper($prefix, array('page' => $this->post_type));

			// General Info
			$meta_box = array(
				'id' => '_company_layout',
				'title' => __('Company Layout Style', 'noo'),
				'context' => 'side',
				'priority' => 'core',
				'description' => '',
				'fields' => array(
					array(
						'id' => '_layout_style',
						'label' => __('Company Display Style', 'noo'),
						'type' => 'radio',
						'std' => 'default',
						'options' => array(
							array('value' => 'default', 'label' => 'Default'),
							array('value' => 'sidebar', 'label' => 'Sidebar Information'),
							array('value' => 'full', 'label' => 'Full Information'),
						)
					),
				),
			);

			$helper->add_meta_box($meta_box);
		}

		public function add_meta_boxes_membership() {
			// Declare helper object
			$prefix = '';
			$helper = new NOO_Meta_Boxes_Helper($prefix, array('page' => 'noo_company'));

			// General Info
			$meta_box = array(
				'id' => 'employer_membership',
				'title' => __('Job Package', 'noo'),
				'context' => 'side',
				'priority' => 'default',
				'description' => '',
				'fields' => array(
					array(
						'id' => '_employer_membership',
						'label' => '',
						'type' => 'job_package_info',
						'std' => '',
						'callback' => array( $this, 'company_job_package_info' ),
					),
				),
			);

			$helper->add_meta_box($meta_box);
		}

		public function company_job_package_info( $post, $id, $type, $meta, $std, $field) {
			$employer_id = $this->get_employer_id( $post->ID );
			$package = !empty( $employer_id ) ? jm_get_job_posting_info( $employer_id ) : null;
			if( empty( $package ) ) {
				echo __( 'N/A', 'noo' );
				return;
			}

			if( isset( $package['product_id'] ) && !empty( $package['product_id'] ) ) : ?>
				<div class="company-package-info">
					<label><?php echo __('Plan','noo'); ?></label>
					<strong><a href="<?php echo get_edit_post_link( $package['product_id'] ); ?>"><?php echo get_the_title( $package['product_id'] ); ?></a></strong>
				</div>
			<?php
			endif;

			$is_unlimited = $package['job_limit'] >= 99999999;
			$job_limit_text = $is_unlimited ? __('Unlimited', 'noo') : sprintf( _n( '%d job', '%d jobs', $package['job_limit'], 'noo' ), number_format_i18n( $package['job_limit'] ) );
			$job_added = jm_get_job_posting_added( $employer_id );
			$feature_job_remain = jm_get_feature_job_remain( $employer_id );
			if( $is_unlimited || $package['job_limit'] > 0 ) :
				?>
				<div class="company-package-info">
					<label><?php _e('Job Limit','noo')?></label>
					<strong><?php echo $job_limit_text; ?></strong>
				</div>
				<div class="company-package-info">
					<label><?php _e('Job Added','noo')?></label>
					<strong><?php echo $job_added > 0 ? sprintf( _n( '%d job', '%d jobs', $job_added, 'noo' ), number_format_i18n( $job_added ) ) : __( '0 job', 'noo'); ?></strong>
				</div>
				<div class="company-package-info">
					<label><?php _e('Job Duration','noo')?></label>
					<strong><?php echo sprintf( _n( '%s day', '%s days', $package['job_duration'], 'noo' ), number_format_i18n( $package['job_duration'] ) ); ?></strong>
				</div>
			<?php endif; ?>
			<?php if( isset( $package['job_featured'] ) && !empty( $package['job_featured'] ) ) : ?>
				<div class="company-package-info">
					<label><?php _e('Featured Job limit','noo')?></label>
					<strong><?php echo sprintf( _n( '%d job', '%d jobs', $package['job_featured'], 'noo' ), number_format_i18n( $package['job_featured'] ) ); ?></strong><br/>
					<?php if( $feature_job_remain < $package['job_featured'] ) echo '&nbsp;' . sprintf( __('( %d remain )', 'noo'), $feature_job_remain ); ?>
				</div>
			<?php endif;
		}

		public function custom_enter_title( $input ) {
			global $post_type;

			if ( $this->post_type == $post_type )
				return __( 'Company Name', 'noo' );

			return $input;
		}

		public function manage_edit_columns($columns) {
			
			if ( ! is_array( $columns ) ) $columns = array();

			$before = array_slice($columns, 0, 2);
			$after = array_slice($columns, 2);
			
			$new_columns = array(
				'company_featured' => '<span class="tips" data-tip="' . __( 'Is Company Featured?', 'noo' ) . '">' . __( 'Featured?', 'noo' ) . '</span>',
				'employer_package' => __( 'Membership Package', 'noo' ),
				'job_count' => __( 'Job Count', 'noo' ),
			);

			$columns = array_merge($before, $new_columns, $after);
			return $columns;
		}

		public function manage_posts_custom_column($column) {
			global $post, $wpdb;
			switch ( $column ) {
				case "company_featured" :
					$featured = noo_get_post_meta($post->ID,'_company_featured');
					// Update old data
					if( empty( $featured ) ) update_post_meta( $post->ID, '_company_featured', 'no' );

					// $url = wp_nonce_url( admin_url( 'admin-ajax.php?action=noo_company_feature&company_id=' . $post->ID ), 'noo-company-feature' );
					// echo '<a href="' . esc_url( $url ) . '" title="'. __( 'Toggle featured', 'noo' ) . '">';
					// if ( 'yes' === $featured ) {
					// 	echo '<span class="noo-company-feature" title="'.esc_attr__('Yes','noo').'"><i class="dashicons dashicons-star-filled "></i></span>';
					// } else {
					// 	echo '<span class="noo-company-feature not-featured"  title="'.esc_attr__('No','noo').'"><i class="dashicons dashicons-star-empty"></i></span>';
					// }
					// echo '</a>';
					
					echo '<a href="javascript:void(0)" class="noo-ajax-btn" title="'. __( 'Toggle featured', 'noo' ) . '" data-action="jm_set_company_featured" data-company_id=
					"' . $post->ID . '" data-nonce="' . wp_create_nonce( 'set-company-featured' ) . '">';
					if ( 'yes' === $featured ) {
						echo '<span class="noo-company-feature" title="'.esc_attr__('Yes','noo').'"><i class="dashicons dashicons-star-filled "></i></span>';
					} else {
						echo '<span class="noo-company-feature not-featured"  title="'.esc_attr__('No','noo').'"><i class="dashicons dashicons-star-empty"></i></span>';
					}
					echo '</a>';
				
					break;
				case 'employer_package':
					$employer_id = $this->get_employer_id( $post->ID );
					$package = !empty( $employer_id ) ? jm_get_job_posting_info( $employer_id ) : null;
					if( empty( $package ) || !isset($package['product_id']) ) {
						echo __( 'N/A', 'noo' );
					} else {
						$product_id = absint( $package['product_id'] );
						echo '<a href="' . get_edit_post_link( $product_id ) . '">'.get_the_title( $product_id ).'</a>';
					}

					break;
				case 'job_count':
					$employer_id = $this->get_employer_id( $post->ID );
					$package = !empty( $employer_id ) ? jm_get_job_posting_info( $employer_id ) : null;
					if( empty( $package ) || !isset($package['job_limit']) ) {
						echo __( 'N/A', 'noo' );
					} else {
						$is_unlimited = $package['job_limit'] >= 99999999;
						$job_added = jm_get_job_posting_added( $employer_id );
						echo sprintf( __('%s of %s', 'noo'), $job_added, ( $is_unlimited ? __('Unlimited', 'noo') : absint( $package['job_limit'] ) ) );
					}

					break;
			}
		}
	
		public function add_seting_company_tab($tabs){
			$temp1 = array_slice($tabs, 0, 3);
			$temp2 = array_slice($tabs, 3);

			$company_tab = array( 'company' => __('Company','noo') );
			return array_merge($temp1, $company_tab, $temp2);
		}

		public function setting_page(){
			if(isset($_GET['settings-updated']) && $_GET['settings-updated']) {
				flush_rewrite_rules();
			}
			?>
				<?php settings_fields('noo_company'); ?>
				<h3><?php echo __('Company Options','noo')?></h3>
				<table class="form-table" cellspacing="0">
					<tbody>
						<tr>
							<th>
								<?php esc_html_e('Companies Archive base (slug)','noo')?>
							</th>
							<td>
								<?php $archive_slug = self::get_setting('archive_slug', 'companies'); ?>
								<input type="text" name="noo_company[archive_slug]" value="<?php echo ($archive_slug ? $archive_slug : 'companies') ?>">
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e('Show Company with no Jobs','noo')?>
							</th>
							<td>
								<?php $show_no_jobs = self::get_setting('show_no_jobs',1); ?>
								<input type="hidden" name="noo_company[show_no_jobs]" value="">
								<input type="checkbox" <?php checked( $show_no_jobs, '1' ); ?> name="noo_company[show_no_jobs]" value="1">
							</td>
						</tr>
						<tr>
							<th>
								<?php esc_html_e('Enable Cover Image','noo')?>
							</th>
							<td>
								<?php $cover_image = self::get_setting('cover_image', 'yes'); ?>
								<input type="hidden" name="noo_company[cover_image]" value="">
								<input type="checkbox" <?php checked( $cover_image, 'yes' ); ?> name="noo_company[cover_image]" value="yes">
								<p><small><?php echo __('Allow Employers to change cover image. This image will be displayed as header background on a company\'s page and as a default one of single job pages.','noo') ?></small></p>
							</td>
						</tr>
						<?php do_action( 'noo_setting_company_fields' ); ?>
					</tbody>
				</table>
			<?php 
		}

		public function default_company_data($post_ID = 0, $post = null, $update = false) {

			if( !$update && !empty( $post_ID ) && $post->post_type == 'noo_company' ) {
				update_post_meta( $post_ID, '_company_featured', 'no' );
			}
		}

		public function pre_get_posts( $query ) {
			if ( is_admin() || $query->is_singular ) {
				return $query;
			}

			//if is querying noo_company

			if(isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'noo_company' ) {

				add_filter( 'posts_where', array( $this, 'posts_where' ) );

				if(is_post_type_archive('noo_company')){
					$company_list_style = noo_get_option('noo_companies_style', '');
					if ($company_list_style == 'style2'){
						$noo_company_count = noo_get_option('noo_companies_style_count', 12);
						$query->query_vars['posts_per_page'] = $noo_company_count;
					} else{
						$query->query_vars['posts_per_page'] = -1;
					}

				} else{
					if(empty($query->query_vars['posts_per_page'])){
						$query->query_vars['posts_per_page'] = -1;
					}
				}

				if( !self::get_setting('show_no_jobs',1) ) {
					$query->query_vars['meta_query'][] = array(
						'key'     => '_noo_job_count',
						'value'   => 0,
						'compare' => '>',
						'type' => 'NUMERIC'
					);
				}

				$query->query_vars['orderby'] = 'title';
				$query->query_vars['order'] = 'ASC';
			}

			return $query;
		}

		public function posts_where( $where ) {
			remove_filter( current_filter(), __FUNCTION__ );
			global $wpdb;
			if ( isset( $_GET['key'] ) && !empty( $_GET['key'] ) ) {
				$first_char = esc_attr($_GET['key']);
				$where .= sprintf( " AND LOWER( SUBSTR( %s.post_title, 1, 1 ) ) = '%s' ", $wpdb->posts, strtolower( $first_char ) );
			} else {
				$where .= sprintf( " AND %s.post_title <> '' ", $wpdb->posts );
			}

			return $where;
		}


		public function search_by_title_only( $search, &$wp_query ) {
			//if is querying noo_company
			if(isset($_GET['post_type']) && $_GET['post_type'] == 'noo_company' ) {
				if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
					global $wpdb;

					$q = $wp_query->query_vars;
					$n = ! empty( $q['exact'] ) ? '' : '%';

					$search = array();

					foreach ( ( array ) $q['search_terms'] as $term ) {
						$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
					}

					$search = ' AND ' . implode( ' AND ', $search );
				}
			}

			return $search;
		}

		public function noo_companies_shortcode($atts, $content = null){
			extract(shortcode_atts(array(
				'title' => __( 'Companies', 'noo' ),
				'style' => '',
				'number' => '',
			), $atts));

			$number = (!empty($number)) ? $number : '-1';
			$paged = get_query_var('paged') ? get_query_var('paged') : 1;
			$args = array(
				'post_type'			  => 'noo_company',
				'post_status'         => 'publish',
				'posts_per_page'	  => $number,
				'orderby'			  => 'title',
				'order'				  => 'ASC',
				'paged' => $paged
			);

			$r = new WP_Query($args);
			ob_start();
			self::loop_display(array('query'=> $r, 'title'=>$title, 'style' => $style));
			$output = ob_get_clean();
			return $output;
		}

		public function noo_company_feature_shortcode($atts, $content = null){
			extract(shortcode_atts(array(
				'title'            => __( 'Featured Employer', 'noo' ),
				'posts_per_page'   => -1,
				'featured_content' => '',
				'autoplay'         => 'false',
				'slider_speed'     => '800',
			), $atts));
			wp_enqueue_script( 'vendor-carouFredSel' );
			$args = array(
				'post_type'			  => 'noo_company',
				'post_status'         => 'publish',
				'posts_per_page'	  => $posts_per_page,
				'orderby'			  => 'title',
				'order'				  => 'DESC',
			);

			$args['meta_query'][] = array(
				'key'   => '_company_featured',
				'value' => 'yes'
			);
			
			$r = new WP_Query($args);
			ob_start();
			self::loop_display(array(
				'query'            => $r, 
				'title'            => $title, 
				'style'            => 'slider', 
				'featured_content' => $content,
				'autoplay'         => $autoplay,
				'slider_speed'     => $slider_speed
			));
			$output = ob_get_clean();
			return $output;
		}

		public static function get_employer_id( $company_id = null ) {
			if( empty( $company_id ) ) return 0;

			if( isset( self::$employers[$company_id] ) ) {
				return self::$employers[$company_id];
			}

			$employers = get_users( array( 'meta_key' => 'employer_company', 'meta_value' => $company_id, 'fields' => 'id' ) );

			if( empty( $employers ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
				// Try to get the employer from the translated company
				$trid = apply_filters( 'wpml_element_trid', '', $company_id, 'post_noo_company' );
				$translations = apply_filters( 'wpml_get_element_translations_filter', $company_id, $trid, 'post_noo_company' );

				if( !empty( $translations ) ) {
					foreach ($translations as $lang => $tran_obj) {
						$maybe_empl = get_users( array( 'meta_key' => 'employer_company', 'meta_value' => $tran_obj->element_id, 'fields' => 'id' ) );
						if( !empty( $maybe_empl ) ) {
							$employers = $maybe_empl;
							break;
						}
					}
				}
			}

			if( empty( $employers ) ) {
				self::$employers[$company_id] = 0;
			} else {
				self::$employers[$company_id] = $employers[0];
			}

			return self::$employers[$company_id];
		}

		public static function count_jobs( $company_id = null ) {
			if( empty( $company_id ) ) return 0;
			global $wpdb;

			$employer = self::get_employer_id( $company_id );

			if( !defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$company_jobs = $wpdb->get_var( "SELECT COUNT( DISTINCT p.ID ) FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					WHERE p.post_type = 'noo_job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)");
			} else {
				$company_jobs = $wpdb->get_var( "SELECT COUNT( DISTINCT p.ID ) FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					LEFT JOIN {$wpdb->prefix}icl_translations AS wpml
					ON p.ID = wpml.element_id
					WHERE p.post_type = 'noo_job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)
						AND wpml.language_code = '".ICL_LANGUAGE_CODE."'"
					);
			}

			return  absint( $company_jobs );
		}

		public static function get_company_jobs( $company_id = null, $exclude_job_ids = array(), $number_of_jobs = -1, $status = 'publish' ) {
			if( empty( $company_id ) ) return array();
			global $wpdb;

			$employer = self::get_employer_id( $company_id );

			if( !defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					WHERE p.post_type = 'noo_job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)";
			} else {
				$query = "SELECT DISTINCT p.ID FROM {$wpdb->posts} AS p 
					LEFT JOIN {$wpdb->postmeta} AS pm
					ON p.ID = pm.post_id AND pm.meta_key = '_company_id'
					LEFT JOIN {$wpdb->prefix}icl_translations AS wpml
					ON p.ID = wpml.element_id
					WHERE p.post_type = 'noo_job' AND p.post_status = 'publish'
						AND ( pm.meta_value = '{$company_id}'
							OR (
								p.post_author = {$employer}
								AND ( pm.meta_value IS NULL OR pm.meta_value = '' )
							)
						)
						AND wpml.language_code = '".ICL_LANGUAGE_CODE."'";
			}

			if( !empty( $status ) && $status != 'all' ) {
				$query .= " AND p.post_status = '{$status}'";
			}

			if( !empty( $exclude_job_ids ) ) {
				$query .= " AND p.ID NOT IN ( " . implode(',', $exclude_job_ids ) . " )";
			}

			$query .= "  ORDER BY p.post_date DESC";

			if( $number_of_jobs > 0 ) {
				$query .= " LIMIT 0, {$number_of_jobs}";
			}

			return $company_jobs = $wpdb->get_col( $query );
		}

		public static function get_more_jobs( $company_id = null, $exclude_job_ids = array(), $number_of_jobs = 5 ) {
			self::get_company_jobs( $company_id, $exclude_job_ids, $number_of_jobs );
		}

		public function custom_disable_redirect_canonical( $redirect_url ){
			global $post;
			$ptype = get_post_type( $post );
			if ( $ptype == 'noo_company' ) $redirect_url = false;
			return $redirect_url;
		}

		public static function get_company_logo( $company_id = 0, $size = 'company-logo', $alt = '', $args = array() ) {
			if( empty( $company_id ) ) return '';

			$size_key = is_array( $size ) ? implode( '_', $size ) : $size;
			if( !isset( self::$companies[$company_id] ) ) {
				self::$companies[$company_id] = array();
			}

			if( !isset( self::$companies[$company_id][$size_key] ) ) {
				$class			= apply_filters( 'noo_company_logo_class', '', $company_id );
				$thumbnail_id	= noo_get_post_meta($company_id, '_logo', '');
				$size			= is_numeric( $size ) ? array( $size, $size ) : $size;
				$args['alt']	= $alt;
				$args['class']	= isset( $args['class'] ) ? $args['class'] . ' ' . $class : $class;
				$company_logo	= wp_get_attachment_image($thumbnail_id, $size, false, $args );
				if(empty($company_logo)){
					$img_size = '';
					if( is_array( $size ) ) {
						$size[1] = count( $size ) > 1 ? $size[1] : $size[0];
						$img_size = 'width="' . $size[0] . 'px" height="' . $size[1] . 'px"';
					}

					$company_logo = '<img src="'.NOO_ASSETS_URI.'/images/company-logo.png" ' . $img_size . ' class="' . $args['class'] . '" alt="' . $args['alt'] . '">';	
				}

				self::$companies[$company_id][$size_key] = $company_logo;
			}

			return apply_filters( 'noo_company_logo', self::$companies[$company_id][$size_key], $company_id );
		}

		public static function loop_display( $args = '' ) {
			$defaults = array( 
				'query'        => '', 
				'title'        => '',
				'style'        => '',
				'content'      => '',
				'autoplay'     => 'false',
				'slider_speed' => '800'
			);
			$p = wp_parse_args( $args, $defaults );
			extract($p);
			global $wp_query;
			if(!empty($query))
				$wp_query = $query;

			// ===== Option slider
				$option = array(
					'autoplay'     => $autoplay,
					'slider_speed' => $slider_speed
				);

			ob_start();
	        include(locate_template("layouts/noo_company-loop.php"));
	        echo ob_get_clean();

			wp_reset_postdata();
			wp_reset_query();
		}
		public static function display_sidebar( $company_id = null, $show_more_job = false ){

			if(empty($company_id)) {
				return;
			}

			ob_start();
	        include(locate_template("layouts/noo_company-info.php"));
	        echo ob_get_clean();

			wp_reset_postdata();
			wp_reset_query();
		}

		public static function display_sidebar_two( $company_id = null, $show_more_job = false ){

			if(empty($company_id)) {
				return;
			}

			ob_start();
			include(locate_template("layouts/noo_company-info-two.php"));
			echo ob_get_clean();

			wp_reset_postdata();
			wp_reset_query();
		}

		public function ajax_set_company_featured(){
			$result = check_ajax_referer( 'set-company-featured', 'nonce', false );

			if( $result ) {
				$post_id = ! empty( $_POST['company_id'] ) ? (int) $_POST['company_id'] : '';
	
				if ( ! $post_id || get_post_type( $post_id ) !== 'noo_company' ) {
					jm_ajax_exit();
				}
		
				$featured = noo_get_post_meta( $post_id, '_company_featured' );
		
				if ( 'yes' === $featured ) {
					update_post_meta( $post_id, '_company_featured', 'no' );
				} else {
					update_post_meta( $post_id, '_company_featured', 'yes' );
				}
			}
	
			jm_ajax_exit( '', true );
		}

		public static function get_layout($company_id =''){
			if (empty($company_id)){
				$company_id = get_the_ID();
			}

			$company_meta_layout = get_post_meta($company_id , '_layout_style', true);

			if ($company_meta_layout == 'sidebar') {
				$company_layout = '';
			} elseif($company_meta_layout == 'full') {
				$company_layout = 'two';
			} else{
				$company_layout = noo_get_option('noo_single_company_layout', '');
			}
			return $company_layout;
		}
	}

	new Noo_Company();
endif;