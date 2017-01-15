<?php
if(!class_exists('Noo_Job_Alert')):
	class Noo_Job_Alert {

		public static function set_alert_schedule( $job_alert_id = null, $frequency = '' ) {
			if( !self::enable_job_alert() ) {
				return;
			}

			if( empty( $job_alert_id ) ) {
				return;
			}

			wp_clear_scheduled_hook( 'noo-job-alert-notify', array( $job_alert_id ) );

			$alert = get_post( $job_alert_id );

			if ( ! $alert || $alert->post_status !== 'publish' || $alert->post_type !== 'noo_job_alert' ) {
				return;
			}

			// Update the schedule time
			update_post_meta( $alert->ID, '_start_schedule_time', time() );

			// Reschedule next alert
			$frequency = empty( $frequency ) ? noo_get_post_meta( $alert->ID, '_frequency', 'weekly' ) : $frequency;
			switch ( $frequency ) {
				case 'daily' :
					$next = strtotime( '+1 day' );
				break;
				case 'hourly' :
					$next = strtotime( '+1 hour' );
				break;
				case 'weekly' :
					$next = strtotime( '+1 week' );
				break;
				case 'fortnight' :
					$next = strtotime( '+1 fortnight' );
				break;
				case 'monthly' :
					$next = strtotime( '+1 month' );
				break;
				default:
					$next = strtotime( '+1 week' );
			}

			// Create cron
			return wp_schedule_single_event( $next, 'noo-job-alert-notify', array( $alert->ID ) );
		}

		public static function enable_job_alert() {
			return self::get_setting('enable_job_alert', 'yes') == 'yes';
		}

		public static function get_setting($id = null ,$default = null){
			global $noo_job_alert_setting;
			if(!isset($noo_job_alert_setting) || empty($noo_job_alert_setting)){
				$noo_job_alert_setting = get_option('noo_job_alert');
			}
			if (isset($noo_job_alert_setting[$id])) {
				return $noo_job_alert_setting[$id];
			}
			return $default;
		}

		public function __construct(){
			if( self::enable_job_alert() ) {
				add_action( 'init', array( $this, 'register_post_type' ), 20 );
				add_action( 'noo-job-alert-notify', array( $this, 'notify' ) );
			}

			if( is_admin() ) {
				add_action('admin_init', array(&$this,'admin_init'));

				add_filter('noo_job_settings_tabs_array', array(&$this,'add_seting_job_alert_tab'), 20);
				add_action('noo_job_setting_job_alert', array(&$this,'setting_page'));
			}
		}

		public function register_post_type() {
			register_post_type( 
				'noo_job_alert', 
				array(
					'public' 				=> false,
					'show_ui' 				=> false,
					'capability_type' 		=> 'post',
					'publicly_queryable' 	=> false,
					'exclude_from_search' 	=> true,
					'hierarchical' 			=> false,
					'rewrite' 				=> false,
					'query_var' 			=> false,
					'supports' 				=> false,
					'has_archive' 			=> false,
					'show_in_nav_menus' 	=> false
				)
			);
		}
	
		public function admin_init(){
			register_setting('noo_job_alert','noo_job_alert');
		}
		
		public function add_seting_job_alert_tab($tabs){
			$tabs['job_alert'] = __('Job Alert','noo');
			return $tabs;
		}
		
		public function setting_page(){
			if(isset($_GET['settings-updated']) && $_GET['settings-updated'])
			{
				flush_rewrite_rules();
			}
			?>
			<?php settings_fields('noo_job_alert'); ?>
			<h3><?php echo __('Member Options','noo')?></h3>
			<table class="form-table" cellspacing="0">
				<tbody>
					<tr>
						<th>
							<?php esc_html_e('Enable Job Alert','noo')?>
						</th>
						<td>
							<?php 
							$enable_job_alert = self::get_setting('enable_job_alert', 'yes');
							?>
							<input type="hidden" name="noo_job_alert[enable_job_alert]" value="no" >
							<input type="checkbox" name="noo_job_alert[enable_job_alert]" value="yes" <?php checked($enable_job_alert,'yes')?>>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e('Max Jobs for each Email','noo')?>
						</th>
						<td>
							<?php 
							$max_job_count_email = self::get_setting('max_job_count_email', 5);
							?>
							<input type="text" name="noo_job_alert[max_job_count_email]" value="<?php echo ($max_job_count_email ? $max_job_count_email :'5') ?>">
							<p><small><?php echo __( 'The maximum number of jobs included in each email. It helps make sure the email has reasonable length. If there are more jobs, a read more link will be added to the end of email.', 'noo' ); ?></small></p>
						</td>
					</tr>
					<?php do_action( 'noo_setting_job_alert_fields' ); ?>
				</tbody>
			</table>
			<?php
		}

		public function notify( $alert_id ) {
			$alert = get_post( $alert_id );

			if ( ! $alert || $alert->post_status !== 'publish' || $alert->post_type !== 'noo_job_alert' ) {
				return;
			}

			$user  = get_user_by( 'id', $alert->post_author );
			$jobs  = $this->_get_alert_jobs( $alert );

			if ( $jobs && $jobs->found_posts > 0 ) {
				$site_name = get_bloginfo( 'name' );

				$email = $this->_format_email( $alert, $user, $jobs );
				$subject = sprintf( __( '%d+ New Jobs - Job Alert from %s', 'noo' ), $jobs->found_posts, $site_name );
				$subject = apply_filters( 'noo_job_alert_email_subject', $subject, $alert, $jobs );

				if ( $email ) {
					noo_mail( $user->user_email, $subject, $email, array(), 'noo_notify_job_alert_candidate' );
				}

				// Count
				update_post_meta( $alert->ID, '_notify_count', 1 + absint( noo_get_post_meta( $alert->ID, '_notify_count', 0 ) ) );
			}

			self::set_alert_schedule( $alert->ID );
		}

		public function _get_alert_jobs( $alert ) {
			global $wpdb;

			$alert_id = $alert->ID;

			$post__in = array();
			// $meta_query = array('relation' => 'AND');
			$tax_query = array('relation' => 'AND');
			$date_query = array();

			$keywords = noo_get_post_meta( $alert_id, '_keywords', '' );
			$search_keywords   = array_map( 'trim', explode( ',', $keywords ) );
			$keywords_where    = array();

			if( !empty( $search_keywords ) && count( $search_keywords ) ) :
				foreach ( $search_keywords as $keyword ) {
					$keywords_where[]    = 'post_title LIKE \'%' . esc_sql( $keyword ) . '%\' OR post_content LIKE \'%' . esc_sql( $keyword ) . '%\'';
				}

				$where = implode( ' OR ', $keywords_where );
				$post__in = array_merge( $wpdb->get_col( "
				    SELECT DISTINCT ID FROM {$wpdb->posts}
				    WHERE ( {$where} )
				    AND post_type = 'noo_job'
				    AND post_status = 'publish'" ), array(0) ); // add 0 value to make sure there's no result if no job matchs keywords

			endif;

			$location = noo_get_post_meta( $alert_id, '_job_location', '' );
			$location = noo_json_decode($location);
			if( !empty($location) ){
				$location_query = array(
						'taxonomy'     => 'job_location',
						'field'        => 'id',
						'terms'        => $location,
						'compare'      => 'IN'
				);
				$tax_query[] = $location_query;
			}

			$category = noo_get_post_meta( $alert_id, '_job_category', '' );
			$category = noo_json_decode($category);
			if( !empty( $category) ){
				$category_query = array(
						'taxonomy'     => 'job_category',
						'field'        => 'id',
						'terms'        => $category,
						'compare'      => 'IN'
				);
				$tax_query[] = $category_query;
			}

			$type = noo_get_post_meta( $alert_id, '_job_type', '' );
			if( !empty( $type) ){
				$type_query = array(
						'taxonomy'     => 'job_type',
						'field'        => 'id',
						'terms'        => $type
				);
				$tax_query[] = $type_query;
			}

			$last_schedule_time = noo_get_post_meta( $alert_id, '_start_schedule_time', '' );
			if( !empty( $last_schedule_time ) ) {
				$date_query['after'] = get_date_from_gmt( date('Y-m-d H:i:s', absint( $last_schedule_time ) ), 'Y-m-d H:i:s' );
			} else {
				$frequency = noo_get_post_meta( $alert_id, '_frequency', '' );
				switch( $frequency ) {
					case 'monthly':
						$date_query['after'] = '-1 month';
					break;
					case 'fortnight':
						$date_query['after'] = '-1 fortnight';
					break;
					case 'daily':
						$date_query['after'] = '-1 day';
					break;
					case 'hourly':
						$date_query['after'] = '-1 hour';
					break;
					default: // weekly
						$date_query['after'] = '-1 week';
					break;
				}
			}

			$args = array(
				'post_type'     => 'noo_job',
				'post_status'   => 'publish',
				'posts_per_page' => -1,
				'nopaging'      => true,
				'post__in'    => $post__in,
				// 'meta_query'    => $meta_query,
				'tax_query'    => $tax_query,
				'date_query'   => $date_query,
			);

			do_action( 'before_get_job_alert', $args );

			$result = new WP_Query( $args );

			do_action( 'after_get_job_alert', $args );

			return $result;
		}

		private function _format_email( $alert, $user, $jobs ) {
			$max_alert_job_count = self::get_setting('max_job_count_email', 5);
			$site_name = get_bloginfo( 'name' );

			$message  = sprintf(__('Dear %s,', 'noo'), $user->display_name) . '<br/><br/>';
			$message .= sprintf(__('We found %d new jobs that match your criteria.', 'noo'), $jobs->found_posts) . '<br/><br/>';

			if ( $jobs && $jobs->have_posts() ) {
				$count = 0;
				while ( $jobs->have_posts() && $count <= $max_alert_job_count ) :
					$jobs->the_post(); global $post;
					$count++;
					$locations = wp_get_post_terms( $post->ID, 'job_location', array( 'fields' => 'names' ) );
					$categories = wp_get_post_terms( $post->ID, 'job_category', array( 'fields' => 'names' ) );
					$types = wp_get_post_terms( $post->ID, 'job_type', array( 'fields' => 'names' ) );

					$message .= sprintf(__('%s: <a href="%s">%s</a>', 'noo'), get_the_title( $post ), get_permalink( $post->ID ), get_permalink( $post->ID ) ) . '<br/>';
					$message .= sprintf(__('** Location: %s', 'noo'), implode(', ', $locations) ) . '<br/>';
					$message .= sprintf(__('** Job Category: %s', 'noo'), implode(', ', $categories) ) . '<br/>';
					$message .= sprintf(__('** Job Type: %s', 'noo'), implode(', ', $types) ) . '<br/>';
					$message .= __('------', 'noo') . '<br/>';

				endwhile;

				if( $jobs->found_posts > $max_alert_job_count ) {
					// @TODO: add search link
					$message .= sprintf(__('View more jobs: %s', 'noo'), get_home_url()) . '<br/>';
				}
			}

			$message .= '<br/>' . __('Best regards,', 'noo') . '<br/>';
			$message .= $site_name;

			return apply_filters( 'noo_job_alerts_email_content', $message );
		}
		
		public static function get_frequency(){
			$frequency = array(
				'daily'=>__('Daily','noo'),
				'weekly'=>__('Weekly','noo'),
				'fortnight'=>__('Fortnightly','noo'),
				'monthly'=>__('Monthly','noo'),
			);
			return apply_filters('get_frequency', $frequency);
			
		}
	}

	new Noo_Job_Alert();
endif;
