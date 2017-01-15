<?php
if( !function_exists( 'jm_admin_job_approve_action' ) ) :
	function jm_admin_job_approve_action(){
		if(isset($_GET['action']) && $_GET['action'] == 'noo_job_approve'){
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'noo' ), '', array( 'response' => 403 ) );
			}
	
			if ( ! check_admin_referer( 'noo-job-approve' ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'noo' ), '', array( 'response' => 403 ) );
			}
	
			$post_id = ! empty( $_GET['job_id'] ) ? (int) $_GET['job_id'] : '';
	
			if ( ! $post_id || get_post_type( $post_id ) !== 'noo_job' ) {
				die;
			}
	
			$job_data = array(
				'ID'          => $post_id,
				'post_status' => 'publish'
			);
			wp_update_post( $job_data );
			do_action('noo_job_after_approve', $post_id);
			wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() ) ) );
			die();
		}
	}
	add_action( 'admin_init', 'jm_admin_job_approve_action' );
endif;

if( !function_exists( 'jm_admin_job_feature_action' ) ) :
	function jm_admin_job_feature_action(){
		if(isset($_GET['action']) && $_GET['action'] == 'noo_job_feature'){
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'noo' ), '', array( 'response' => 403 ) );
			}
	
			if ( ! check_admin_referer( 'noo-job-feature' ) ) {
				wp_die( __( 'You have taken too long. Please go back and retry.', 'noo' ), '', array( 'response' => 403 ) );
			}
	
			$post_id = ! empty( $_GET['job_id'] ) ? (int) $_GET['job_id'] : '';
	
			if ( ! $post_id || get_post_type( $post_id ) !== 'noo_job' ) {
				die;
			}
	
			$featured = noo_get_post_meta( $post_id, '_featured' );
	
			if ( 'yes' === $featured ) {
				update_post_meta( $post_id, '_featured', 'no' );
			} else {
				update_post_meta( $post_id, '_featured', 'yes' );
			}
	
	
			wp_safe_redirect( esc_url_raw( remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() ) ) );
			die();
		}
	}
	add_action( 'admin_init', 'jm_admin_job_feature_action' );
endif;

if( !function_exists( 'jm_admin_job_transition_post_status' ) ) :
	function jm_admin_job_transition_post_status( $new_status, $old_status, $post ) {
		if( $post->post_type !== 'noo_job' )
			return;

		if( !noo_get_post_meta( $post->ID, '_in_review', '' ) ) {
			return;
		}

		if( !is_admin() ) {
			return;
		}

		if( $new_status == 'publish' && $old_status != 'publish' ) {
			$employer_id = $post->post_author;

			wp_update_post(array(
				'ID'=>$post->ID,
				'post_date'		=> current_time( 'mysql' ),
				'post_date_gmt'	=> current_time( 'mysql' , 1 )
			));

			jm_set_job_expired( $post->ID );

			update_post_meta( $post->ID, '_in_review', '' );

			// employer email
			if( jm_et_get_setting('employer_job_approved_activated') ) {

				if ( is_multisite() )
					$blogname = $GLOBALS['current_site']->site_name;
				else
					$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
				$employer = get_user_by( 'id', $employer_id );

				$subject = jm_et_get_setting('employer_job_approved_subject');

				$array_subject = array(
					'[job_title]' => $post->post_title,
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);
				$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

				$to = $employer->user_email;

				$array_message = array(
					'[job_title]' => $post->post_title,
					'[job_url]' => get_permalink($post),
					'[job_company]' => $employer->display_name,
					'[job_manage_url]' => Noo_Member::get_endpoint_url('manage-job'),
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$message = jm_et_get_setting('employer_job_approved_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);

				noo_mail($to, $subject, $message,array(),'noo_notify_job_review_approve_employer');
			}
		}

		if( $new_status == 'trash' ) {
			$employer_id = $post->post_author;

			update_post_meta( $post->ID, '_in_review', '' );

			jm_decrease_job_posting_count( $employer_id );
			$featured = noo_get_post_meta( $post->ID, '_featured' );
			if( $featured == 'yes' ) {
				$job_featured = jm_get_feature_job_added( $employer_id );
				update_user_meta($employer_id, '_job_featured', max( $job_featured - 1, 0 ) );
			}

			// employer email
			if( jm_et_get_setting('employer_job_rejected_activated') ) {

				if ( is_multisite() )
					$blogname = $GLOBALS['current_site']->site_name;
				else
					$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
				$employer = get_user_by( 'id', $employer_id );

				$subject = jm_et_get_setting('employer_job_rejected_subject');

				$array_subject = array(
					'[job_title]' => $post->post_title,
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);
				$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

				$to = $employer->user_email;
				
				$array_message = array(
					'[job_title]' => $post->post_title,
					'[job_url]' => get_permalink($post),
					'[job_company]' => $employer->display_name,
					'[job_manage_url]' => Noo_Member::get_endpoint_url('manage-job'),
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$message = jm_et_get_setting('employer_job_rejected_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);

				noo_mail($to, $subject, $message,array(),'noo_notify_job_review_reject_employer');
			}
		}
	}
	add_action('transition_post_status', 'jm_admin_job_transition_post_status', 10, 3);
endif;

if( !function_exists( 'jm_admin_job_list_columns_header' ) ) :
	function jm_admin_job_list_columns_header( $columns ) {
		if ( ! is_array( $columns ) )
			$columns = array();

		$temp_title_col = $columns['title'];
		unset( $columns['title'], $columns['date'], $columns['author'] );
		
		$columns["job_type"]			= __( "Type", 'noo' );
		$columns["title"]				= $temp_title_col;
		$columns["job_category"]		= __( "Categories", 'noo' );
		$columns["job_posted"]			= __( "Posted", 'noo' );
		$columns["job_closing"]			= __( "Closing", 'noo' );
		$columns["job_expires"]			= __( "Expired", 'noo' );
		$columns['featured_job']		= '<span class="tips" data-tip="' . __( "Is Job Featured?", 'noo' ) . '">' . __( "Featured?", 'noo' ) . '</span>';
		$columns['application']			= '<span class="tips" data-tip="' . __( "Number of Application", 'noo' ) . '">' . __( "Application", 'noo' ) . '</span>';
		$columns['job_status']			= __( "Status", 'noo' );
		if( isset( $columns['comments'] ) ) {
			$temp = $columns['comments'];
			unset( $columns['comments'] );
			$columns['comments']         = $temp;
		}
		$columns['job_actions']          = __( "Actions", 'noo' );
		
		return $columns;
	}
	add_filter( 'manage_edit-noo_job_columns', 'jm_admin_job_list_columns_header' );
endif;

if( !function_exists( 'jm_admin_job_list_columns_data' ) ) :
	function jm_admin_job_list_columns_data($column){
		global $post, $wpdb;
		switch ( $column ) {
			case "job_type" :
				$type = jm_get_job_type( $post );
				if ( $type ) {
					if( !empty( $type->color ) ) {
						edit_term_link( $type->name, '<span class="job-type ' . $type->slug . '" style="background-color:' . $type->color . ';">', '</span>', $type );
					} else {
						edit_term_link( $type->name, '<span class="job-type ' . $type->slug . '" style="color:#0073aa;font-size:13px;">', '</span>', $type );
					}
				}
			break;
			case "job_position" :
				echo '<div class="job_position">';
				echo '<a href="' . admin_url('post.php?post=' . $post->ID . '&action=edit') . '" class="tips job_title" data-tip="' . sprintf( __( 'ID: %d', 'noo' ), $post->ID ) . '"><b>' . get_the_title( $post ) . '<b/></a>';
			
				echo '<div class="location">';
			
				$company_id = jm_get_job_company($post);
				if ( $company_id ) {
					$company_name = get_the_title( $company_id );
					echo '<span>' . __( 'for', 'noo' ) . '&nbsp;<a href="' . get_edit_post_link( $company_id ) . '">'.$company_name.'</a></span>';
				}
			
				echo '</div>';
				echo '</div>';
				break;
			case "job_category" :
				if ( ! $terms = get_the_terms( $post->ID, $column ) ) {
					echo '<span class="na">&ndash;</span>'; 
				} else {
					$terms_edit = array();
					foreach( $terms as $term ) {
						$terms_edit[] = edit_term_link( $term->name, '', '', $term, false );
					}
					echo implode(', ', $terms_edit);
				}
				break;
			case "job_posted" :
				echo '<strong>' . date_i18n( get_option('date_format'), strtotime( $post->post_date ) ) . '</strong><span>';
				echo ( empty( $post->post_author ) ? __( 'by a guest', 'noo' ) : sprintf( __( 'by %s', 'noo' ), '<a href="' . get_edit_user_link( $post->post_author ) . '">' . get_the_author() . '</a>' ) ) . '</span>';
				break;
			case "job_closing" :
				if ( $post->_closing ) {
					$closing = !is_numeric($post->_closing) ? strtotime( $post->_closing ) : $post->_closing;
					echo '<strong>' . date_i18n( get_option('date_format'), $closing ) . '</strong>';
				} else {
					echo '&ndash;';
				}
				break;
			case "job_expires" :
				if ( $post->_expires )
					echo '<strong>' . date_i18n( get_option('date_format'), $post->_expires ) . '</strong>';
				else
					echo '&ndash;';
				break;
			case "featured_job" :
				$featured = noo_get_post_meta($post->ID, '_featured' );
				if( empty( $featured ) ) {
					// Update old data
					update_post_meta( $post->ID, '_featured', 'no' );
				}
				$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=noo_job_feature&job_id=' . $post->ID ), 'noo-job-feature' );
				echo '<a href="' . esc_url( $url ) . '" title="'. __( 'Toggle featured', 'noo' ) . '">';
				if ( 'yes' === $featured ) {
					echo '<span class="noo-job-feature" title="'.esc_attr__('Yes','noo').'"><i class="dashicons dashicons-star-filled "></i></span>';
				} else {
					echo '<span class="noo-job-feature not-featured"  title="'.esc_attr__('No','noo').'"><i class="dashicons dashicons-star-empty"></i></span>';
				}
				echo '</a>';
				
				break;
			case "application" :
				$application_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'noo_application' AND post_parent = {$post->ID}" );
				if ( $application_count > 0 ) {
					$url_args = array( 's' => '', 'post_status' => 'all', 'post_type' => 'noo_application', 'job' => $post->ID, 'action' => -1, 'action2' => -1 );
					$application_link = esc_url( add_query_arg( $url_args,admin_url( 'edit.php' ) ) ); 
					echo '<strong><a href="' . $application_link . '">' . $application_count . '</a></strong>';
				} else {
					echo '&ndash;';
				}
				break;
			case "job_status" :
				$status   = jm_correct_job_status( $post->ID, $post->post_status );
				$status_text = '';
				$statuses = jm_get_job_status();
				if ( isset( $statuses[ $status ] ) ) {
					$status_text = $statuses[ $status ];
				} else {
					$status_text = __( 'Inactive', 'noo' );
				}
				echo esc_html( $status_text );
				break;
			case "job_actions" :
				echo '<div class="actions">';
				$admin_actions           = array();
				if ( $post->post_status == 'pending' && current_user_can ( 'publish_post', $post->ID ) ) {
					$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=noo_job_approve&job_id=' . $post->ID ), 'noo-job-approve' );
					$admin_actions['approve']   = array(
						'action'  => 'approve',
						'name'    => __( 'Approve', 'noo' ),
						'url'     =>  $url,
						'icon'	  => 'yes',
					);
				}
				if ( $post->post_status !== 'trash' ) {
					if ( current_user_can( 'read_post', $post->ID ) ) {
						$admin_actions['view']   = array(
							'action'  => 'view',
							'name'    => __( 'View', 'noo' ),
							'url'     => $post->post_status == 'draft' ? esc_url( get_preview_post_link( $post ) ) : get_permalink( $post->ID ),
							'icon'	  => 'visibility',
						);
					}
					if ( current_user_can( 'edit_post', $post->ID ) ) {
						$admin_actions['edit']   = array(
							'action'  => 'edit',
							'name'    => __( 'Edit', 'noo' ),
							'url'     => get_edit_post_link( $post->ID ),
							'icon'	  => 'edit',
						);
					}
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						$admin_actions['delete'] = array(
							'action'  => 'delete',
							'name'    => __( 'Delete', 'noo' ),
							'url'     => get_delete_post_link( $post->ID ),
							'icon'	  => 'trash',
						);
					}
				}
			
				$admin_actions = apply_filters( 'job_manager_admin_actions', $admin_actions, $post );
			
				foreach ( $admin_actions as $action ) {
					printf( '<a class="button tips action-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action['action'], esc_url( $action['url'] ), esc_attr( $action['name'] ), '<i class="dashicons dashicons-'.$action['icon'].'"></i>' );
				}
			
				echo '</div>';
			
				break;
		}
	}
	add_filter( 'manage_noo_job_posts_custom_column', 'jm_admin_job_list_columns_data' );
endif;

if( !function_exists( 'jm_admin_job_list_filter' ) ) :
	function jm_admin_job_list_filter() {
		$type = 'post';
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		}

		//only add filter to post type you want
		if ('noo_job' == $type){
			global $post;

			// Company
			$companies = get_posts(
				array(
					'post_type' => 'noo_company',
					'posts_per_page' => -1,
					'post_status' => 'publish',
					'orderby' => 'title',
					'order' => 'ASC',
					'suppress_filters' => false
				)
			);
			?>
			<select name="company">
				<option value=""><?php _e('All Companies', 'noo'); ?></option>
				<?php
				$current_v = isset($_GET['company'])? $_GET['company']:'';
				foreach ($companies as $company) {
					if( empty( $company->post_title ) ) continue;
					printf
					(
						'<option value="%s"%s>%s</option>',
						$company->ID,
						$company->ID == $current_v ? ' selected="selected"':'',
						$company->post_title
					);
				}
				?>
			</select>
			<?php
			// Employer
			?>
			<!-- <select name="employer">
				<option value=""><?php _e('All Employers', 'noo'); ?></option>
				<?php
				// $current_v = isset($_GET['employer'])? $_GET['employer']:'';
				// $user_list = jm_get_members( Noo_Member::EMPLOYER_ROLE );
				// $admin_list = jm_get_members( 'administrator' );
				// $user_list = array_merge($admin_list, $user_list);

				// foreach ( $user_list as $user ) {
				// 	$company_id = jm_get_employer_company($user->ID);
				// 	echo'<option value="' . $user->ID . '"';
				// 	selected( $current_v, $user->ID, true );
				// 	echo '>' . $user->display_name;
				// 	if( !empty($company_id) ) {
				// 		$company_name = get_the_title( $company_id );
				// 		echo ( !empty($company_name) ? ' - ' . $company_name : '' );
				// 	}
				// 	echo '</option>';
				// }
				?>
			</select> -->
			<?php
			// Job Category
			$job_categories = get_terms( 'job_category' );
			?>
			<select name="job_category">
				<option value=""><?php _e('All Categories', 'noo'); ?></option>
				<?php
				$current_v = isset($_GET['job_category'])? $_GET['job_category']:'';
				foreach ($job_categories as $job_category) {
					printf
					(
						'<option value="%s"%s>%s</option>',
						$job_category->slug,
						$job_category->slug == $current_v ? ' selected="selected"':'',
						$job_category->name
					);
				}
				?>
			</select>
			<?php
			// Job Location
			$job_locations = get_terms( 'job_location' );
			?>
			<select name="job_location">
				<option value=""><?php _e('All Locations', 'noo'); ?></option>
				<?php
				$current_v = isset($_GET['job_location'])? $_GET['job_location']:'';
				foreach ($job_locations as $job_location) {
					printf
					(
						'<option value="%s"%s>%s</option>',
						$job_location->slug,
						$job_location->slug == $current_v ? ' selected="selected"':'',
						$job_location->name
					);
				}
				?>
			</select>
			<?php
			// Job Type
			$job_types = get_terms( 'job_type' );
			?>
			<select name="job_type">
				<option value=""><?php _e('All Types', 'noo'); ?></option>
				<?php
				$current_v = isset($_GET['job_type'])? $_GET['job_type']:'';
				foreach ($job_types as $job_type) {
					printf
					(
						'<option value="%s"%s>%s</option>',
						$job_type->slug,
						$job_type->slug == $current_v ? ' selected="selected"':'',
						$job_type->name
					);
				}
				?>
			</select>
			<?php
			// Job Tag
			$job_tags = get_terms( 'job_tag' );
			?>
			<select name="job_tag">
				<option value=""><?php _e('All Tags', 'noo'); ?></option>
				<?php
				$current_v = isset($_GET['job_tag'])? $_GET['job_tag']:'';
				foreach ($job_tags as $job_tag) {
					printf
					(
						'<option value="%s"%s>%s</option>',
						$job_tag->slug,
						$job_tag->slug == $current_v ? ' selected="selected"':'',
						$job_tag->name
					);
				}
				?>
			</select>
			<?php
		}
	}
	add_action( 'restrict_manage_posts', 'jm_admin_job_list_filter' );
endif;

if( !function_exists( 'jm_admin_job_list_filter_action' ) ) :
	function jm_admin_job_list_filter_action( $query ){
		global $pagenow;
		$type = 'post';
		if (isset($_GET['post_type'])) {
			$type = $_GET['post_type'];
		}
		if ( 'noo_job' == $type && is_admin() && $pagenow=='edit.php' ) {
			if( !isset($query->query_vars['post_type']) || $query->query_vars['post_type'] == 'noo_job' ) {
				if( isset($_GET['company']) && $_GET['company'] != '') {
					$company_id = absint( $_GET['company'] );

					if( !empty( $company_id ) ) {
						$job_ids = Noo_Company::get_company_jobs( $company_id, array(), -1, $_GET['post_status'] );
						$query->query_vars['post__in'] = array_merge( $job_ids, array( 0 ) );
					}
				}
				if( isset($_GET['employer']) && $_GET['employer'] != '') {
					$employer_id = absint( $_GET['employer'] );

					if( !empty( $employer_id ) ) {
						$query->query_vars['author'] = $employer_id;
					}
				}
			}
		}
	}

	add_filter( 'parse_query', 'jm_admin_job_list_filter_action' );
endif;

if( !function_exists( 'jm_admin_job_list_views_status' ) ) :
	function jm_admin_job_list_views_status( $views ) {
		if( isset( $views['publish'] ) )
			$views['publish'] = str_replace( 'Published ', _x('Active', 'Job status', 'noo') . ' ', $views['publish'] );

		return $views;
	}

	add_filter( 'views_edit-noo_job', 'jm_admin_job_list_views_status' );
endif;
