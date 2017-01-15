<?php
if(!class_exists('Noo_Job')) :
	class Noo_Job {
		
		protected static $_instance = null;
		
		public function __construct() {
			// add_action( 'init', array( $this, 'register_post_type' ), 0 );
			// add_action('noo_job_check_expired_jobs', array($this,'check_expired_jobs'));
			// add_action('noo_job_reset_job_counter', array($this,'reset_job_counter'));

			// schema

		}
		
		protected function _get_job_map_data(){
			return jm_build_job_map_data();
		}
		
		public static function get_setting($group, $id = null ,$default = null){
			return jm_get_setting( $group, $id, $default );
		}

		public static function get_default_fields( $include_tax_fields = true ) {
			return jm_get_job_default_fields( $include_tax_fields );
		}
		
		public static function get_custom_fields_option($key = '', $default = null){
			return jm_get_job_custom_fields_option( $key, $default );
		}
		
		public static function advanced_search_field($field_val='', $is_resume = false, $tag_id = 'search-custom-field', $show_label = false ){
			if( $is_resume ) {
				return jm_resume_advanced_search_field( $field_val );
			} else {
				return jm_job_advanced_search_field( $field_val );
			}
		}
		
		/**
		 * Check use job package
		 * @return boolean
		 */
		
		public static function use_woocommerce_package(){
			return jm_is_woo_job_posting();
		}
		
		public static function get_application_method($post = null){
			$post = get_post( $post );
			if ( $post->post_type !== 'noo_job' )
				return;
			
			$method = new stdClass();
			$apply  = get_post_meta($post->ID,'_application_email',true);
			
			if ( empty( $apply ) )
				return false;
			
			if ( strstr( $apply, '@' ) && is_email( $apply ) ) {
				$method->type      = 'email';
				$method->raw_email = $apply;
				$method->email     = antispambot( $apply );
				$method->subject   = apply_filters( 'noo_job_application_email_subject', sprintf( __( 'Application via "%s" on %s', 'noo' ), $post->post_title, home_url() ), $post );
			} else {
				if ( strpos( $apply, 'http' ) !== 0 )
					$apply = 'http://' . $apply;
				$method->type = 'url';
				$method->url  = $apply;
			}
			
			return apply_filters( 'noo_job_get_application_method', $method, $post );
		}
		
		public static function get_job_locations($search_name = ''){
			return jm_search_job_location( $search_name );
		}
		
		public static function jobpackage_handler() {
			if( jm_is_woo_job_posting() ) {
				if( isset( $_GET['package_id'] ) || jm_get_job_posting_remain() > 0 ) {
					wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'post_job')));
				}
			}
			return;
		}
		
		public static function login_handler(){
			if(Noo_Member::is_logged_in()) {
				if(jm_is_woo_job_posting()) {
					wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'job_package')));
				}
				else {
					wp_safe_redirect(esc_url_raw(add_query_arg( 'action', 'post_job')));
				}
			}
			return;
		}
		
		public static function get_employer_package($employer_id=''){
			return jm_get_job_posting_info( $employer_id );
		}

		public static function get_job_remain( $employer_id = '' ) {
			return jm_get_job_posting_remain($employer_id);
		}

		public static function get_job_added( $employer_id = '' ) {
			return jm_get_job_posting_added( $employer_id );
		}

		public static function increase_job_count($employer_id='') {
			jm_increase_job_posting_count( $employer_id );
		}

		public static function decrease_job_count($employer_id='') {
			jm_decrease_job_posting_count( $employer_id );
		}

		public static function set_job_expires($job_id='') {
			jm_set_job_expired( $job_id );
		}
		
		/**
		 * 
		 * @param int $employer_id
		 * @param bool $is_paged
		 * @param bool $only_publish
		 * @return WP_Query
		 */
		public static function get_job_by_employer($employer_id='',$is_paged = true,$only_publish = false){
			return jm_user_job_query( $employer_id, $is_paged, ( $only_publish ? array( 'publish' ) : array() ) );
		}
		
		public static function get_employer_company($employer_id=''){
			return jm_get_employer_company( $employer_id );
		}

		public static function can_add_job($employer_id = ''){
			return jm_can_post_job( $employer_id );
		}

		/**
		 * Retrieve get feature job by employer
		 * 
		 * @param string $employer_id
		 * @return WP_Query
		 */
		public static function get_count_feature_job_by_employer($employer_id=''){
			return jm_get_feature_job_added($employer_id);
		}
		
		public static function can_set_job_feature($employer_id = ''){
			return jm_can_set_feature_job( $employer_id );
		}

		public static function get_remain_job_feature($employer_id = '') {
			return jm_get_feature_job_remain( $employer_id );
		}

		public static function send_notification($job_id = null, $user_id = 0) {
			if( empty( $job_id ) ) {
				return false;
			}
			if( empty( $user_id ) ) $user_id = get_current_user_id();
			$job = get_post($job_id);
			if( empty( $job ) ) return;

			$current_user = get_userdata( $user_id );
			if( $current_user->ID != $job->post_author ) {
				return false;
			}

			$emailed = noo_get_post_meta( $job_id, '_new_job_emailed', 0 );
			if( $emailed ) {
				return false;
			}

			if ( is_multisite() )
				$blogname = $GLOBALS['current_site']->site_name;
			else
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			
			$company = get_post(absint(jm_get_employer_company($current_user->ID)));

			$job_need_approve = jm_get_job_setting( 'job_approve','yes' ) == 'yes';
			if( $job_need_approve ) {
				$job_link = esc_url( add_query_arg( 'job_id', $job_id, Noo_Member::get_endpoint_url('preview-job') ) );
			} else {
				$job_link = get_permalink($job_id);
			}
			
			// admin email
			if( jm_et_get_setting('admin_job_submitted_activated') ) {
				$subject = jm_et_get_setting('admin_job_submitted_subject');

				$array_subject = array(
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
					'[job_title]' => $job->post_title,
				);
				$subject = str_replace(array_keys($array_subject), $array_subject, $subject);

				$to = get_option('admin_email');

				$array_message = array(
					'[job_title]' => $job->post_title,
					'[job_url]' => $job_link,
					'[job_company]' => $company->post_title,
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$message = jm_et_get_setting('admin_job_submitted_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);

				$subject = jm_et_custom_field('job',$job_id, $subject);
				$message = jm_et_custom_field('job',$job_id, $message);

				noo_mail($to, $subject, $message ,array(),'noo_notify_job_submitted_admin');
			}

			// employer email
			if( jm_et_get_setting('employer_job_submitted_activated') ) {
				$subject_employer = jm_et_get_setting('employer_job_submitted_subject');

				$array_subject = array(
					'[site_name]' => $blogname,
					'[job_title]' => $job->post_title,
					'[site_url]' => esc_url(home_url('')),
				);

				$subject = str_replace(array_keys($array_subject), $array_subject, $subject_employer);

				
				$to = $current_user->user_email;

				$array_message = array(
					'[job_title]' => $job->post_title,
					'[job_url]' => $job_link,
					'[job_company]' => $current_user->display_name,
					'[job_manage_url]' => Noo_Member::get_endpoint_url('manage-job'),
					'[site_name]' => $blogname,
					'[site_url]' => esc_url(home_url('')),
				);

				$message = jm_et_get_setting('employer_job_submitted_content');
				$message = str_replace(array_keys($array_message), $array_message, $message);

				$subject = jm_et_custom_field('job',$job_id, $subject);
				$message = jm_et_custom_field('job',$job_id, $message);

				noo_mail($to, $subject, $message,'noo_notify_job_submitted_employer');
			}

			update_post_meta( $job_id, '_new_job_emailed', 1 );
		}

		public static function get_job_type($job = null ) {
			return jm_get_job_type($job);
		}
		
		public static function get_job_status(){
			return jm_get_job_status();
		}
		
		public static function is_page_post_job(){
			return jm_is_job_posting_page();
		}
		
		public static function need_login(){
			return !Noo_Member::is_logged_in();
		}


		private static $linkedin_script_loaded;

		public static function load_linkedin_script() {
			static $linkedin_script_loaded = false;

			if( $linkedin_script_loaded ) return;
			$linkedin_script_loaded = true;
			$protocol = is_ssl() ? 'https' : 'http';
			?>
			<script type="text/javascript" src="<?php echo $protocol; ?>://platform.linkedin.com/in.js">
				<?php
					echo 'api_key: ' . jm_get_3rd_api_setting('linkedin_app_id', '') . "\n";
					echo 'authorize: true' . "\n";
					if( is_ssl() )
						echo 'credentials_cookie: true';
				?>
			</script>
			<?php
		}

		public static function social_share( $post_id = null, $title = '' ) {
			jm_the_job_social( $post_id, $title );
		}
		
		public static function the_job_meta($args = '', $job = null) {
			jm_the_job_meta( $args, $job );
		}
		
		// backward compatible
		public static function noo_contentjob_meta($args = '', $job = null) {
			jm_the_job_meta( $args, $job );
		}

		public static function the_job_tag($job = null) {
			jm_the_job_tag( $job );
		}

		public static function display_detail($query=null,$in_preview=false){
			jm_job_detail( $query, $in_preview );
		}

		public static function loop_display( $args = '' ) {
			jm_job_loop( $args );
		}

		public static function get_bookmarked_job_ids( $user_id = 0 ) {
			return jm_get_candidate_bookmarked_job( $user_id );
		}

		public static function set_bookmark_job( $user_id = 0, $job_id = 0 ) {
			return jm_job_set_bookmarked( $user_id, $job_id );
		}

		public static function clear_bookmark_job( $user_id = 0, $job_id = 0 ) {
			return jm_job_clear_bookmarked( $user_id, $job_id );
		}

		public static function is_bookmarked( $user_id = 0, $job_id = 0 ) {
			return jm_is_job_bookmarked( $user_id, $job_id );
		}
		
		public static function geolocation_enabled(){
			return jm_geolocation_enabled();	
		}

		public static function get_job_geolocation($raw_address){
			return jm_get_geolocation($raw_address);
		}

		public static function related_jobs( $job_id, $title ) {
			jm_related_jobs( $job_id, $title );
		}

}
new Noo_Job();
endif;