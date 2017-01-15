<?php if ( $style == 'slider' ) : ?>
	<?php $id = noo_vc_elements_id_increment(); ?>
	<div class="wpb_wrapper">
		<div class="noo-text-block">
			<?php if( !empty($title) ) : ?>
				<h3 style="text-align: center;">
					<strong>
						<?php echo $title; ?>
					</strong>
				</h3>
			<?php endif; ?>
			<p style="text-align: center;">
				<?php echo $featured_content; ?>
			</p>
		</div>
	</div>
	<?php
	$option['owl'] = 'yes';
	noo_caroufredsel_slider( $wp_query, !empty( $option ) ? $option : array() ); ?>
<?php elseif($style == 'style2'): ?>
	<?php $letter_range = range( __('A', 'noo' ), __('Z', 'noo' ) );
	$letter_range = apply_filters( 'noo_company_title_letter_range', $letter_range );
	$letter_range = array_unique( $letter_range );
	?>
	<?php if( !empty($title) ) : ?>
		<div class="form-title">
			<h3><?php echo ($title); ?></h3>
		</div>
	<?php endif; ?>
	<div class="company-letters">
		<?php
		if(!empty($archive) && $archive =='yes'){
			$link = get_post_type_archive_link( 'noo_company' );
		} else{
			$link = get_page_link();
		}
		$current_key = (isset($_GET['key'])) ? $_GET['key'] : '';

		?>
		<a href="<?php echo $link; ?>" class="<?php echo ($current_key == '') ? 'selected' : ''; ?>"><?php _e('All', 'noo'); ?></a>
		<?php foreach ( $letter_range as $letter ) {
			$class = ($current_key == $letter) ? 'selected' : '';
			$letter = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $letter ) : strtoupper( $letter );
			echo '<a href="' . $link . '?key='.$letter.'" class="'.$class.'">' . $letter . '</a>';
		} ?>
	</div>
	<div class="company-list">
		<?php do_action('noo_list_company_before'); ?>
		<div class="row">
			<?php
			while ( $wp_query->have_posts() ) : $wp_query->the_post(); global $post; ?>
				<?php
				$company_name		= $post->post_title;

				$count = noo_company_job_count( $post->ID );

				$ft = ('yes' == noo_get_post_meta( $post->ID, '_company_featured', '' )) ? 'featured-company' : '';
				?>
				<div class="col-sm-4 company-list-item">
					<div class="company-item company-inner <?php echo esc_attr($ft); ?>">
						<div class="company-item-thumbnail">
							<a href="<?php the_permalink(); ?>">
								<?php echo Noo_Company::get_company_logo($post->ID); ?>
							</a>
							<a class="btn btn-primary btn-company" href="<?php the_permalink(); ?>">
								<?php echo __('View More', 'noo'); ?>
							</a>
						</div>
						<div class="company-item-meta">
							<a href="<?php the_permalink(); ?>">
								<?php echo esc_html($company_name); ?>
							</a>
							<p>
								<i class="fa fa-briefcase"></i><span class="job-count"><?php echo $count > 0 ? sprintf( _n( '%s Job', '%s Jobs', $count, 'noo'), $count ) : __('No Job','noo'); ?></span>
							</p>
						</div>
					</div>
				</div>
			<?php endwhile; ?>
		</div>
		<?php noo_pagination(); ?>
		<?php do_action('noo_list_company_after'); ?>
	</div>
<?php else : ?>
	<?php $letter_range = range( __('A', 'noo' ), __('Z', 'noo' ) ); 
		$letter_range = apply_filters( 'noo_company_title_letter_range', $letter_range );
		$letter_range = array_unique( $letter_range );
	?>
	<?php if( !empty($title) ) : ?>
		<div class="form-title">
			<h3><?php echo ($title); ?></h3>
		</div>
	<?php endif; ?>
	<div class="company-letters">
		<a data-filter="*" href="#all" class="selected"><?php _e('All', 'noo'); ?></a>
		<?php foreach ( $letter_range as $letter ) {
			$letter = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $letter ) : strtoupper( $letter );
			echo '<a data-filter=".filter-'.$letter.'" href="#' . $letter . '">' . $letter . '</a>';
		} ?>
	</div>
	<?php
		if($wp_query->have_posts()):
			$current_letter = '';
			wp_enqueue_script('vendor-isotope');
	?>
		<div class="masonry">
			<ul class="companies-overview masonry-container ">
				<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); global $post; ?>
					<?php
						$company_name		= $post->post_title;

						$company_letter		= function_exists( 'mb_strtoupper' ) ? mb_strtoupper(mb_substr($company_name, 0, 1)) : strtoupper(substr($company_name, 0, 1));
						$count				= noo_company_job_count( $post->ID );
						
						if( $company_letter != $current_letter ) {
							if( $current_letter != '' ) {
								echo '</ul>';
								echo '</li>';
							}
							$current_letter = $company_letter;

							echo '<li class="company-group masonry-item filter-' . $current_letter . '"><div id="' . $current_letter . '" class="company-letter text-primary">' . $current_letter . '</div>';
							echo '<ul>';
						}

						echo '<li class="company-name"><a href="' . get_permalink() . '">' . esc_attr( $company_name ) . ' (' . $count . ')</a></li>';
					?>
				<?php endwhile; ?>
					</ul>
				</li>
			</ul>
		</div>
	<?php endif; ?>
<?php endif; ?>