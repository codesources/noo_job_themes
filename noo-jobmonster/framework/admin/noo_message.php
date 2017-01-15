<?php

class Noo_Message {
	/**
	 * @var Noo_Message The single instance of the class
	 */
	protected static $_instance = null;
	
	/** @var int $_customer_id */
	protected $_customer_id;
	
	/** @var array $_data  */
	protected $_data = array();
	
	/** @var bool $_dirty When something changes */
	protected $_dirty = false;
	
	/** cookie name */
	private $_cookie;
	
	/** session due to expire timestamp */
	private $_session_expiring;
	
	/** session expiration timestamp */
	private $_session_expiration;
	
	/** Bool based on whether a cookie exists **/
	private $_has_cookie = false;
	
	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		$this->_cookie = 'noo_message_' . COOKIEHASH;

		if ( $cookie = $this->get_session_cookie() ) {
			$this->_customer_id        = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;

			// Update session if its close to expiring
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
				$session_expiry_option = '_noo_message_expires_' . $this->_customer_id;
				// Check if option exists first to avoid auloading cleaned up sessions
				if ( false === get_option( $session_expiry_option ) ) {
					add_option( $session_expiry_option, $this->_session_expiration, '', 'no' );
				} else {
					update_option( $session_expiry_option, $this->_session_expiration );
				}
			}

		} else {
			$this->set_session_expiration();
			$this->_customer_id = $this->generate_customer_id();
		}

		$this->_data = $this->get_session_data();
		
		// add_action( 'wp', array( $this, 'maybe_set_customer_cookies' ) ); // Set cookies
    	add_action( 'shutdown', array( $this, 'save_data' ), 20 );
    	add_action( 'clear_auth_cookie', array( $this, 'destroy_session' ) );
    }
    
    /**
     * Main Instance
     * 
     * @return Noo_Message
     */
    public static function instance() {
    	if ( is_null( self::$_instance ) ) {
    		self::$_instance = new self();
    	}
    	return self::$_instance;
    }

    /**
     * Sets the session cookie on-demand (usually after adding an item to the cart).
     *
     * Since the cookie name (as of 2.1) is prepended with wp, cache systems like batcache will not cache pages when set.
     *
     * Warning: Cookies will only be set if this is called before the headers are sent.
     */
    public function set_customer_session_cookie( $set = true ) {
    	if ( $set ) {
	    	// Set/renew our cookie
			$to_hash           = $this->_customer_id . $this->_session_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_customer_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

	    	// Set the cookie
	    	$this->setcookie( $this->_cookie, $cookie_value, $this->_session_expiration, apply_filters( 'noo_message_use_secure_cookie', false ) );
	    }
    }

    /**
     * Return true if the current user has an active session, i.e. a cookie to retrieve values
     * @return boolean
     */
    public function has_session() {
    	return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in();
    }

    /**
     * set_session_expiration function.
     */
    public function set_session_expiration() {
	    $this->_session_expiring    = time() + intval( apply_filters( 'noo_message_expiring', 60 * 60 * 47 ) ); // 47 Hours
		$this->_session_expiration  = time() + intval( apply_filters( 'noo_message_expiration', 60 * 60 * 48 ) ); // 48 Hours
    }

	/**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return int|string
	 */
	public function generate_customer_id() {
		if ( is_user_logged_in() ) {
			return get_current_user_id();
		} else {
			require_once( ABSPATH . 'wp-includes/class-phpass.php');
			$hasher = new PasswordHash( 8, false );
			return md5( $hasher->get_random_bytes( 32 ) );
		}
	}

	/**
	 * get_session_cookie function.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		if ( empty( $_COOKIE[ $this->_cookie ] ) ) {
			return false;
		}

		list( $customer_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $_COOKIE[ $this->_cookie ] );

		// Validate hash
		$to_hash = $customer_id . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( $hash != $cookie_hash ) {
			return false;
		}

		return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
	}

	/**
	 * get_session_data function.
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) get_option( '_noo_message_' . $this->_customer_id, array() ) : array();
	}

	public function maybe_set_customer_cookies() {
		if ( ! headers_sent() && did_action( 'wp_loaded' ) ) {
			$this->set_customer_session_cookie( true );
		}
	}

    /**
     * save_data function.
     */
    public function save_data() {
    	// Dirty if something changed - prevents saving nothing new
    	if ( $this->_dirty && $this->has_session() ) {

			$session_option        = '_noo_message_' . $this->_customer_id;
			$session_expiry_option = '_noo_message_expires_' . $this->_customer_id;

	    	if ( false === get_option( $session_option ) ) {
	    		add_option( $session_option, $this->_data, '', 'no' );
		    	add_option( $session_expiry_option, $this->_session_expiration, '', 'no' );
	    	} else {
		    	update_option( $session_option, $this->_data );
	    	}
	    }
    }

    public function setcookie( $name, $value, $expire = 0, $secure = false ) {
    	if ( ! headers_sent() ) {
    		setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
    	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    		headers_sent( $file, $line );
    		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE );
    	}
    }
    
    /**
     * Destroy all session data
     */
    public function destroy_session() {
		// Clear cookie
		$this->setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, apply_filters( 'noo_message_use_secure_cookie', false ) );

		// Delete session
		$session_option        = '_noo_message_' . $this->_customer_id;
		$session_expiry_option = '_noo_message_expires_' . $this->_customer_id;

		delete_option( $session_option );
		delete_option( $session_expiry_option );

		// Clear data
		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}

    /**
	 * cleanup_sessions function.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		if ( ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {
			$now                = time();
			$expired_sessions   = array();
			$noo_message_expires = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM %s WHERE option_name LIKE '\_noo\_message\_expires\_%' AND option_value < '$now'" ), $wpdb->options );

			foreach ( $noo_message_expires as $option_name ) {
				$session_id         = substr( $option_name, 20 );
				$expired_sessions[] = $option_name;  // Expires key
				$expired_sessions[] = "_noo_message_$session_id"; // Session key
			}

			if ( ! empty( $expired_sessions ) ) {
				$expired_sessions_chunked = array_chunk( $expired_sessions, 100 );
				foreach ( $expired_sessions_chunked as $chunk ) {
					if ( wp_using_ext_object_cache() ) {
						// delete from object cache first, to avoid cached but deleted options
						foreach ( $chunk as $option ) {
							wp_cache_delete( $option, 'options' );
						}
					}

					// delete from options table
					$option_names = implode( "','", $chunk );
					$wpdb->query( $wpdb->prepare( "DELETE FROM %s WHERE option_name IN ('$option_names')", $wpdb->options ) );
				}
			}
		}
	}
	
	/**
	 * __get function.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}
	
	/**
	 * __set function.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}
	
	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}
	
	/**
	 * __unset function.
	 *
	 * @param mixed $key
	 * @return void
	 */
	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_dirty = true;
		}
	}
	
	/**
	 * Get a session variable
	 *
	 * @param string $key
	 * @param  mixed $default used if the session variable isn't set
	 * @return mixed value of session variable
	 */
	public function get( $key, $default = null ) {
		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}
	
	/**
	 * Set a session variable
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
			$this->_dirty = true;
		}
	}
	
	/**
	 * get_customer_id function.
	 *
	 * @access public
	 * @return int
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}
}


function noo_set_customer_cookies() {
	$noo_message = Noo_Message::instance();
	$noo_message->set_customer_session_cookie();
}
// add_action( 'init', 'noo_set_customer_cookies', 5 );

function noo_message_count( $type = '' ) {
	$message_count = 0;
	$noo_message = Noo_Message::instance();
	$all_messages  = $noo_message->get( 'noo_messages', array() );

	if ( isset( $all_messages[$type] ) ) {

		$message_count = absint( sizeof( $all_messages[$type] ) );

	} elseif ( empty( $type ) ) {

		foreach ( $all_messages as $message ) {
			$message_count += absint( sizeof( $all_messages ) );
		}

	}

	return $message_count;
}

function noo_message_add( $message, $type = 'success' ) {
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
		error_log( 'Add message: ' . $message );
	$noo_message = Noo_Message::instance();
	$messages = $noo_message->get('noo_messages',array());
	
	if ( 'success' === $type ) {
		$message = apply_filters( 'noo_message_add', $message );
	}

	$messages[$type][] = apply_filters( 'noo_message_add_' . $type, $message );
	$noo_message->set('noo_messages', $messages);

	$noo_message->set_customer_session_cookie( true );
}


function noo_message_clear() {
	$noo_message = Noo_Message::instance();
	$noo_message->set('noo_messages', null);

	// $noo_message->set_customer_session_cookie( true );
}

function noo_message_print() {
	$noo_message = Noo_Message::instance();
	$all_messsages  = $noo_message->get( 'noo_messages', array() );
	$message_types = apply_filters( 'noo_message_types', array( 'error', 'success', 'notice' ) );
	foreach ( $message_types as $message_type ) {
		if ( noo_message_count( $message_type ) > 0 ) {
			$messages = $all_messsages[$message_type];
			if(!$messages)
				continue;
			
			?>
			<div class="noo-messages noo-message-<?php echo esc_attr($message_type)?>">
				<ul>
					<?php foreach ( $messages as $message ) : ?>
						<li><?php echo wp_kses_post( $message ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php 
		}
	}
	noo_message_clear();
}
add_action('noo_lost_password_form_before', 'noo_message_print',10);
add_action('noo_reset_password_form_before', 'noo_message_print',10);
// add_action('noo_before_job_loop', 'noo_message_print',10);
add_action('noo_before_single_job', 'noo_message_print',10);
add_action('noo_member_manage_not_login_before', 'noo_message_print',10);
add_action('noo_member_manage_application_before', 'noo_message_print',10);
add_action('noo_member_manage_job_before', 'noo_message_print',10);
add_action('noo_member_manage_plan_before', 'noo_message_print',10);
add_action('noo_edit_job_before', 'noo_message_print',10);
add_action('noo_edit_company_before', 'noo_message_print',10);
add_action('noo_edit_candidate_before', 'noo_message_print',10);
add_action('noo_post_resume_before', 'noo_message_print',10);
// add_action('noo_post_resume_general_before', 'noo_message_print',10);
// add_action('noo_post_resume_detail_before', 'noo_message_print',10);
add_action('noo_member_manage_resume_before', 'noo_message_print',10);
add_action('noo_member_manage_job_alert_before', 'noo_message_print',10);
add_action('noo_edit_job_alert_before', 'noo_message_print',10);
add_action('noo_job_package_before', 'noo_message_print',10);
add_action('noo_company_contact_form', 'noo_message_print',10);
// add_action('page_post_job_message', 'noo_message_print',10);
// add_action('page_post_resume_message', 'noo_message_print',10);

