<?php

if( !function_exists( 'jm_admin_resume_edit_title_placeholder' ) ) :
	function jm_admin_resume_edit_title_placeholder($text, $post){
		if ( $post->post_type == 'noo_resume' )
			return __( 'Resume Title', 'noo' );
		return $text;
	}
	add_filter( 'enter_title_here', 'jm_admin_resume_edit_title_placeholder', 10, 2 );
endif;

if( !function_exists( 'jm_extend_resume_status' ) ) :
	function jm_extend_resume_status(){
		global $post, $post_type;
		if($post_type === 'noo_resume'){
			$html = $selected_label = '';
			foreach ((array) jm_get_resume_status() as $status=>$label){
				$seleced = selected($post->post_status,esc_attr($status),false);
				if($seleced)
					$selected_label = $label;
				$html .= "<option ".$seleced." value='".esc_attr($status)."'>".$label."</option>";
			}  
			?>
			<script type="text/javascript">
				jQuery( document ).ready( function($) {
					<?php if ( ! empty( $selected_label ) ) : ?>
						jQuery( '#post-status-display' ).html( '<?php echo esc_js( $selected_label ); ?>' );
					<?php endif; ?>
					var select = jQuery( '#post-status-select' ).find( 'select' );
					jQuery( select ).html( "<?php echo ($html); ?>" );
				} );
			</script>
			<?php
		}
	}

	foreach ( array( 'post', 'post-new' ) as $hook ) {
		add_action( "admin_footer-{$hook}.php", 'jm_extend_resume_status' );
	}
endif;

if( !function_exists( 'jm_resume_meta_boxes' ) ) :
	function jm_resume_meta_boxes() {
		// Declare helper object
		$helper = new NOO_Meta_Boxes_Helper( '', array( 'page' => 'noo_resume' ) );

		// General Info
		$meta_box = array(
			'id'           => '_general_info',
			'title'        => __( 'General Information', 'noo' ),
			'context'      => 'normal',
			'priority'     => 'core',
			'description'  => '',
			'fields'       => array(
			),
		);

		$fields = jm_get_resume_custom_fields();
		if($fields){
			foreach ($fields as $field){
				$id = jm_resume_custom_fields_name( $field['name'], $field );

				$new_field = noo_custom_field_to_meta_box( $field, $id );

				if( $field['name'] == '_job_location' ) {
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
					$new_field['multiple'] = true;
				}

				if( $field['name'] == '_job_category' ) {
					$new_field['type'] = 'select';
					$job_categories = array();
					// $job_categories[] = array('value'=>'','label'=>__('- Select a category -','noo'));
					$job_categories_terms = (array) get_terms('job_category', array('hide_empty'=>0));

					if( !empty( $job_categories_terms ) ) {
						foreach ($job_categories_terms as $category){
							$job_categories[] = array('value'=>$category->term_id,'label'=>$category->name);
						}
					}

					$new_field['options'] = $job_categories;
					$new_field['multiple'] = true;
				}

				$meta_box['fields'][] = $new_field;
			}
		}

		$helper->add_meta_box($meta_box);

		// Education
		if( jm_get_resume_setting('enable_education', '1') ) {
			$meta_box = array(
				'id'           => '_education',
				'title'        => __( 'Education', 'noo' ),
				'context'      => 'normal',
				'priority'     => 'core',
				'description'  => '',
				'fields'       => array(
					array(
						'id'	=> '_education',
						'label'	=> '',
						'type'	=> 'education',
						'std'	=> '',
						'callback' => 'jm_meta_box_field_resume_detail'
						)
				),
			);

			$helper->add_meta_box($meta_box);
		}

		// Experience
		if( jm_get_resume_setting('enable_experience', '1') ) {
			$meta_box = array(
				'id'           => '_experience',
				'title'        => __( 'Work Experience', 'noo' ),
				'context'      => 'normal',
				'priority'     => 'core',
				'description'  => '',
				'fields'       => array(
					array(
						'id'	=> '_experience',
						'label'	=> '',
						'type'	=> 'experience',
						'std'	=> '',
						'callback' => 'jm_meta_box_field_resume_detail'
						)
				),
			);

			$helper->add_meta_box($meta_box);
		}

		// Skill
		if( jm_get_resume_setting('enable_skill', '1') ) {
			$meta_box = array(
				'id'           => '_skill',
				'title'        => __( 'Summary of Skills', 'noo' ),
				'context'      => 'normal',
				'priority'     => 'core',
				'description'  => '',
				'fields'       => array(
					array(
						'id'	=> '_skill',
						'label'	=> '',
						'type'	=> 'skill',
						'std'	=> '',
						'callback' => 'jm_meta_box_field_resume_detail'
						)
				),
			);

			$helper->add_meta_box($meta_box);
		}

		// Candidate 
		$meta_box = array(
			'id'           => 'candidate',
			'title'        => __( 'Candidate', 'noo' ),
			'context'      => 'side',
			'priority'     => 'default',
			'description'  => '',
			'fields'       => array(
				array(
					'id' => 'author',
					'label' => __( 'This Resume belongs to Candidate', 'noo' ),
					'desc' => '',
					'type' => 'candidate_author',
					'std' => '',
					'callback' => 'jm_meta_box_field_resume_detail'
				)
			)
		);

		$helper->add_meta_box($meta_box);

		// Viewable 
		if( jm_viewable_resume_enabled() ) {
			$meta_box = array(
				'id'           => 'viewable',
				'title'        => __( 'Publicly Viewable/Searchable', 'noo' ),
				'context'      => 'side',
				'priority'     => 'default',
				'description'  => '',
				'fields'       => array(
					array(
						'id' => '_viewable',
						'label' => __( 'Viewable/Searchable', 'noo' ),
						'desc' => __( 'Set it to yes and this resume will be publicly viewable and searchable.', 'noo' ),
						'type' => 'select',
						'std' => 'no',
						'options' => array (
							array('value'=>'no','label'=>__('No','noo')),
							array('value'=>'yes','label'=>__('Yes','noo')),
							)
					)
				)
			);

			$helper->add_meta_box($meta_box);
		}

		// Attachment 
		if( jm_get_resume_setting('enable_upload_resume', '1') ) :
			$meta_box = array(
				'id'           => 'attachment',
				'title'        => __( 'Resume Attachment', 'noo' ),
				'context'      => 'side',
				'priority'     => 'default',
				'description'  => '',
				'fields'       => array(
					array(
						'id' => '_noo_file_cv',
						'type' => 'attachment',
						'std' => '',
						'options' => array(
							'extensions' => jm_get_resume_setting('extensions_upload_resume', 'pdf,doc,docx'),
						)
					)
				)
			);

			$helper->add_meta_box($meta_box);
		endif;
	}

	add_action( 'add_meta_boxes', 'jm_resume_meta_boxes', 30 );

endif;

if( !function_exists( 'jm_meta_box_field_resume_detail' ) ) :
	function jm_meta_box_field_resume_detail( $post, $id, $type, $meta, $std = null, $field = null ) {
		switch( $type ) {
			case 'candidate_author':

				$user_list = jm_get_members( Noo_Member::CANDIDATE_ROLE );

				echo'<select name="post_author_override" id="post_author_override" class="noo-admin-chosen' . ( is_rtl() ? ' chosen-rtl' : '' ) . '" data-placeholder="' . __('- Select a Candidate - ', 'noo') . '">';
				echo'	<option value=""></option>';
				foreach ( $user_list as $user ) {
					echo'<option value="' . $user->ID . '"';
					selected( $post->post_author, $user->ID, true );
					echo '>' . $user->display_name . '</option>';
				}
				echo '</select>';

				break;
			case 'education':
				$meta = array();
				$meta['school'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_school' ) );
				$meta['qualification'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_qualification' ) );
				$meta['date'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_date' ) );
				$meta['note'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_note' ) );

				foreach ($meta as $key => $value) {
					if( empty( $value ) ) $meta[$key] = array();
				}

				?>
				<div class="noo-metabox-addable" data-name="<?php echo esc_attr($id); ?>" >
					<table class="noo-addable-fields">
						<thead>
							<tr>
								<th><label><?php _e('School name', 'noo'); ?></label></th>
								<th><label><?php _e('Qualification(s)', 'noo'); ?></label></th>
								<th><label><?php _e('Start/end date', 'noo'); ?></label></th>
								<th><label><?php _e('Note', 'noo'); ?></label></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4">
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_school'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_qualification'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_date'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_note'; ?>]' />
									<input type="button" value="<?php _e('Add Education', 'noo'); ?>" class="button button-default noo-clone-fields" data-template="<tr><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_school'; ?>][]' /></td><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_qualification'; ?>][]' /></td><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_date'; ?>][]' /></td><td><textarea name='noo_meta_boxes[<?php echo esc_attr($id) . '_note'; ?>][]' ></textarea> </td><td><a href='javascript:void()' class='noo-remove-fields'><?php _e('x', 'noo'); ?></a></td></tr>"/>
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php
						foreach( $meta['school'] as $index => $school ) : 
						?>
							<tr>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_school'; ?>][]" value="<?php echo esc_attr($meta['school'][$index]); ?>" /></td>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_qualification'; ?>][]" value="<?php echo esc_attr($meta['qualification'][$index]); ?>" /></td>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_date'; ?>][]" value="<?php echo esc_attr($meta['date'][$index]); ?>" /></td>
								<td><textarea name="noo_meta_boxes[<?php echo esc_attr($id) . '_note'; ?>][]" ><?php echo esc_attr($meta['note'][$index]); ?></textarea> </td>
								<td><a href="javascript:void()" class="noo-remove-fields"><?php _e('x', 'noo'); ?></a></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
				break;
			case 'experience':
				$meta = array();
				$meta['employer'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_employer' ) );
				$meta['job'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_job' ) );
				$meta['date'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_date' ) );
				$meta['note'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_note' ) );

				foreach ($meta as $key => $value) {
					if( empty( $value ) ) $meta[$key] = array();
				}

				?>
				<div class="noo-metabox-addable" data-name="<?php echo esc_attr($id); ?>" >
					<table class="noo-addable-fields">
						<thead>
							<tr>
								<th><label><?php _e('Employer', 'noo'); ?></label></th>
								<th><label><?php _e('Job Title', 'noo'); ?></label></th>
								<th><label><?php _e('Start/end date', 'noo'); ?></label></th>
								<th><label><?php _e('Note', 'noo'); ?></label></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="4">
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_employer'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_job'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_date'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_note'; ?>]' />
									<input type="button" value="<?php _e('Add Experience', 'noo'); ?>" class="button button-default noo-clone-fields" data-template="<tr><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_employer'; ?>][]' /></td><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_job'; ?>][]' /></td><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_date'; ?>][]' /></td><td><textarea name='noo_meta_boxes[<?php echo esc_attr($id) . '_note'; ?>][]' ></textarea> </td><td><a href='javascript:void()' class='noo-remove-fields'><?php _e('x', 'noo'); ?></a></td></tr>"/>
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php
						foreach( $meta['employer'] as $index => $employer ) : 
							// if( empty( $employer ) ) continue;
						?>
							<tr>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_employer'; ?>][]" value="<?php echo esc_attr($meta['employer'][$index]); ?>" /></td>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_job'; ?>][]" value="<?php echo esc_attr($meta['job'][$index]); ?>" /></td>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_date'; ?>][]" value="<?php echo esc_attr($meta['date'][$index]); ?>" /></td>
								<td><textarea name="noo_meta_boxes[<?php echo esc_attr($id) . '_note'; ?>][]" ><?php echo esc_attr($meta['note'][$index]); ?></textarea> </td>
								<td><a href="javascript:void()" class="noo-remove-fields"><?php _e('x', 'noo'); ?></a></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
				break;
			case 'skill':
				$meta = array();
				$meta['name'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_name' ) );
				$meta['percent'] = noo_json_decode( noo_get_post_meta( get_the_ID(), $id . '_percent' ) );

				foreach ($meta as $key => $value) {
					if( empty( $value ) ) $meta[$key] = array();
				}

				?>
				<div class="noo-metabox-addable" data-name="<?php echo esc_attr($id); ?>" >
					<table class="noo-addable-fields">
						<thead>
							<tr>
								<th><label><?php _e('Skill Name', 'noo'); ?></label></th>
								<th style="width:20%;"><label><?php _e('Percent % ( 1 to 100 )', 'noo'); ?></label></th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="2">
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_name'; ?>]' />
									<input type='hidden' value="" name='noo_meta_boxes[<?php echo esc_attr($id) . '_percent'; ?>]' />
									<input type="button" value="<?php _e('Add Skill', 'noo'); ?>" class="button button-default noo-clone-fields" data-template="<tr><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_name'; ?>][]' /></td><td><input type='text' name='noo_meta_boxes[<?php echo esc_attr($id) . '_percent'; ?>][]' /></td><td><a href='javascript:void()' class='noo-remove-fields'><?php _e('x', 'noo'); ?></a></td></tr>"/>
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php
						foreach( $meta['name'] as $index => $name ) : 
							// if( empty( $name ) ) continue;
						?>
							<tr>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_name'; ?>][]" value="<?php echo esc_attr($meta['name'][$index]); ?>" /></td>
								<td><input type="text" name="noo_meta_boxes[<?php echo esc_attr($id) . '_percent'; ?>][]" value="<?php echo esc_attr($meta['percent'][$index]); ?>" /></td>
								<td><a href="javascript:void()" class="noo-remove-fields"><?php _e('x', 'noo'); ?></a></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
				break;
		}
	}
endif;

if( !function_exists( 'jm_meta_box_field_attachment' ) ) :
	function jm_meta_box_field_attachment( $post, $id, $type, $meta, $std = null, $field = null ) {
		$extensions = isset( $field['extensions'] ) && !empty( $field['extensions'] ) ? $field['extensions'] : 'pdf,doc,docx';
		?>
			<div class="clearfix">
		    	<?php noo_file_upload_form_field( 'noo_meta_boxes[' . $id . ']', noo_upload_convert_extension_list( $extensions ), $meta ) ?>
			</div>
		<?php
	}
endif;

if( !function_exists( 'jm_meta_box_function_sanitize_html_list_value' ) ) :
	function jm_meta_box_function_sanitize_html_list_value( $values ) {
		if( !is_array( $values ) ) return $values;

		$count = count( $values );
		for( $index = 0; $index < $count; $index++ ) {
			$values[$index] = htmlentities( $values[$index], ENT_QUOTES );
		}

		return $values;
	}
	add_filter( 'noo_sanitize_meta__education_note', 'jm_meta_box_function_sanitize_html_list_value' );
	add_filter( 'noo_sanitize_meta__experience_note', 'jm_meta_box_function_sanitize_html_list_value' );
endif;
