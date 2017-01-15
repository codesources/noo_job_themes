<?php

if ( ! function_exists( 'noo_register_upload_script' ) ) :
	
	function noo_register_upload_script() {
		$js_folder_uri = SCRIPT_DEBUG ? NOO_ASSETS_URI . '/js' : NOO_ASSETS_URI . '/js/min';
		$js_suffix     = SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'noo_plupload', $js_folder_uri . '/noo_plupload' . $js_suffix . '.js', array( 'jquery', 'plupload-all' ), null, true );

		$noo_plupload = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'remove' => wp_create_nonce('noo-plupload-remove'),
			'confirmMsg' => __('Are you sure you want to delete this?', 'noo'),
		);
		wp_localize_script('noo_plupload', 'nooPluploadL10n', $noo_plupload);
	}

	add_action( 'wp_enqueue_scripts', 'noo_register_upload_script' );

endif;

if( !function_exists( 'noo_image_upload_form_field' ) ) :
	function noo_image_upload_form_field($field_name='',$value='',$is_multiple=false) {
		if( empty( $field_name ) ) return;
		wp_enqueue_script('noo_plupload');
		$uniqid = uniqid();
		?>
		<div class="upload-btn-wrap">
			<div id="noo_upload-<?php echo $field_name; ?>-<?php echo $uniqid; ?>" class="btn btn-default">
				<i class="fa fa-folder-open-o"></i> <?php _e('Browse','noo');?>
			</div>
			<div class="noo_upload-status"></div>
		</div>
		<div id="noo_upload-<?php echo $field_name; ?>-preview" class="upload-preview-wrap">
			<?php
				noo_show_list_image_uploaded($value, $field_name);
			?>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#noo_upload-<?php echo $field_name; ?>-<?php echo $uniqid; ?>').noo_upload({
				input_name : '<?php echo $field_name; ?>',
				container : 'noo_upload-<?php echo $field_name; ?>-wrap',
				browse_button : 'noo_upload-<?php echo $field_name; ?>-<?php echo $uniqid; ?>',
				tag_thumb : 'noo_upload-<?php echo $field_name; ?>-preview',
				multi_upload : <?php echo ( $is_multiple ? "true" : "false" ); ?>
			});
		});
		</script>
		<?php
	}
endif;

if( !function_exists( 'noo_file_upload_form_field' ) ) :
	function noo_file_upload_form_field($field_name='',$extensions=array(),$value='',$is_multiple=false) {
		if( !wp_script_is( 'noo_plupload', 'registered' ) ) {
			noo_register_upload_script();
		}
		wp_enqueue_script('noo_plupload');
		
		$id = uniqid('plupload_');
		$max_upload_size = wp_max_upload_size();
		if ( ! $max_upload_size ) {
			$max_upload_size = 0;
		}
		$plupload_init = array(
			'runtimes' => 'html5,flash,html4',
			'browse_button' => $id.'_uploader-btn',
			'container' => $id.'_upload-container',
			'file_data_name' => 'file',
			'max_file_size' => $max_upload_size,
			'url' => esc_url_raw( add_query_arg( array( 'action' => 'noo_plupload', 'nonce' => wp_create_nonce('noo-plupload') ), admin_url('admin-ajax.php') ) ),
			'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
			'filters' => array(array('title' => __('Allowed Files', 'noo'), 'extensions' => implode(',', $extensions))),
			'multipart' => true,
			'urlstream_upload' => true,
			'multi_selection' => $is_multiple
		);
		$plupload_init_json = htmlspecialchars(json_encode($plupload_init), ENT_QUOTES, 'UTF-8');
		?>
		<div id="<?php echo esc_attr($id.'_upload-container'); ?>" class="noo-plupload">
			<div class="noo-plupload-btn" data-settings="<?php echo esc_attr($plupload_init_json)?>">
				<a href="#" class="btn btn-default" id="<?php echo esc_attr($id.'_uploader-btn'); ?>"><i class="fa fa-folder-open-o"></i> <?php esc_html_e('Browse','noo')?></a>
		    	<p class="help-block"><?php printf( __( 'Maximum upload file size: %s', 'noo' ), esc_html( size_format( $max_upload_size ) ) ); ?></p>
		    	<?php if( !empty( $extensions ) ) : ?>
					<p class="help-block"><?php echo sprintf( __('Allowed file: %s', 'noo'), '.' . implode(', .', $extensions) ); ?></p>
				<?php endif; ?>
			</div>
			<div class="noo-plupload-preview">
				<?php 
				$file_name = !empty($value) ? noo_json_decode( $value ) : array();
				if( !empty($file_name) ) :
					$file_name = $file_name[0];
					$trash_icon = is_admin() ? 'dashicons dashicons-trash' : 'fa fa-trash-o';
				?>
				<div>
					<a class="delete-pluploaded" data-toggle="tooltip" data-filename="<?php echo esc_attr($file_name); ?>" href="#" title="<?php _e('Delete File', 'noo'); ?>"><i class="<?php echo $trash_icon; ?>"></i>&nbsp;</a>
					<strong><?php echo esc_html($file_name); ?></strong>
				</div>
				<?php endif; ?>
			</div>
			<input type="hidden" class="noo-plupload-value" name="<?php echo esc_attr($field_name)?>" value="<?php echo esc_attr($value); ?>" >
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'jm_get_allowed_attach_file_types' ) ) :
	function jm_get_allowed_attach_file_types() {
		$settings = jm_get_resume_setting('extensions_upload_resume', 'pdf,doc,docx');

		return noo_upload_convert_extension_list( $settings );
	}

endif;

if ( ! function_exists( 'noo_show_list_image_uploaded' ) ) :
	
	function noo_show_list_image_uploaded( $thumb_id, $input_name ) {
		echo '<input type="hidden" name="' . $input_name . '" value="" />'; // blank input to make sure you always have the submitted images 
		if( !empty($thumb_id) ) {
			if ( is_array( $thumb_id ) ) {
				foreach ( $thumb_id as $img ) :
					$img = trim($img);
					$img_src = wp_get_attachment_image_src( $img, 'thumbnail' );
					if( !empty( $img ) && !empty( $img_src ) ) :
						echo '<div class="image-upload-thumb">';
						echo "<img src='{$img_src[0]}' alt='*' />";
						echo '<input type="hidden" name="' . $input_name . '[]" value="' . $img . '" class="img-' . $img . '"/>';
						echo '<a class="delete-uploaded" data-fileid="' . $img .'" href="#" title="' . __('Remove', 'noo') . '"><i class="fa fa-times-circle"></i></a></p>';
						echo '</div>';
					endif;
				endforeach;
			} else {
				$img_src = is_numeric( $thumb_id ) ? wp_get_attachment_image_src( $thumb_id, 'thumbnail' ) : '';
				$img_src = !empty( $img_src ) ? $img_src[0] : $thumb_id;
				echo '<div class="image-upload-thumb">';
				echo "<img src='{$img_src}' alt='*' />";
				echo '<input type="hidden" name="' . $input_name . '" value="' . $thumb_id . '" class="img-' . $thumb_id . '"/>';
				echo '<a class="delete-uploaded" data-fileid="' . $thumb_id .'" href="#" title="' . __('Remove', 'noo') . '"><i class="fa fa-times-circle"></i></a></p>';
				echo '</div>';
			}
		}
	}

endif;

if ( ! function_exists( 'noo_upload_convert_extension_list' ) ) :
	function noo_upload_convert_extension_list( $exts = 'pdf,doc,docx' ) {
		$exts = !empty( $exts ) ? explode(',', $exts ) : array();
		$allowed_exts = array();
		foreach ($exts as $type) {
			$type = trim($type);
			if( empty( $type ) || $type === '.' ) continue;
			$type = $type[0] === '.' ? substr( $type, 1 ) : $type;
			$allowed_exts[] = $type;
		}

		return $allowed_exts;
	}

endif;

if( !function_exists( 'noo_meta_box_field_attachment' ) ) :
	function noo_meta_box_field_attachment( $post, $id, $type, $meta, $std = null, $field = null ) {
		$extensions = isset( $field['options']['extensions'] ) && !empty( $field['options']['extensions'] ) ? $field['options']['extensions'] : 'pdf,doc,docx';
		?>
			<div class="clearfix">
		    	<?php noo_file_upload_form_field( 'noo_meta_boxes[' . $id . ']', noo_upload_convert_extension_list( $extensions ), $meta ) ?>
			</div>
		<?php
	}

	add_action( 'noo_meta_box_field_attachment', 'noo_meta_box_field_attachment', 10, 6 );
endif;


