<?php
if(!class_exists('Noo_CPT')):
	class Noo_CPT {
		public $post_type = 'noo_cpt';
		public $slug = 'cpts';
		public $prefix = 'cpt';
		public $option_key = 'noo_cpt';
		public $setting_title = '';

		/**
		 * Static property to hold our singleton instance
		 * @var nooPayPalFramework
		 */
		static $instance = false;

		public function __construct(){

			add_action( 'init', array( &$this, 'register_post_type' ),0 );
			add_filter( 'template_include', array( $this, 'template_loader' ) );

			if( is_admin() ) {
				add_action('admin_init', array(&$this,'admin_init'));

				add_action( 'add_meta_boxes', array(&$this, 'remove_meta_boxes' ), 20 );
				add_action( 'add_meta_boxes', array(&$this, 'add_meta_boxes' ), 30 );

				if( !empty( $this->option_key ) && !empty( $this->setting_title) ) {
					add_action('admin_menu', array(&$this,'settings_sub_menu'));
				}

				add_filter('manage_edit-' . $this->post_type . '_columns', array(&$this, 'manage_edit_columns'));
				add_action('manage_posts_custom_column', array(&$this, 'manage_posts_custom_column'));

				// add_filter( 'enter_title_here', array (&$this,'custom_enter_title') );

				// add_filter('months_dropdown_results', '__return_empty_array');
				// add_action( 'restrict_manage_posts', array(&$this, 'restrict_manage_posts') );
				// add_filter( 'parse_query', array(&$this, 'posts_filter') );
			}
		}

		/**
		 * If an instance exists, this returns it.  If not, it creates one and
		 * retuns it.
		 *
		 * @return nooPayPalFramework
		 */
		public static function getInstance() {
			if ( !self::$instance )
				self::$instance = new self;
			return self::$instance;
		}

		public function template_loader($template){
			if(is_post_type_archive( $this->post_type ) ){
				$template       = locate_template( "archive-{$this->post_type}.php" );
			}
			return $template;
		}

		public function admin_init() {

		}

		public function register_post_type() {
			// Sample register post type
			// $archive_slug = $this->getSetting('', 'archive_slug',$this->slug);
			// register_post_type( 
			// 	$this->post_type, 
			// 	array( 
			// 		'labels' => array( 
			// 			'name' => __( 'Customs', 'noo' ), 
			// 			'singular_name' => __( 'Custom', 'noo' ), 
			// 			'add_new' => __( 'Add New Custom', 'noo' ), 
			// 			'add_new_item' => __( 'Add Custom', 'noo' ), 
			// 			'edit' => __( 'Edit', 'noo' ), 
			// 			'edit_item' => __( 'Edit Custom', 'noo' ), 
			// 			'new_item' => __( 'New Custom', 'noo' ), 
			// 			'view' => __( 'View', 'noo' ), 
			// 			'view_item' => __( 'View Custom', 'noo' ), 
			// 			'search_items' => __( 'Search Custom', 'noo' ), 
			// 			'not_found' => __( 'No Customs found', 'noo' ), 
			// 			'not_found_in_trash' => __( 'No Customs found in Trash', 'noo' ), 
			// 			'parent' => __( 'Parent Custom', 'noo' ) ), 
			// 		'public' => true, 
			// 		'has_archive' => true, 
			// 		'menu_icon' => 'dashicons-clipboard', 
			// 		'rewrite' => array( 'slug' => $archive_slug, 'with_front' => false ), 
			// 		'supports' => array( 'title', 'editor','excerpt','thumbnail', 'comments' ), 
			// 		'can_export' => true ) );
			
			// register_taxonomy( 
			// 	$this->prefix . '_category', 
			// 	$this->post_type, 
			// 	array( 
			// 		'labels' => array( 
			// 			'name' => __( 'Custom Category', 'noo' ), 
			// 			'add_new_item' => __( 'Add New Custom Category', 'noo' ), 
			// 			'new_item_name' => __( 'New Custom Category', 'noo' ) ), 
			// 		'hierarchical' => true, 
			// 		'query_var' => true, 
			// 		'rewrite' => array( 'slug' => $this->prefix . '-category' ) ) );
		}

		public function remove_meta_boxes() {
			// Remove slug and revolution slider

			// remove_meta_box( 'slugdiv', $this->post_type, 'normal' );
			// remove_meta_box( 'mymetabox_revslider_0', $this->post_type, 'normal' );
		}

		public function add_meta_boxes() {
		}

		public function manage_edit_columns($columns) {
			return $columns;
		}

		public function manage_posts_custom_column($column) {
		}

		public function getSetting( $group = '', $id, $default = null ){
			$group = empty( $group ) ? $this->post_type : $group;
			$options = get_option($group);
			if (isset($options[$id])) {
				return $options[$id];
			}
			return $default;
		}

		public function register_settings_page() {
			// Sample setting fields
			$fields = array(
				// array(
				// 	'id' => "_image",
				// 	'label' => __( 'Your Image', 'noo' ),
				// 	'type' => 'image',
				// ),
				// array(
				// 	'type' => 'divider',
				// ),
				// array(
				// 	'id' => "_gallery_preview",
				// 	'label' => __('Preview Content', 'noo'),
				// 	'type' => 'radio',
				// 	'default' => 'featured',
				// 	'options' => array(
				// 		array(
				// 			'label' => __('Featured Image', 'noo'),
				// 			'value' => 'featured',
				// 		),
				// 		array(
				// 			'label' => __('First Image on Gallery', 'noo'),
				// 			'value' => 'first_image',
				// 		),
				// 		array(
				// 			'label' => __('Image Slideshow', 'noo'),
				// 			'value' => 'slideshow',
				// 		),
				// 	)
				// )
			);

			$this->render_settings_page( $fields );
		}

		public function settings_sub_menu() {
			//create new setting menu
			add_submenu_page('edit.php?post_type=' . $this->post_type, $this->setting_title, __('Settings', 'noo'), 'administrator', $this->post_type . '-settings', array(&$this, 'register_settings_page'));

			//call register settings function
			add_action( 'admin_init', array(&$this,'register_settings') );
		}

		public function register_settings(){
			register_setting($this->post_type, $this->option_key);
		}

		public function render_setting_field( $args = null, $option_name = '' ) {
			$defaults = array(
				'id'=>'',
				'type'=>'',
				'default'=>'',
				'options'=>array(),
				'args'=>array()
			);
			$r = wp_parse_args($args,$defaults);
			extract($r);

			if( empty( $id ) || empty( $type ) ) {
				return '';
			}
			$option_name = empty( $option_name ) ? $this->post_type : $option_name;
			$value = $this->getSetting( $option_name, $id, $default);
			$html = array();
			switch( $type ) {
				case 'text':
					$value = !empty( $value ) ? ' value="' . $value . '"' : '';
					$value = empty( $value ) && ( $default != null && $default != '' ) ? ' placeholder="' . $default . '"' : $value;
					$html[] = '<input id="'.$id.'"" type="text" name="' . $option_name . '[' . $id . ']" ' . $value . ' />';
					break;

				case 'textarea':
					$html[] = '<textarea id='.$id.' name="' . $option_name . '[' . $id . ']" placeholder="' . $default . '">' . ( $value ? $value : $default ) . '</textarea>';
					if( !empty( $desc ) ) {
						$html[] = '<p><small>' . $desc . '</small></p>';
					}
					break;

				case 'select':
					if( !is_array( $options ) ) {
						break;
					}

					$html[] ='<select id='.$id.' name="' . $option_name . '[' . $id . ']" >';
					foreach ( $options as $index => $option ) {
						$opt_value		= $option['value'];
						$opt_label		= $option['label'];
						$opt_selected	= ( $value == $opt_value ) ? ' selected="selected"' : '';

						$opt_id			= isset( $option['id'] ) ? ' '.$option['id'] : $id . '_' . $index;
						$opt_class		= isset( $option['class'] ) ? ' class="'.$option['class'].'"' : '';
						$opt_for = '';
						$html[] = '<option value="' . $opt_value  .'"' . $opt_for . $opt_class . $opt_selected . '>';
						$html[] = $opt_label;
						$html[] = '</option>';
					}
					$html[] = '</select>';
					break;
				case 'radio':
					if( !is_array( $options ) ) {
						break;
					}

					foreach ( $options as $index => $option ) {
						$opt_value		= $option['value'];
						$opt_label		= $option['label'];
						$opt_checked	= ( $value == $opt_value ) ? ' checked="checked"' : '';

						$opt_id			= isset( $option['id'] ) ? ' '.$option['id'] : $id . '_' . $index;
						$opt_for		= ' for="' . $opt_id . '"';
						$opt_class		= isset( $option['class'] ) ? ' class="'.$option['class'].'"' : '';
						$html[] = '<input id="' . $opt_id . '" type="radio" name="' . $option_name . '[' . $id . ']" value="' . $opt_value . '" class="radio"' . $opt_checked .'/>';
						$html[] = '<label' . $opt_for . $opt_class . '>' . $opt_label . '</label>';
						$html[] = '<br/>';
					}

					break;
				case 'checkbox':
					$checked = ( $value ) ? ' checked="checked"' : '';

					echo '<input type="hidden" name="' . $option_name . '[' . $id . ']" value="0" />';
					echo '<input type="checkbox" id="' . $id . '" name="' . $option_name . '[' . $id . ']" value="1"' . $checked . ' /> ';

					break;
				case 'label':
					echo '<p>' . $default . '</p>';
					break;
				case 'image':
					$html[] = '<input type="text" id='.$id.' name="' . $option_name . '[' . $id . ']" value="' . $value . '" style="margin-bottom: 5px;">';
					if(function_exists( 'wp_enqueue_media' )){
						wp_enqueue_media();
					} else{
						wp_enqueue_style('thickbox');
						wp_enqueue_script('media-upload');
						wp_enqueue_script('thickbox');
					}
					$html[] = '<br>';
					$html[] = '<input id="'.$id.'_upload" class="button button-primary" type="button" value="' . __('Select Image','noo') . '">';
					$html[] = '<input id="'.$id.'_clear" class="button" type="button" value="' . __('Clear Image','noo') . '">';
					$html[] = '<br>';
					$html[] = '<div class="noo-thumb-wrapper">';
					if(!empty($value)) {
						$html[] = '	<img alt="" src="' . $value . '">';
					}
					$html[] = '</div>';
					$html[] = '<script>';
					$html[] = 'jQuery(document).ready(function($) {';
					if ( empty ( $value ) ) {
						$html[] = '	$("#'.$id.'_clear").css("display", "none");';
					}
					$html[] = '	$("#'.$id.'_upload").on("click", function(event) {';
					$html[] = '		event.preventDefault();';
					$html[] = '		var noo_upload_btn   = $(this);';
					$html[] = '		if(wp_media_frame) {';
					$html[] = '			wp_media_frame.open();';
					$html[] = '			return;';
					$html[] = '		}';

					$html[] = '		var wp_media_frame = wp.media.frames.wp_media_frame = wp.media({';
					$html[] = '			title: "' . __( 'Select or Upload your Image', 'noo' ) . '",';
					$html[] = '			button: {';
					$html[] = '				text: "' . __( 'Select', 'noo' ) . '"';
					$html[] = '			},';
					$html[] = '			library: { type: "image" },';
					$html[] = '			multiple: false';
					$html[] = '		});';

					$html[] = '		wp_media_frame.on("select", function(){';
					$html[] = '			var attachment = wp_media_frame.state().get("selection").first().toJSON();';
					$html[] = '			noo_upload_btn.siblings("#'.$id.'").val(attachment.url);';
					$html[] = '			noo_thumb_wraper = noo_upload_btn.siblings("noo-thumb-wrapper");';
					$html[] = '			noo_thumb_wraper.html("");';
					$html[] = '			noo_thumb_wraper.append(\'<img src="\' + attachment.url + \'" alt="" />\');';
					$html[] = '			noo_upload_btn.attr("value", "' . __( 'Change Image', 'noo' ) . '");';
					$html[] = '			$("#'.$id.'_clear").css("display", "inline-block");';
					$html[] = '		});';

					$html[] = '		wp_media_frame.open();';
					$html[] = '	});';

					$html[] = '	$("#noo_donate_modal_header_clear").on("click", function(event) {';
					$html[] = '		var noo_clear_btn = $(this);';
					$html[] = '		noo_clear_btn.hide();';
					$html[] = '		$("#'.$id.'_upload").attr("value", " ' . __( 'Select Image', 'noo' ) . '");';
					$html[] = '		noo_clear_btn.siblings("#'.$id.'").val("");';
					$html[] = '		noo_clear_btn.siblings(".noo-thumb-wrapper").html("");';
					$html[] = '	});';
					$html[] = '});';
					$html[] = '</script>';

					break;
			}

			return implode("\n", $html);
		}

		public function render_settings_page( $fields = array(), $option_name ){
			if( empty( $fields ) ) {
				return;
			}

			$option_name = empty( $option_name ) ? $this->post_type : $option_name;

			?>
			<div class="wrap">
				<h2><?php echo esc_html( $this->setting_title ); ?></h2>
				<form action="options.php" method="post">
					<?php settings_fields($option_name); ?>
					<table class="form-table" cellspacing="0">
						<tbody>
							<?php foreach ( $fields as $field ) : ?>
								<tr>
									<th>
										<?php _e($field['label']); ?>
									</th>
									<td>
										<?php 
										if( isset( $field['callback'] ) ) {
											call_user_func($field['callback'], $field);
										} else {
											echo $this->render_setting_field( $field, $option_name );
										}
										if( !empty( $field['desc'] ) ) {
											echo '<p><small>' . $field['desc'] . '</small></p>';
										}
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		public function register_settings_tab_page() {
			$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
			$tabs = apply_filters( $this->post_type . '_settings_tabs_array', array());
			?>
			<div class="wrap">
				<form action="options.php" method="post">
					<h2 class="nav-tab-wrapper">
						<?php
							foreach ( $tabs as $name => $label ) {
								echo '<a href="' . admin_url( 'edit.php?post_type=' . $this->post_type . '&page='.$this->post_type . '-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
							}
						?>
					</h2>
					<?php 
					do_action( $this->post_type . '_setting_' . $current_tab );
					submit_button(__('Save Changes','noo'));
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Function to add tab setting
		 */
		public function render_settings_tab( $fields = array(), $option_name = '' ){
			if( empty( $fields ) ) {
				return;
			}

			$option_name = empty( $option_name ) ? $this->post_type : $option_name;

			settings_fields($option_name);
			?>
			<table class="form-table" cellspacing="0">
				<tbody>
					<?php foreach ( $fields as $field ) : ?>
						<tr>
							<th>
								<?php esc_html_e($field['label']); ?>
							</th>
							<td>
								<?php 
								if( isset( $field['callback'] ) ) {
									call_user_func($field['callback'], $field);
								} else {
									echo $this->render_setting_field( $field, $option_name );
								}
								if( !empty( $field['desc'] ) ) {
									echo '<p><small>' . $field['desc'] . '</small></p>';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		}

		protected function _ajax_exit( $message = '', $success = false, $redirect = '' ) {
			$response = array(
				'success' => $success,
				'message' => $message,
			);

			if( !empty( $redirect ) ) {
				$response['redirect'] = $redirect;
			}

			echo json_encode($response);
			exit();
		}
	}
endif;