<?php
/**
 * NOO Framework Site Package.
 *
 * Register Script
 * This file register & enqueue scripts used in NOO Themes.
 *
 * @package    NOO Framework
 * @version    1.0.0
 * @author     NooTheme Team
 * @copyright  Copyright (c) 2014, NooTheme
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link       https://www.nootheme.com
 */
// =============================================================================

//
// Site scripts
//
if ( ! function_exists( 'noo_enqueue_site_scripts' ) ) :
	function noo_enqueue_site_scripts() {

		$js_folder_uri = SCRIPT_DEBUG ? NOO_ASSETS_URI . '/js' : NOO_ASSETS_URI . '/js/min';
		$js_suffix = SCRIPT_DEBUG ? '' : '.min';

		// vendor script
		wp_register_script( 'vendor-modernizr', NOO_FRAMEWORK_URI . '/vendor/modernizr-2.7.1.min.js', null, null, false );
		wp_register_script( 'vendor-touchSwipe', NOO_FRAMEWORK_URI . '/vendor/jquery.touchSwipe.min.js', array( 'jquery' ), null, true );
		wp_register_script( 'vendor-bootstrap', NOO_FRAMEWORK_URI . '/vendor/bootstrap.min.js', array( 'vendor-touchSwipe' ), null, true );
		
		wp_register_script( 'vendor-hoverIntent', NOO_FRAMEWORK_URI . '/vendor/hoverIntent-r7.min.js', array( 'jquery' ), null, true );
		wp_register_script( 'vendor-superfish', NOO_FRAMEWORK_URI . '/vendor/superfish-1.7.4.min.js', array( 'jquery', 'vendor-hoverIntent' ), null, true );
    	wp_register_script( 'vendor-jplayer', NOO_FRAMEWORK_URI . '/vendor/jplayer/jplayer-2.5.0.min.js', array( 'jquery' ), null, true );
		
		wp_register_script( 'vendor-imagesloaded', NOO_FRAMEWORK_URI . '/vendor/imagesloaded.pkgd.min.js', null, null, true );
		wp_register_script( 'vendor-isotope', NOO_FRAMEWORK_URI . '/vendor/isotope-2.0.0.min.js', array('vendor-imagesloaded'), null, true );
		wp_register_script( 'vendor-infinitescroll', NOO_FRAMEWORK_URI . '/vendor/infinitescroll-2.0.2.min.js', null, null, true );
		wp_register_script( 'vendor-carouFredSel', NOO_FRAMEWORK_URI . '/vendor/carouFredSel/jquery.carouFredSel-6.2.1-packed.js', array( 'jquery', 'vendor-touchSwipe','vendor-imagesloaded' ), null, true );
		
		wp_register_script( 'vendor-easing', NOO_FRAMEWORK_URI . '/vendor/easing-1.3.0.min.js', array( 'jquery' ), null, true );
		wp_register_script( 'vendor-appear', NOO_FRAMEWORK_URI . '/vendor/jquery.appear.js', array( 'jquery','vendor-easing' ), null, true );
		wp_register_script( 'vendor-countTo', NOO_FRAMEWORK_URI . '/vendor/jquery.countTo.js', array( 'jquery', 'vendor-appear' ), null, true );
		
		wp_register_script( 'vendor-nivo-lightbox-js', NOO_FRAMEWORK_URI . '/vendor/nivo-lightbox/nivo-lightbox.min.js', array( 'jquery' ), null, true );
		
		wp_register_script( 'vendor-parallax', NOO_FRAMEWORK_URI . '/vendor/jquery.parallax-1.1.3.js', array( 'jquery'), null, true );
		wp_register_script( 'vendor-nicescroll', NOO_FRAMEWORK_URI . '/vendor/nicescroll-3.5.4.min.js', array( 'jquery' ), null, true );
		
		wp_register_style( 'vendor-chosen', NOO_FRAMEWORK_URI . '/vendor/chosen/chosen.css', null, null );
		wp_register_script( 'vendor-chosen', NOO_FRAMEWORK_URI . '/vendor/chosen/chosen.jquery.min.js', array( 'jquery'), null, true );
		// wp_register_script( 'vendor-ajax-chosen', NOO_FRAMEWORK_URI . '/vendor/chosen/ajax-chosen.jquery.min.js', array( 'vendor-chosen'), null, true );
		wp_localize_script( 'vendor-chosen', 'noo_chosen', array(
			'multiple_text'		=> __('Select Some Options', 'noo'),
			'single_text'		=> __('Select an Option', 'noo'),
			'no_result_text'	=> __('No results match', 'noo')
		));
		
		wp_register_script( 'noo-timeline-vendor', NOO_FRAMEWORK_URI . '/vendor/venobox.min.js',null, null, null, false );
		wp_register_script( 'noo-timeline', NOO_FRAMEWORK_URI . '/vendor/timeliner.js',array( 'jquery'), null, null, false );
		wp_register_script( 'noo-readmore', NOO_FRAMEWORK_URI . '/vendor/readmore.min.js',array( 'jquery'), null, null, false );

		wp_localize_script( 'noo-readmore', 'noo_readmore', array(
			'lessLink'		=> __('Read less', 'noo'),
			'moreLink'		=> __('Read more', 'noo')
		));

		// BigVideo scripts.
		wp_register_script( 'vendor-bigvideo-video',        NOO_FRAMEWORK_URI . '/vendor/bigvideo/video-4.1.0.min.js',        array( 'jquery', 'jquery-ui-slider', 'vendor-imagesloaded' ), NULL, true );
		wp_register_script( 'vendor-bigvideo-bigvideo',     NOO_FRAMEWORK_URI . '/vendor/bigvideo/bigvideo-1.0.0.min.js',     array( 'jquery', 'jquery-ui-slider', 'vendor-imagesloaded', 'vendor-bigvideo-video' ), NULL, true );
		// wp_register_script( 'noo-countdown',     NOO_FRAMEWORK_URI . '/vendor/noo_countdown.js',, null, null, false );
		
		// Bootstrap WYSIHTML5
		// -- js upload
		wp_register_script( 'noo-upload', $js_folder_uri . '/noo.function.upload' . $js_suffix . '.js', array( 'jquery', 'plupload-all'), null, true );
		$nooUpload = array(
			'url' => esc_url_raw( add_query_arg( array( 'action' => 'noo_upload', 'nonce' => wp_create_nonce('aaiu_allow') ), admin_url('admin-ajax.php') ) ),
			'delete_url' => esc_url_raw( add_query_arg( array( 'action' => 'noo_delete_attachment', 'nonce' => wp_create_nonce('aaiu_remove') ), admin_url('admin-ajax.php') ) ),
			'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
			'remove_txt' => __('Remove', 'noo')
		);
		wp_localize_script('noo-upload', 'nooUpload', $nooUpload);


		wp_register_script( 'noo-script', $js_folder_uri . '/noo' . $js_suffix . '.js', array( 'jquery','vendor-bootstrap', 'vendor-superfish', 'vendor-jplayer' ), null, true );
		
		wp_register_script( 'vendor-carousel', NOO_FRAMEWORK_URI . '/vendor/owl.carousel.min.js', array( 'jquery'), null, true );
		
		if ( ! is_admin() ) {
			wp_enqueue_script( 'vendor-modernizr' );

			// Required for nested reply function that moves reply inline with JS
			if ( is_singular() ) wp_enqueue_script( 'comment-reply' );

			$is_shop				= NOO_WOOCOMMERCE_EXIST && is_shop();
			$nooL10n = array(
				'ajax_url'        => admin_url( 'admin-ajax.php', 'relative' ),
				'home_url'        => home_url( '/' ),
				'is_blog'         => is_home() ? 'true' : 'false',
				'is_archive'      => is_post_type_archive('post') ? 'true' : 'false',
				'is_single'       => is_single() ? 'true' : 'false',
				'is_companies'    => is_post_type_archive( 'noo_company' ) ? 'true' : 'false',
				'is_jobs'    	  => is_post_type_archive( 'noo_job' ) || is_tax( 'job_category') || is_tax( 'job_type') || is_tax( 'job_tag' ) || is_tax( 'job_location') ? 'true' : 'false',
				'is_job'    	  => is_singular( 'noo_job' ) ? 'true' : 'false',
				'is_resumes'      => is_post_type_archive( 'noo_resume' ) ? 'true' : 'false',
				'is_resume'    	  => is_singular( 'noo_resume' ) ? 'true' : 'false',
				'is_shop'         => NOO_WOOCOMMERCE_EXIST && is_shop() ? 'true' : 'false',
				'is_product'      => NOO_WOOCOMMERCE_EXIST && is_product() ? 'true' : 'false',
			);
			
			
			wp_localize_script('noo-script', 'nooL10n', $nooL10n);
			wp_enqueue_script( 'noo-script' );

			wp_register_script( 'noo-blockUI', $js_folder_uri . '/jquery.blockUI' . $js_suffix . '.js', array( 'jquery' ), null, true );
			wp_register_script( 'noo-member', $js_folder_uri . '/member' . $js_suffix . '.js', array( 'noo-blockUI' ), null, true );
			$nooMemberL10n = array(
				'ajax_security' =>wp_create_nonce( 'noo-member-security' ),
				'ajax_url'      => admin_url( 'admin-ajax.php', 'relative' ),
				'confirm_not_agree_term'=>__('Please agree with the Terms of use','noo'),
				'confirm_delete'=>__('Are you sure to delete this job?','noo'),
				'loadingmessage'=>'<i class="fa fa-spinner fa-spin"></i> '.__('Sending info, please wait...','noo'),
			);
			wp_localize_script('noo-member', 'nooMemberL10n', $nooMemberL10n);
			wp_enqueue_script('noo-member');

			if( is_page() ) {
				$page_template = get_page_template_slug();
				if( $page_template == 'page-post-job.php'
					|| $page_template == 'page-post-resume.php'
					|| get_the_ID() == Noo_Member::get_member_page_id() ) {
					wp_enqueue_script( 'noo-upload' );
				}

				if( $page_template == 'page-post-resume.php' ) {
					wp_enqueue_script('noo-timeline-vendor');
					wp_enqueue_script('noo-timeline');
				}
			}
		}

		wp_enqueue_script( 'vendor-carousel' );
		wp_register_script( 'google-map-custom', get_template_directory_uri() . '/assets/js/google-map-custom.js', array( 'jquery' ), null, false );
		wp_enqueue_script( 'google-re-captcha', 'https://www.google.com/recaptcha/api.js', null, null, false );

	}
add_action( 'wp_enqueue_scripts', 'noo_enqueue_site_scripts' );
endif;
