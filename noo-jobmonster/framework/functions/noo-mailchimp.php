<?php
if ( ! class_exists( 'NooMailChimp' ) ) :

	class NooMailChimp {

		public static $instance;

		public static function getInstance() {
			if( empty( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		private function __construct() {
			// Ajax for mail list
			add_action( 'wp_ajax_noo_mail_list', array(&$this, 'ajax_mail_list') );

			add_action( 'wp_ajax_noo_mc_subscribe', array(&$this, 'ajax_mc_subscribe') );
			add_action( 'wp_ajax_nopriv_noo_mc_subscribe', array(&$this, 'ajax_mc_subscribe'));
		}

		public function ajax_mc_subscribe() {
			if ( ! check_ajax_referer( 'noo-subscribe', 'nonce', false ) ) {
				$this->_ajax_exit( __( 'Your session has expired. Please reload and retry.', 'noo' ) );
			}

			// setup the email and name varaibles
			$email = strip_tags($_POST['mc_email']);
			
			// check for a valid email
			if(!is_email($email)) {
				$this->_ajax_exit(__('Your email address is invalid. Click back and enter a valid email address.', 'noo'), __('Invalid Email', 'noo'));
			}

			$list_id = strip_tags($_POST['mc_list_id']);

			if(empty($list_id)) {
				$this->_ajax_exit(__( 'There\'s an unknown problem. Please reload and retry.', 'noo' ));
			}
			
			// send this email to campaign_monitor
			if( $this->_subscribe_email($email, $list_id) ) {
				setcookie('noo_subscribed', 1, time()*20, '/');
				$this->_ajax_exit( __( 'Thank you for your subscription.', 'noo' ), true );
			} else {
				$this->_ajax_exit($list_id. ' ' .$email. ' ' .__( 'There\'s an unknown problem. Please reload and retry.', 'noo' ));
			}
		}

		public function ajax_mail_list() {
			$api_key = isset( $_POST['api_key'] ) ? $_POST['api_key'] : '';
			if( empty( $api_key ) ) {
				exit();
			}

			$lists = $this->get_mail_lists( $api_key );
			if( empty( $lists ) ) {
				exit();
			}

			foreach($lists as $id => $list_name) {
				echo '<option value="' . $id . '" >' . $list_name . '</option>';
			}

			exit();
		}

		// get an array of all campaign monitor subscription lists
		public function get_mail_lists( $api_key = '' ) {
			$api_key = trim($api_key);
			$api_key = empty( $api_key ) ? noo_get_option('noo_mailchimp_api_key') : $api_key;

			if(strlen($api_key) > 0 ) {
				
				$lists = array();
				
				if( ! class_exists('MCAPI' ) ) {
					require_once(NOO_FRAMEWORK_FUNCTION . '/MCAPI.class.php');
				}		
				$api = new MCAPI($api_key);
				$list_data = $api->lists();
				if($list_data && isset($list_data['data']) && is_array($list_data['data']) ) :
					foreach($list_data['data'] as $key => $list) {
						$lists[$list['id']] = $list['name'];
					}
				endif;
				return $lists;
					
			}

			return false;
		}

		// adds an email to the campaign_monitor subscription list
		private function _subscribe_email($email, $list_id) {
			$api_key = trim(noo_get_option('noo_mailchimp_api_key'));
			if(strlen($api_key) > 0 ) {
				
				if( ! class_exists('MCAPI' ) ) {
					require_once(NOO_FRAMEWORK_FUNCTION . '/MCAPI.class.php');
				}
				$api = new MCAPI($api_key);
				// $opt_in = isset(self::$noo_mc_options['double_opt_in']) ? true : false;
				if($api->listSubscribe($list_id, $email, array(), 'html', true) === true) {
					return true;
				}
			
			}
			return false;
		}

		private function _ajax_exit( $data = '', $success = false, $redirect = '' ) {
			$response = array(
				'success' => $success,
				'data' => $data,
			);

			if( !empty( $redirect ) ) {
				$response['redirect'] = $redirect;
			}

			echo json_encode($response);
			exit();
		}
	}

endif;
	
global $noo_mailchimp;
$noo_mailchimp = NooMailChimp::getInstance();