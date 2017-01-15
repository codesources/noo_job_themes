<?php
if($wp_query->have_posts() || !$is_shortcode):
	$title = empty($title) ? __('Resumes', 'noo') : $title;
	$settings_fields = get_theme_mod( 'noo_resume_list_fields', 'title,_job_location,_job_category' );
	$settings_fields = !is_array( $settings_fields ) ? explode( ',', $settings_fields ) : $settings_fields;
	$display_fields = array();

	foreach( $settings_fields as $index => $resume_field ) {
		if( $resume_field == 'title' ) {
			$field = array( 'name' => 'title', 'label' => __( 'Resume Title', 'noo' ) );
		} else {
			$field = jm_get_resume_field( $resume_field );
		}
		if( !empty( $field ) ) {
			$display_fields[] = $field;
		}
	}
?>
	<?php if(!$ajax_item || $ajax_item == null )://ajax item ?>
		<div class="resumes posts-loop" data-paginate="<?php echo $paginate; ?>">
			<div class="posts-loop-title">
				<h3><?php echo $title; ?></h3>
			</div>
			<div class="posts-loop-content resume-table">
				<table class="table">
					<thead>
						<tr>
							<th><?php _e('Candidate','noo')?></th>
							<?php foreach( $display_fields as $index => $field ) : ?>
								<?php if( $index <= 1 || count( $display_fields ) <= 3 ) : ?>
									<th><?php echo esc_html( isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'] ); ?></th>
								<?php else : ?>
									<th class="hidden-xs"><?php _e('Details','noo')?></th>
									<?php break; ?>
								<?php endif; ?>
							<?php endforeach; reset( $display_fields ); ?>
						</tr>
					</thead>
					<tbody class="<?php echo $paginate; ?>-wrap">
	<?php endif; ?>
	<?php if($wp_query->have_posts()):?>
		<?php if( jm_can_view_resume(null,true) ):?>
			<?php while ($wp_query->have_posts()): $wp_query->the_post();global $post;?>
				<tr>
					<td>
						<?php
							$candidate_avatar	= '';
							$candidate_name	= '';
							if( !empty( $post->post_author ) ) :
								$candidate_avatar 	= noo_get_avatar( $post->post_author, 40 );
								$candidate = get_user_by( 'id', $post->post_author );
								$candidate_name = $candidate->display_name;
								$candidate_link = esc_url( apply_filters( 'noo_resume_candidate_link', get_the_permalink(), $post->ID, $post->post_author ) );
							?>
							<div class="loop-item-wrap">
							    <div class="item-featured">
									<a href="<?php echo $candidate_link; ?>">
										<?php echo $candidate_avatar;?>
									</a>
								</div>
								
								<div class="loop-item-content">
									<h2 class="loop-item-title">
										<a href="<?php echo $candidate_link;; ?>" ><?php echo esc_html( $candidate_name ); ?></a>
									</h2>
								</div>
							</div>
						<?php endif; ?>
					</td>
					<?php foreach( $display_fields as $index => $field ) : ?>
						<?php if( !isset( $field['name'] ) || empty( $field['name'] )) continue; ?>
						<?php if( $index <= 2 ) : ?>
							<td>
						<?php endif; ?>
						<?php if ( $field['name'] == 'title' ) : ?>
							<a href="<?php the_permalink()?>"><strong><?php the_title()?></strong></a></td>
						<?php else : ?>
							<?php
								$value = jm_get_resume_field_value( $post->ID, $field );
								if( !empty($value) ) {
									$html = array();
									$value = noo_convert_custom_field_value( $field, $value );
									if( $index <= 1 || count( $display_fields ) <= 3 ) {
										if( is_array( $value ) ) {
											$value = implode(', ', $value);
										}
										$html[] = $value;
									} else {
										$label = isset( $field['label_translated'] ) ? $field['label_translated'] : $field['label'];
										$html[] = '<span class="resume-' . $field['name'] . '" style="display: inline-block;">';
										$html[] = $label . ': <em>';
										$html[] = is_array($value) ? implode(', ', $value) : $value;
										$html[] = '</em></span>';
									}

									echo implode("\n", $html);
								} ?>
						<?php endif; ?>
						<?php if( $index <= 1 ) : ?>
							</td>
						<?php endif; ?>
					<?php endforeach; reset( $display_fields ); ?>
					<?php if( $index >= 2 ) : ?>
						</td>
					<?php endif; ?>
				</tr>
			<?php endwhile;?>
		<?php else:?>
			<?php
				$wp_query->max_num_pages = 0;
				list($title, $link) = jm_get_cannot_view_resume_message();
			?>
			<tr>
				<td colspan="6">
					<h3><?php echo $title; ?></h3>
					<?php if( !empty( $link ) ) echo $link; ?>
				</td>
			</tr>
		<?php endif;?>
	<?php else:?>
		<tr>
			<td colspan="6"><h3><?php _e('No Resume available','noo')?></h3></td>
		</tr>
	<?php endif;?>
	<?php if(!$ajax_item || $ajax_item == null ) ://ajax item ?>
				</tbody>
			</table>
		</div>
		<?php if($pagination) :
			if( $paginate == 'resume_nextajax') :
				if ( 1 < $wp_query->max_num_pages ) :
					?>
					<div class="pagination list-center" 
						data-job-category="<?php echo esc_attr($job_category);?>"
						data-job-location="<?php echo esc_attr($job_location);?>"
						data-orderby="<?php echo esc_attr($orderby);?>"
						data-order="<?php echo esc_attr($order);?>"
						data-posts-per-page="<?php echo absint($posts_per_page)?>"
						data-current-page="1"
						data-max-page="<?php echo absint($wp_query->max_num_pages)?>">
						<a href="#" class="prev page-numbers disabled">
							<i class="fa fa-long-arrow-left"></i>
						</a>
						
						<a href="#" class="next page-numbers">
							<i class="fa fa-long-arrow-right"></i>
						</a>
					</div>
					<?php
				endif;
			else :
				
				( $live_search ? noo_pagination( '', $wp_query, $live_search) : noo_pagination( '', $wp_query) );
				
			endif;
		endif;
	?>
	</div>
	<?php
	endif;
endif;
?>