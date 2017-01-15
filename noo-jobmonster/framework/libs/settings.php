<?php
if( !function_exists( 'noo_get_setting' ) ) :
	function noo_get_setting($group, $id = null ,$default = null){
		global $noo_setting_group;
		if(!isset($noo_setting_group[$group])){
			$noo_setting_group[$group] = get_option($group);
		}
		$group_setting_value = $noo_setting_group[$group];
		if(empty($id)) {
			return $group_setting_value;
		}

		if(isset($group_setting_value[$id])) {
			return $group_setting_value[$id];
		}

		return $default;
	}
endif;

if( !function_exists( 'noo_render_setting_form' ) ) :
	function noo_render_setting_form( $fields = array(), $option_group = '', $title = '' ){
		if( empty( $fields ) || !is_array( $fields ) || empty( $option_group ) ) {
			return;
		}

		settings_fields($option_group);
		?>
		<?php if( !empty( $title ) ) : ?>
			<h3><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>
		<table class="form-table" cellspacing="0">
			<tbody>
				<?php foreach ( $fields as $field ) : ?>
					<tr class="<?php echo $field['id']; ?>">
						<th>
							<?php esc_html_e($field['label']); ?>
							<?php if( isset( $field['label_desc'] ) && !empty( $field['label_desc'] ) ) : ?>
								<p><small><?php esc_html_e($field['label_desc']); ?></small></p>
							<?php endif; ?>
						</th>
						<td>
							<?php 
							if( isset( $field['callback'] ) ) {
								call_user_func($field['callback'], $field);
							} else {
								echo noo_render_setting_field( $field, $option_group );
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
endif;

if( !function_exists( 'noo_render_setting_field' ) ) :
	function noo_render_setting_field( $args = null, $option_group = '' ) {
		$defaults = array(
			'id'=>'',
			'type'=>'',
			'default'=>'',
			'options'=>array(),
			'value' => null,
			'class' => '',
			'echo' => false,
		);
		$r = wp_parse_args($args, $defaults);
		extract($r);

		if( empty( $id ) || empty( $type ) ) {
			return '';
		}
		$value = empty( $value ) ? ( !empty( $option_group ) ? noo_get_setting( $option_group, $id, $default) : get_option( $id, $default ) ) : $value;
		$option_name = !empty( $option_group ) ? $option_group . '[' . $id . ']' : $id;

		$html = array();
		switch( $type ) {
			case 'text':
			case 'number':
			case 'url':
				$value = ( $value !== null && $value !== false ) ? ' value="' . $value . '"' : '';
				$value = empty( $value ) && ( $default != null && $default != '' ) ? ' placeholder="' . $default . '"' : $value;
				$html[] = '<input id="'.$id.'" type="text" name="' . $option_name . '"' . $value . ' class="' . $class . '" />';
				break;

			case 'textarea':
				$html[] = '<textarea id='.$id.' name="' . $option_name . '" placeholder="' . $default . '" class="' . $class . '">' . ( $value ? $value : $default ) . '</textarea>';
				if( !empty( $desc ) ) {
					$html[] = '<p><small>' . $desc . '</small></p>';
				}
				break;

			case 'select':
				if( !is_array( $options ) ) {
					break;
				}

				$html[] ='<select id='.$id.' name="' . $option_name . '" class="' . $class . '">';
				foreach ( $options as $opt_value => $opt_label ) {
					$opt_selected	= ( $value == $opt_value ) ? ' selected="selected"' : '';

					$opt_class		= !empty( $opt_value ) ? ' class="opt-'.$opt_value.'"' : '';
					$opt_for = '';
					$html[] = '<option value="' . $opt_value  .'"' . $opt_for . $opt_class . $opt_selected . '>';
					$html[] = $opt_label;
					$html[] = '</option>';
				}
				$html[] = '</select>';
				break;
			case 'multiple_select':
				if( !is_array( $options ) ) {
					break;
				}

				$html[] ='<select id='.$id.' name="' . $option_name . '[]" class="' . $class . '" multiple="multiple" style="width: 30%; padding: 2px;">';
				foreach ( $options as $opt_value => $opt_label ) {
					$opt_selected	= (!empty($value) && in_array($opt_value,$value )) ? ' selected="selected"' : '';

					$opt_class		= !empty( $opt_value ) ? ' class="opt-'.$opt_value.'"' : '';
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
				$html[] = '<fieldset>';
				foreach ( $options as $opt_value => $opt_label ) {
					$opt_checked	= ( $value == $opt_value ) ? ' checked="checked"' : '';

					$opt_id			= $id . '_' . $opt_value;
					$opt_for		= ' for="' . $opt_id . '"';
					$opt_class		= !empty( $opt_value ) ? ' class="opt-'.$opt_value.'"' : '';
					$html[] = '<label' . $opt_for . $opt_class . '>';
					$html[] = '<input id="' . $opt_id . '" type="radio" name="' . $option_name . '" value="' . $opt_value . '" class="radio"' . $opt_checked .'/>';
					$html[] = $opt_label . '</label>';
					$html[] = '<br/>';
				}
				$html[] = '</fieldset>';

				break;
			case 'checkbox':
				$checked = ( $value ) ? ' checked="checked"' : '';
				$html[] = '<input type="hidden" name="' . $option_name . '" value="0" />';
				$html[] = '<input type="checkbox" id="' . $id . '" name="' . $option_name . '" value="1"' . $checked . ' />';

				if ( isset( $child_fields ) && !empty( $child_fields ) && is_array( $child_fields ) ) :
					$html[] = '<script>';
					$html[] = '	jQuery(document).ready(function($) {';
					$id = esc_attr( $id );
					foreach ( $child_fields as $option_value => $fields ) :
						if ( empty( $fields ) ) continue;
						$fields = explode( ',', $fields );
						foreach ( $fields as $child_field ) :
							if ( trim( $child_field ) == "" ) continue;
							$html[] = '$(".'. trim( $child_field ) . '").addClass("child_' . $id . ' ' . $id .'_val_' . esc_attr($option_value) . '" );';
						endforeach;
					endforeach;

					$html[] = '		var control    = jQuery("#' . $id . '");';

					$html[] = '		control.bind("toggle_children", function() {';
					$html[] = '			var $this = jQuery(this);';
					$html[] = '			if($this.parents(".' . $id . '").hasClass("hide-option")) {';
					$html[] = '				jQuery(".child_' . $id . '").addClass("hide-option").find("input, select").trigger("toggle_children");';
					$html[] = '				return;';
					$html[] = '			}';

					$html[] = '			if($this.is( ":checked" )) {';
					$html[] = '				jQuery(".' . $id . '_val_off").addClass("hide-option").find("input, select").trigger("toggle_children");';
					$html[] = '				jQuery(".' . $id . '_val_on").removeClass("hide-option").find("input, select").trigger("toggle_children");';
					$html[] = '			} else {';
					$html[] = '				jQuery(".' . $id . '_val_on").addClass("hide-option").find("input, select").trigger("toggle_children");';
					$html[] = '				jQuery(".' . $id . '_val_off").removeClass("hide-option").find("input, select").trigger("toggle_children");';
					$html[] = '			}';
					$html[] = '		});';

					$html[] = '		control.trigger("toggle_children");';

					$html[] = '		control.click( function() {';
					$html[] = '			control.trigger("toggle_children");';
					$html[] = '		});';
					$html[] = '	});';
					$html[] = '</script>';
				endif;

				break;
			case 'label':
				$html[] = '<p class="' . $class . '">' . $default . '</p>';
				break;
			case 'image':
				$html[] = '<input type="text" id='.$id.' name="' . $option_name . '" value="' . $value . '" style="margin-bottom: 5px;">';
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

			case 'datepicker':
				wp_enqueue_script( 'vendor-datetimepicker' );
				wp_enqueue_style( 'vendor-datetimepicker' );

				$date_text = !empty( $value ) ? date_i18n( get_option('date_format'), $value ) : '';

				$html[] = '<input type="text" id="' . $id . '" name="' . $id . '" value="' . esc_attr( $date_text ) . '" /> ';
				$html[] = '<input type="hidden" name="' . $id . '" value="' . esc_attr( $value ) . '" /> ';
				$html[] = '<script type="text/javascript">';
				$html[] = '	jQuery(document).ready(function($) {';
				$html[] = '		$("#' . $id . '").datetimepicker({';
				$html[] = '			format: "' . get_option('date_format') . '",';
				$html[] = '			step: 15,';
				$html[] = '			timepicker: false,';
				$html[] = '			onChangeDateTime:function(dp,$input) {';
				$html[] = '				$input.next("input[type=\'hidden\']").val(parseInt(dp.getTime()/1000)-60*dp.getTimezoneOffset());';
				$html[] = '			}';
				$html[] = '		});';
				$html[] = '	});';
				$html[] = '</script>';
				break;
		}

		if( $echo ) echo implode("\n", $html);
		else return implode("\n", $html);
	}
endif;
