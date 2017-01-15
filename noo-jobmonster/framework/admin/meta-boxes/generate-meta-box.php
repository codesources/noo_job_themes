<?php
/**
 * NOO Meta-Boxes Package
 *
 * NOO Meta-Boxes Register Function
 * This file register add_meta_boxes and save_post actions.
 *
 * @package    NOO Framework
 * @subpackage NOO Meta-Boxes
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */

if( !defined('JSON_UNESCAPED_UNICODE') ) {
	define( 'JSON_UNESCAPED_UNICODE', 256 );
}

// Create meta box base on inputted value
function noo_create_meta_box( $post, $meta_box ) {

	if ( ! is_array( $meta_box ) )
		return false;

	$prefix = '_noo_wp_post';

	if ( isset( $meta_box['description'] ) && $meta_box['description'] != '' )
		echo '<p>' . $meta_box['description'] . '</p>';

	wp_nonce_field( basename( __FILE__ ), 'noo_meta_box_nonce' );

	foreach ( $meta_box['fields'] as $field ) {

		if ( !isset( $field['type'] ) || empty( $field['type'] ) )
			continue;

		// If it's divider, add a hr
		if( $field['type'] == 'divider') {
			echo '<hr/>';
			continue;
		}

		if ( !isset( $field['id'] ) || empty( $field['id'] ) )
			continue;

		$id  = esc_attr($field['id']);
		$meta = noo_get_post_meta( $post->ID, $id );

		$label = isset( $field['label'] ) && !empty( $field['label'] ) ? '<strong>' . esc_html($field['label']) . '</strong>' : '';
		$std = isset( $field['std'] ) ? esc_attr($field['std']) : '';
		$class = empty($label) && isset( $meta_box['context'] ) && ( $meta_box['context'] == 'side' ) ? '' : 'noo-control ';
		$class = isset( $field['class'] ) && !empty( $field['class'] ) ? ' class="' . $class . esc_attr($field['class']) . '"' : ' class="' . $class . '"';
		$value = '';

		echo '<div class="noo-form-group ' . $id . '">';

		if( $field['type'] != 'checkbox' || $meta_box['context'] != 'side' ) {
			if(!empty($label)){
				echo '<label for="' . $field['id'] . '">'.$label;
				if ( isset( $field['desc'] ) && !empty( $field['desc'] ) )
					echo '<div class="field-desc">' . esc_html($field['desc']) . '</div>';
				echo '</label>';
			}
		} else {
			$field['inline_label'] = true;
		}

		echo '<div ' . $class . '>';

		$params = apply_filters( 'noo_meta_box_field_params', compact( 'id', 'meta', 'std', 'field' ), $post );
		extract($params);
		
		if( isset($field['callback']) && !empty($field['callback']) ) {
			call_user_func($field['callback'], $post, $id, $field['type'], $meta, $std, $field);
		} else {
			noo_render_metabox_fields( $post, $id, $field['type'], $meta, $std, $field );
		}

		echo '</div>'; // div.noo-control
		echo '</div>'; // div.noo-form-group

	} // foreach - $meta_box['fields']
} // function - noo_create_meta_box

function noo_render_metabox_fields ( $post, $id, $type, $meta, $std, $field = null ) {
	switch( $type ) {
		case 'text':
			$value = $meta ? ' value="' . $meta . '"' : '';
			$value = empty( $value ) && ( $std != null && $std != '' ) ? ' placeholder="' . $std . '"' : $value;
			echo '<input id='.$id.' type="text" name="noo_meta_boxes[' . $id . ']" ' . $value . ' />';
			break;

		case 'textarea':
			echo '<textarea id='.$id.' name="noo_meta_boxes[' . $id . ']" placeholder="' . $std . '">' . ( $meta ? $meta : $std ) . '</textarea>';
			break;

		case 'gallery':
			$meta = $meta ? $meta : $std;
			$output = '';
			if ( $meta != '' ) {
				$image_ids = explode( ',', $meta );
				foreach ( $image_ids as $image_id ) {
					$output .= wp_get_attachment_image( $image_id, 'thumbnail');
				}
			}

			$btn_text = !empty( $meta ) ? __( 'Edit Gallery', 'noo' ) : __( 'Add Images', 'noo' );
			echo '<input type="hidden" name="noo_meta_boxes[' . $id . ']" id="' . $id . '" value="' . $meta . '" />';
			echo '<input type="button" class="button button-primary" name="' . $id . '_button_upload" id="' . $id . '_upload" value="' . $btn_text . '" />';
			echo '<input type="button" class="button" name="' . $id . '_button_clear" id="' . $id . '_clear" value="' . __( 'Clear Gallery', 'noo' ) . '" />';
			echo '<div class="noo-thumb-wrapper">' . $output . '</div>';
?>
			<script>
				jQuery(document).ready(function($) {

					// gallery state: add new or edit.
					var gallery_state = '<?php echo empty ( $meta ) ? 'gallery-library' : 'gallery-edit'; ?>';

					// Hide the Clear Gallery button if there's no image.
					<?php if ( empty ( $meta ) ) : ?> $('#<?php echo esc_attr($id); ?>_clear').hide(); <?php endif; ?>

					$('#<?php echo esc_attr($id); ?>_upload').on('click', function(event) {
						event.preventDefault();

						var noo_upload_btn   = $(this);

						// if media frame exists, reopen
						if(wp_media_frame) {
							wp_media_frame.setState(gallery_state);
							wp_media_frame.open();
							return;
						}

						// create new media frame
						// I decided to create new frame every time to control the Library state as well as selected images
						var wp_media_frame = wp.media.frames.wp_media_frame = wp.media({
							title: 'NOO Gallery', // it has no effect but I really want to change the title
							frame: "post",
							toolbar: 'main-gallery',
							state: gallery_state,
							library: { type: 'image' },
							multiple: true
						});

						// when open media frame, add the selected image to Gallery
						wp_media_frame.on('open',function() {
							var selected_ids = noo_upload_btn.siblings('#<?php echo esc_attr($id); ?>').val();
							if (!selected_ids)
								return;
							selected_ids = selected_ids.split(',');
							var library = wp_media_frame.state().get('library');
							selected_ids.forEach(function(id) {
								attachment = wp.media.attachment(id);
								attachment.fetch();
								library.add( attachment ? [ attachment ] : [] );
							});
						});

						// when click Insert Gallery, run callback
						wp_media_frame.on('update', function(){

							var library = wp_media_frame.state().get('library');
							var images	= [];
							var noo_thumb_wraper = noo_upload_btn.siblings('.noo-thumb-wrapper');
							noo_thumb_wraper.html('');

							library.map( function( attachment ) {
								attachment = attachment.toJSON();
								images.push(attachment.id);
								noo_thumb_wraper.append('<img src="' + attachment.url + '" alt="" />');
							});

							gallery_state = 'gallery-edit';

							noo_upload_btn.siblings('#<?php echo esc_attr($id); ?>').val(images.join(','));

							noo_upload_btn.attr('value', '<?php echo __( 'Edit Gallery', 'noo' ); ?>');
							$('#<?php echo esc_attr($id); ?>_clear').css('display', 'inline-block');
						});

						// open media frame
						wp_media_frame.open();
					});

					// Clear button, clear all the images and reset the gallery
					$('#<?php echo esc_attr($id); ?>_clear').on('click', function(event) {
						gallery_state = 'gallery-library';
						var noo_clear_btn = $(this);
						noo_clear_btn.hide();
						$('#<?php echo esc_attr($id); ?>_upload').attr('value', '<?php echo __( 'Add Images', 'noo' ); ?>');
						noo_clear_btn.siblings('#<?php echo esc_attr($id); ?>').val('');
						noo_clear_btn.siblings('#<?php echo esc_attr($id); ?>_ids').val('');
						noo_clear_btn.siblings('.noo-thumb-wrapper').html('');
					});
				});
			</script>

			<?php
			break;
		case 'application_upload':
		case 'media':
			if(function_exists( 'wp_enqueue_media' )){
				wp_enqueue_media();
			}else{
				wp_enqueue_style('thickbox');
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
			}
			$val = $meta ? $meta : $std;
			$btn_text = !empty( $val ) ? __( 'Change File', 'noo' ) : __( 'Select File', 'noo' );
			echo '<input type="text" name="noo_meta_boxes[' . $id . ']" id="' . $id . '" value="' . ( $meta ? $meta : $std ) . '" style="margin-bottom:10px" />';
			echo '<input type="button" class="button button-primary" name="' . $id . '_button_upload" id="' . $id . '_upload" value="' . $btn_text . '" />';
			echo '<input type="button" class="button" name="' . $id . '_button_clear" id="' . $id . '_clear" value="' . __( 'Clear File', 'noo' ) . '" />';
			?>
			<script>
				jQuery(document).ready(function($) {

					<?php if ( empty ( $meta ) ) : ?> $('#<?php echo esc_attr($id); ?>_clear').css('display', 'none'); <?php endif; ?>

					$('#<?php echo esc_attr($id); ?>_upload').on('click', function(event) {
						event.preventDefault();

						var noo_upload_btn   = $(this);

						// if media frame exists, reopen
						if(wp_media_frame) {
			                wp_media_frame.open();
			                return;
			            }

						// create new media frame
						// I decided to create new frame every time to control the selected images
						var wp_media_frame = wp.media.frames.wp_media_frame = wp.media({
							title: "<?php echo __( 'Select or Upload your File', 'noo' ); ?>",
							button: {
								text: "<?php echo __( 'Select', 'noo' ); ?>"
							},
							<?php if($type == 'media'):?>
							library: { type: 'video,audio' },
							<?php endif;?>
							<?php if($type == 'application_upload'):?>
							library: { type: 'application' },
							<?php endif;?>
							multiple: false
						});

						// when image selected, run callback
						wp_media_frame.on('select', function(){
							var attachment = wp_media_frame.state().get('selection').first().toJSON();
							noo_upload_btn.siblings('#<?php echo esc_attr($id); ?>').val(attachment.url);
							noo_upload_btn.attr('value', '<?php echo __( 'Change File', 'noo' ); ?>');
							$('#<?php echo esc_attr($id); ?>_clear').css('display', 'inline-block');
						});

						// open media frame
						wp_media_frame.open();
					});

					$('#<?php echo esc_attr($id); ?>_clear').on('click', function(event) {
						var noo_clear_btn = $(this);
						noo_clear_btn.hide();
						$('#<?php echo esc_attr($id); ?>_upload').attr('value', '<?php echo __( 'Select File', 'noo' ); ?>');
						noo_clear_btn.siblings('#<?php echo esc_attr($id); ?>').val('');
					});
				});
			</script>
			<?php
		break;
		case 'image':
			if(function_exists( 'wp_enqueue_media' )){
				wp_enqueue_media();
			}else{
				wp_enqueue_style('thickbox');
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
			}
			$image_id = $meta ? $meta : $std;
			$image = wp_get_attachment_image( $image_id, 'thumbnail');
			$output = !empty( $image_id ) ? $image : '';
			$btn_text = !empty( $image_id ) ? __( 'Change Image', 'noo' ) : __( 'Select Image', 'noo' );
			echo '<input type="hidden" name="noo_meta_boxes[' . $id . ']" id="' . $id . '" value="' . ( $meta ? $meta : $std ) . '" />';
			echo '<input type="button" class="button button-primary" name="' . $id . '_button_upload" id="' . $id . '_upload" value="' . $btn_text . '" />';
			echo '<input type="button" class="button" name="' . $id . '_button_clear" id="' . $id . '_clear" value="' . __( 'Clear Image', 'noo' ) . '" />';
			echo '<div class="noo-thumb-wrapper">' . $output . '</div>';
?>
			<script>
				jQuery(document).ready(function($) {

					<?php if ( empty ( $meta ) ) : ?> $('#<?php echo esc_attr($id); ?>_clear').css('display', 'none'); <?php endif; ?>

					$('#<?php echo esc_attr($id); ?>_upload').on('click', function(event) {
						event.preventDefault();

						var noo_upload_btn   = $(this);

						// if media frame exists, reopen
						if(wp_media_frame) {
			                wp_media_frame.open();
			                return;
			            }

						// create new media frame
						// I decided to create new frame every time to control the selected images
						var wp_media_frame = wp.media.frames.wp_media_frame = wp.media({
							title: "<?php echo __( 'Select or Upload your Image', 'noo' ); ?>",
							button: {
								text: "<?php echo __( 'Select', 'noo' ); ?>"
							},
							library: { type: 'image' },
							multiple: false
						});

						// when open media frame, add the selected image
						wp_media_frame.on('open',function() {
							var selected_id = noo_upload_btn.siblings('#<?php echo esc_attr($id); ?>').val();
							if (!selected_id)
								return;
							var selection = wp_media_frame.state().get('selection');
							var attachment = wp.media.attachment(selected_id);
							attachment.fetch();
							selection.add( attachment ? [ attachment ] : [] );
						});

						// when image selected, run callback
						wp_media_frame.on('select', function(){
							var attachment = wp_media_frame.state().get('selection').first().toJSON();
							noo_upload_btn.siblings('#<?php echo esc_attr($id); ?>').val(attachment.id);

							noo_thumb_wraper = noo_upload_btn.siblings('.noo-thumb-wrapper');
							noo_thumb_wraper.html('');
							noo_thumb_wraper.append('<img src="' + attachment.url + '" alt="" />');

							noo_upload_btn.attr('value', '<?php echo __( 'Change Image', 'noo' ); ?>');
							$('#<?php echo esc_attr($id); ?>_clear').css('display', 'inline-block');
						});

						// open media frame
						wp_media_frame.open();
					});

					$('#<?php echo esc_attr($id); ?>_clear').on('click', function(event) {
						var noo_clear_btn = $(this);
						noo_clear_btn.hide();
						$('#<?php echo esc_attr($id); ?>_upload').attr('value', '<?php echo __( 'Select Image', 'noo' ); ?>');
						noo_clear_btn.siblings('#<?php echo esc_attr($id); ?>').val('');
						noo_clear_btn.siblings('.noo-thumb-wrapper').html('');
					});
				});
			</script>

			<?php
			break;

		case 'select':
			$is_multiple = isset($field['multiple']) && $field['multiple'];
			$multiple = $is_multiple ? 'multiple="multiple"' : '';
			
			if( $is_multiple && !is_array($meta)) {
				$meta = $meta ? noo_json_decode($meta) : ( is_array( $std ) ? $std : array( $std ) );
			} else {
				$meta = $meta ? $meta : $std;
			}
			$name = 'name="noo_meta_boxes[' . $id . ']' . ( $is_multiple ? '[]' : '' ) . '"';
			$placeholder = sprintf(__('- Select %s -', 'noo'), ( isset( $field['label'] ) ? $field['label'] : '' ) );
			$chosen_class = ( count( $field['options'] ) > 10 || $is_multiple ) ? 'noo-admin-chosen' : '';
			$chosen_class .= !empty( $chosen_class ) && is_rtl() ? ' chosen-rtl' : '';

			$chosen_attr = !empty( $chosen_class ) ? 'class="' . $chosen_class . '" data-placeholder="'.$placeholder.'"' : '';

			echo'<select id='.$id.' '.$name.' '.$multiple.' '.$chosen_attr.'>';
			if( isset( $field['options'] ) && !empty( $field['options'] ) ) {
				if( !$is_multiple ) {
					echo !empty( $chosen_attr ) ? '<option value=""></option>' : '<option value="">' . $placeholder . '</option>';
				}
				foreach ( $field['options'] as $option ) {
					$opt_value  = @$option['value'];
					$opt_label  = @$option['label'];
					echo '<option';
					echo ' value="'.esc_attr($opt_value).'"';
					if ( $meta == $opt_value || ( is_array($meta) && in_array($opt_value, $meta)) ) echo ' selected="selected"';
					echo '>' . esc_html($opt_label) . '</option>';
				}
			}
			echo '</select>';
			break;

		case 'radio':
			$meta = $meta ? $meta : $std;
			if( isset( $field['options'] ) && !empty( $field['options'] ) ) {
				foreach ( $field['options'] as $index => $option ) {
					$opt_value  = $option['value'];
					$opt_label  = $option['label'];
					$opt_checked = '';

					if ( $meta == $opt_value ) $opt_checked = ' checked="checked"';

					$opt_id   = isset( $option['id'] ) ? ' '.$option['id'] : $id . '_' . $index;
					$opt_value_for = ' for="' . $opt_id . '"';
					$opt_class  = isset( $option['class'] ) ? ' class="'.$option['class'].'"' : '';
					echo '<input id="' . $opt_id . '" type="radio" name="noo_meta_boxes[' . $id . ']" value="' . $opt_value . '" class="radio"' . $opt_checked .'/>';
					echo '<label' . $opt_value_for . $opt_class . '>' . $opt_label . '</label>';
					echo '<br/>';
				}
			}

			if ( !empty( $field['child-boxes'] ) && is_array( $field['child-boxes'] ) ) :
				$child_boxes = $field['child-boxes'];
?>
        <script>
          jQuery(document).ready(function($) {
            <?php
			foreach ( $child_boxes as $option_value => $boxes ) :
				if ( empty( $boxes ) ) continue;
				$boxes = explode( ',', $boxes );
			foreach ( $boxes as $child_box ) :
				if ( trim( $child_box ) == "" ) continue;
?>
                $('#<?php echo trim( $child_box ); ?>').addClass('child_<?php echo esc_attr($id); ?> val_<?php echo esc_attr($option_value); ?>');
                $('label[for="<?php echo trim( $child_box ); ?>-hide"]').addClass('child_<?php echo esc_attr($id); ?> val_<?php echo esc_attr($option_value); ?>');
                <?php
			endforeach;
			endforeach;
?>

			$('.child_<?php echo esc_attr($id); ?>').hide();
			var parentField    = $('.<?php echo esc_attr($id); ?>');
			var checkedElement = parentField.find('input:checked');
			$('.child_<?php echo esc_attr($id); ?>.val_' + checkedElement.val()).show();

			parentField.find('input').click( function() {
				$this = $(this);
				$('.child_<?php echo esc_attr($id); ?>').hide();
				$('.child_<?php echo esc_attr($id); ?>.val_' + $this.val()).show();
            });
          });
        </script>
        <?php endif;

			if ( !empty( $field['child-fields'] ) && is_array( $field['child-fields'] ) ) :
				$child_fields = $field['child-fields'];
?>
        <script>
          jQuery(document).ready(function($) {
            <?php
			foreach ( $child_fields as $option_value => $fields ) :
				if ( empty( $fields ) ) continue;
				$fields = explode( ',', $fields );
			foreach ( $fields as $child_field ) :
				if ( trim( $child_field ) == "" ) continue;
?>
                $('.<?php echo trim( $child_field ); ?>').addClass('child_<?php echo esc_attr($id); ?> val_<?php echo esc_attr($option_value); ?>');
                <?php
			endforeach;
			endforeach;
?>

			$('.child_<?php echo esc_attr($id); ?>').hide();
			var parentField    = $('.<?php echo esc_attr($id); ?>');
			var checkedElement = parentField.find('input:checked');
			$('.child_<?php echo esc_attr($id); ?>.val_' + checkedElement.val()).show();

			parentField.find('input').click( function() {
				$this = $(this);
				$('.child_<?php echo esc_attr($id); ?>').hide();
				$('.child_<?php echo esc_attr($id); ?>.val_' + $this.val()).show();
			});
        });
        </script>
        <?php endif;
			break;

		case 'checkbox':
			$opt_value = '';
			
			if ( $meta === null || $meta === '' ) {
				if ( $std && $std !== 'off' )
					$opt_value = ' checked="checked"';				
			} else {
				if ( $meta && $meta !== 'off' )
					$opt_value = ' checked="checked"';
			}

			echo '<input type="hidden" name="noo_meta_boxes[' . $id . ']" value="0" />';
			if( isset($field['inline_label']) && $field['inline_label'] ) {
				echo '<label>';
				echo '<input type="checkbox" id="' . $id . '" name="noo_meta_boxes[' . $id . ']" value="1"' . $opt_value . ' /> ';
				echo ( isset( $field['label'] ) && !empty( $field['label'] ) ? '<strong>' . $field['label'] . '</strong>' : '' );
				echo '</label>';
			} else {
				echo '<input type="checkbox" id="' . $id . '" name="noo_meta_boxes[' . $id . ']" value="1"' . $opt_value . ' /> ';
			}

			if ( !empty( $field['child-fields'] ) && is_array( $field['child-fields'] ) ) :
				$child_fields = $field['child-fields'];
?>
	        <script>
	          jQuery(document).ready(function($) {
	            <?php
			if ( isset( $child_fields['on'] ) ) :
				$fields = explode( ',', $child_fields['on'] );
			foreach ( $fields as $child_field ) :
				if ( trim( $child_field ) == "" ) continue;
?>
	                $('.<?php echo trim( $child_field ); ?>').addClass('child_<?php echo esc_attr($id); ?> val_on');
	                <?php
			endforeach;
			endif;

			if ( isset( $child_fields['off'] ) ) :
				$fields = explode( ',', $child_fields['off'] );
			foreach ( $fields as $child_field ) :
				if ( trim( $child_field ) == "" ) continue;
?>
	                $('.<?php echo trim( $child_field ); ?>').addClass('child_<?php echo esc_attr($id); ?> val_off');
	                <?php
			endforeach;
			endif;
?>
				$('.child_<?php echo esc_attr($id); ?>').hide();
				var checkboxEl    = $('.<?php echo esc_attr($id); ?>').find('input:checkbox');
				if(checkboxEl.is( ':checked' )) {
					$('.child_<?php echo esc_attr($id); ?>.val_on').show();
				} else {
					$('.child_<?php echo esc_attr($id); ?>.val_off').show();
				}

				checkboxEl.click( function() {
					$this = $(this);
					$('.child_<?php echo esc_attr($id); ?>').hide();
					if($this.is( ':checked' )) {
						$('.child_<?php echo esc_attr($id); ?>.val_on').show();
					} else {
						$('.child_<?php echo esc_attr($id); ?>.val_off').show();
					}
				});
			});
	        </script>
        	<?php endif;
			break;

		case 'multiple_checkbox':

			$meta = $meta ? noo_json_decode($meta) : ( is_array( $std ) ? $std : array( $std ) );
			if( isset( $field['options'] ) && !empty( $field['options'] ) ) {
				foreach ( $field['options'] as $index => $option ) {
					$opt_value  = $option['value'];
					$opt_label  = $option['label'];
					$opt_checked = in_array($opt_value, $meta) ? ' checked="checked"' : '';

					$opt_id   = isset( $option['id'] ) ? ' '.$option['id'] : $id . '_' . $index;
					$opt_value_for = ' for="' . $opt_id . '"';
					$opt_class  = isset( $option['class'] ) ? ' class="'.$option['class'].'"' : '';

					echo '<label' . $opt_value_for . $opt_class . '>';
					echo '<input type="checkbox" id="' . $opt_id . '" name="noo_meta_boxes[' . $id . '][]" value="' . $opt_value . '" ' . $opt_checked . ' />';
					echo ( isset( $option['label'] ) && !empty( $option['label'] ) ? '<strong>' . $option['label'] . '</strong>' : '' );
					echo '</label>';
					echo '<br/>';
				}
			}


			break;

		case 'label':
			$value = empty( $meta ) && ( $std != null && $std != '' ) ? $std : $meta;
			echo '<label id='.$id.' >'. $value . '</label>';
			break;

		case 'page_layout':
			$post_layout = noo_get_option('noo_blog_post_layout', 'same_as_blog');
			$sidebar = '';
			if ($post_layout == 'same_as_blog') {
				$post_layout = noo_get_option( 'noo_blog_layout', 'sidebar' );
				$sidebar = noo_get_option('noo_blog_sidebar', 'sidebar-main');
			} else {
				$sidebar = noo_get_option('noo_blog_post_sidebar', 'sidebar-main');
			}

			$post_layout_text = '';
			switch( $post_layout ) {
				case 'fullwidth':
					$post_layout_text = __( 'Full-Width Page', 'noo' );
					break;
				case 'sidebar':
					$post_layout_text = __( 'Page With Right Sidebar', 'noo' );
					break;
				case 'left_sidebar':
					$post_layout_text = __( 'Page With Left Sidebar', 'noo' );
					break;
			}
			
			echo '<p>' . sprintf( __( 'Global setting for the Layout of Single Post page is: <strong>%s</strong>', 'noo'), $post_layout_text ) . '</p>';
			if ( $post_layout != 'fullwidth' ) {
				$sidebar_text = get_sidebar_name( $sidebar );
				echo '<p>' . sprintf( __( 'And the Sidebar is: <strong>%s</strong>', 'noo'), $sidebar_text ) . '</p>';
			}

			break;

		case 'sidebars':
			$meta = !empty($meta) ? $meta : $std;
			$widget_list = smk_get_all_sidebars();
			echo'<select name="noo_meta_boxes[' . $id . ']" >';
			foreach ( $widget_list as $widget_id => $name ) {
				echo'<option value="' . $widget_id . '"';
				if ( $meta == $widget_id ) echo ' selected="selected"';
				echo '>' . $name . '</option>';
			}
			echo '</select>';

			break;

		case 'menus':
			$meta = !empty($meta) ? $meta : $std;
			$menu_list = get_terms('nav_menu');

			echo'<select name="noo_meta_boxes[' . $id . ']" >';
			echo'	<option value="" '. selected( $meta, '', true ) . '>' . __('Don\'t Need Menu', 'noo') . '</option>';
			foreach ( $menu_list as $menu ) {
				echo'<option value="' . $menu->term_id . '"';
				selected( $meta, $menu->term_id, true );
				echo '>' . $menu->name . '</option>';
			}
			echo '</select>';

			break;

		case 'users':
			$meta = !empty($meta) ? $meta : $std;
			$user_list = get_users();

			echo'<select name="noo_meta_boxes[' . $id . ']" >';
			echo'	<option value="" '. selected( $meta, '', true ) . '>' . __('No User', 'noo') . '</option>';
			foreach ( $user_list as $user ) {
				echo'<option value="' . $user->id . '"';
				selected( $meta, $user->id, true );
				echo '>' . $user->display_name . '</option>';
			}
			echo '</select>';

			break;

		case 'pages':
			$meta = !empty($meta) ? $meta : $std;
			$dropdown = wp_dropdown_pages(
				array(
					'name'              => 'noo_meta_boxes[' . $id . ']',
					'echo'              => 0,
					'show_option_none'  => ' ',
					'option_none_value' => '',
					'selected'          => $meta,
				)
			);

			echo $dropdown;

		case 'datepicker':
		case 'datetimepicker':
			wp_enqueue_script( 'vendor-datetimepicker' );
			wp_enqueue_style( 'vendor-datetimepicker' );
			$date_format = get_option('date_format');
			if( $type == 'datetimepicker' ) {
				$date_format = $date_format . ' ' . get_option('time_format');
			}

			$meta = is_numeric( $meta ) ? $meta : strtotime( $meta );
			$date_text = !empty( $meta ) ? date_i18n( $date_format, $meta ) : '';

			echo '<div>';
			echo '<input type="text" readonly class="input_text" name="noo_meta_boxes[' . $id . ']" id="' . $id . '" value="' .
				 esc_attr( $date_text ) . '" /> ';
			echo '<input type="hidden" name="noo_meta_boxes[' . $id . ']" value="' .
				 esc_attr( $meta ) . '" /> ';
			echo '</div>';
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('#<?php echo esc_js($id); ?>').datetimepicker({
						format:"<?php echo esc_html( $date_format ); ?>",
						step:15,
						<?php if( $type == 'datepicker' ) : ?>
						timepicker:false,
						<?php endif; ?>
						onChangeDateTime:function(dp,$input){
							$input.next('input[type="hidden"]').val(parseInt(dp.getTime()/1000)-60*dp.getTimezoneOffset());
						}
					});
				});
			</script>
			<?php
			break;

		case 'rev_slider':
			$rev_slider = new RevSlider();
			$sliders    = $rev_slider->getArrSliders();
			echo '<select name="noo_meta_boxes[' . $id . ']">';
			echo '<option value="">' . __(' - No Slider - ', 'noo') . '</option>';
			foreach ( $sliders as $slider ) {
				echo '<option value="' . $slider->getAlias() . '"';
				if ( $meta == $slider->getAlias() ) echo ' selected="selected"'; 
				echo '>' . $slider->getTitle() . '</option>';
			}
			echo '</select>';

			break;

		default:
			do_action( 'noo_meta_box_field_' . $type, $post, $id, $type, $meta, $std, $field );
			break;
		} // switch - $field['type']
}


// Save the Post Meta Boxes
function noo_save_meta_box( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	if ( ! isset( $_POST['noo_meta_boxes'] ) || ! isset( $_POST['noo_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['noo_meta_box_nonce'], basename( __FILE__ ) ) )
		return;

	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) return;
	} else {
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	}

	foreach ( $_POST['noo_meta_boxes'] as $key=>$val ) {
		$val = apply_filters( 'noo_sanitize_meta_' . $key, $val );
		if( is_array( $val ) ) {
			$count = count( $val );
			for( $index = 0; $index < $count; $index++ ) {
				$val[$index] = addcslashes( stripslashes( $val[$index] ), '"' );
			}
			$val = json_encode($val, JSON_UNESCAPED_UNICODE);
		} else {
			$val = stripslashes( $val );
		}
		update_post_meta( $post_id, $key, $val );
	}
	do_action('noo_save_meta_box',$post_id);
}

add_action( 'save_post', 'noo_save_meta_box' );

if (!function_exists('noo_json_decode')) :
	function noo_json_decode( $json_str = '' ) {
		if( is_array( $json_str ) ) return $json_str;
		if( !is_string($json_str) ) return array( $json_str );
		$maybe_json = json_decode($json_str, true);
		
		if( !is_array( $maybe_json ) ) {
			if( $json_str == '""' ) return array();
			return array( $json_str );
		}
		
		return $maybe_json;
	}
endif;