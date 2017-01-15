<?php
		if($wp_query->have_posts()):
			if( empty($title) ) {
				if( is_post_type_archive( 'noo_job' )
					|| is_tax( 'job_category' )
					|| is_tax( 'job_type' )
					|| is_tax( 'job_tag' )
					|| is_tax( 'job_location' ) ) {
					$title = __('Latest Jobs', 'noo');
				}
				if( is_search() || $title_type == 'job_count' ) {
					$title = sprintf( _n( 'We found %s available job for you', 'We found %s available jobs for you', $wp_query->found_posts, 'noo'), '<span class="text-primary">' . number_format_i18n($wp_query->found_posts) . '</span>' );
				}
			}
		?>
			<?php if(!$ajax_item || $ajax_item == null )://ajax item
				$id_scroll = uniqid('scroll');
				$attributes = 'id="' . $id_scroll . '" ' . 'class="jobs posts-loop ' . $class . '"' . ( !empty( $paginate ) ? ' data-paginate="'. esc_attr($paginate) .'"' : '' );
			?>
			<div <?php echo $attributes; ?>>
				<?php if( !empty($title) ): ?>
					<div class="posts-loop-title<?php if( is_singular( 'noo_job' ) ) echo ' single_jobs' ?>">
						<h3><?php echo $title;?></h3>
					</div>
				<?php endif;?>
				<div class="posts-loop-content">
					<div class="<?php echo esc_attr($paginate)?>-wrap">
			<?php endif;//ajax item?>
					<?php  ?>
					<?php do_action( 'job_list_before', $loop_args, $wp_query ); ?>

					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); global $post; ?>
						<?php
							$logo_company		= '';
			
							$company_id			= jm_get_job_company($post);

							// $locations			= get_the_terms( get_the_ID(), 'job_location' );
							if( !empty( $company_id ) ) {
								if( noo_get_option( 'noo_jobs_show_company_logo', true ) ) {
									$logo_company    = Noo_Company::get_company_logo( $company_id );
								}
							}
						?>
							<?php do_action( 'job_list_single_before', $loop_args, $wp_query ); ?>
							<article <?php post_class($item_class); ?> data-url="<?php the_permalink(); ?>">
								
								<div class="loop-item-wrap">
									<?php if( !empty( $logo_company ) ) : ?>
										<div class="item-featured">
											<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
												<?php echo $logo_company;?>
											</a>
										</div>
									<?php endif; ?>
									<div class="loop-item-content"<?php echo $show_view_more == 'yes' ? ' style="width: 60%;float: left;"' : ''; ?>>
										<h2 class="loop-item-title">
											<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permanent link to: "%s"','noo' ), the_title_attribute( 'echo=0' ) ) ); ?>"><?php the_title(); ?></a>
										</h2>
										<?php jm_the_job_meta($list_job_meta, $post); ?>
									</div>
									<?php if ( $show_view_more == 'yes' ) : ?>
										<div class="show-view-more" style="float: right;">
											<a class="btn btn-primary" href="<?php echo get_permalink($post->ID)?>">
												<?php _e('View more', 'noo')?>
											</a>
											<?php
											$is_candidate = Noo_Member::is_candidate();
											if ($is_candidate && noo_get_option('noo_jobs_list_style', '') == 'two') : ?>
												<div class="noo-job-bookmark-2">
													<a class="bookmark-job-link bookmark-job <?php echo(jm_is_job_bookmarked(0, get_the_ID()) ? 'bookmarked' : ''); ?>"
													   href="javascript:void(0);" data-toggle="tooltip"
													   data-job-id="<?php echo esc_attr(get_the_ID()); ?>"
													   data-action="noo_bookmark_job"
													   data-security="<?php echo wp_create_nonce('noo-bookmark-job'); ?>"
													   title="<?php _e('Bookmark Job', 'noo'); ?>">
														<i class="fa"></i>
														<span class="noo-bookmark-label">
															<?php
															if (jm_is_job_bookmarked(0, get_the_ID())):
																_e('Bookmarked', 'noo');
															else:
																_e('Bookmark Job', 'noo');
															endif;
															?>
														</span>
													</a>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									
								</div>
							</article>
						<?php do_action( 'job_list_single_after', $loop_args, $wp_query ); ?>
					<?php endwhile; ?>
					<?php do_action( 'job_list_after', $loop_args, $wp_query ); ?>
					<?php if(!$ajax_item)://ajax item?>
							</div>
						</div>
						<?php if($paginate == 'loadmore' && 1 < $wp_query->max_num_pages):?>
							<div class="loadmore-action">			
								<a href="#" class="btn btn-default btn-block btn-loadmore" title="<?php _e('Load More','noo')?>"><?php _e('Load More','noo')?></a>
								<div class="noo-loader loadmore-loading"><span></span><span></span><span></span><span></span><span></span></div>
							</div>
						<?php endif;?>
						<?php 
							if($paginate == 'nextajax'){
								if ( 1 < $wp_query->max_num_pages ){
									?>
									<div class="pagination list-center" 
										<?php
										if( is_array( $paginate_data ) && !empty( $paginate_data ) ) :
											foreach ($paginate_data as $key => $value) :
												echo ' data-' . $key . '="' . $value . '"';
											endforeach;
										endif;
										?>
										<?php echo ( !empty( $id_scroll ) ? "data-scroll=\"{$id_scroll}\"" : '' ); ?>
										data-show="<?php echo esc_attr($featured)?>"
										data-show_view_more="<?php echo esc_attr($show_view_more);?>"
										data-current_page="1"
										data-max_page="<?php echo absint($wp_query->max_num_pages)?>">
										<a href="#" class="prev page-numbers disabled">
											<i class="fa fa-long-arrow-left"></i>
										</a>
										
										<a href="#" class="next page-numbers">
											<i class="fa fa-long-arrow-right"></i>
										</a>
									</div>
									<?php
								}
							}else{
								if($pagination) {
									$pagination_args = isset( $pagination_args ) ? $pagination_args : array();
									noo_pagination($pagination_args,$wp_query);
								}
							}
						?>
					</div>
				<?php endif;//ajax item?>
		<?php else: ?>
			<div class="jobs posts-loop ">
				<?php
				if( $no_content == 'text' || empty( $no_content ) ) {
					noo_get_layout( 'no-content' );
				} elseif( $no_content != 'none' ) {
					echo '<h3>'.$no_content.'</h3>';
				}
				?>
			</div>
		<?php endif; ?>