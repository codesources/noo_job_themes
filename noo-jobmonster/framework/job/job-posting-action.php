<?php

if( !function_exists('jm_page_post_job_login_check') ) :
	function jm_page_post_job_login_check( $action = '' ){
		if(!Noo_Member::is_logged_in()) {
			do_action( 'jm_page_post_job_not_login', $action );
			switch( $action ) {
				case 'login':
					break;
				default:
					jm_force_redirect(esc_url_raw(add_query_arg( 'action', 'login')));
					break;
			}
		} elseif (!Noo_Member::is_employer()) {
			do_action( 'jm_page_post_job_not_employer', $action );
			jm_force_redirect( home_url('/') );
		}
	}
endif;

if( !function_exists('jm_get_page_post_job_steps') ) :
	function jm_get_page_post_job_steps(){
		$steps = array(
			'login' => jm_get_page_post_job_login_step(),
			'job_package' => jm_get_page_post_job_package_step(),
			'post_job' => jm_get_page_post_job_post_step(),
			'preview_job' => jm_get_page_post_job_preview_step(),
		);

		if( !jm_is_woo_job_posting() ) {
			unset( $steps['job_package'] );
		}

		return apply_filters( 'jm_page_post_job_steps_list', $steps );
	}
endif;

if( !function_exists('jm_get_page_post_job_login_step') ) :
	function jm_get_page_post_job_login_step(){
		$title = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
		
		return apply_filters( 'jm_page_post_job_login_step', array(
			'actions' => array( 'login', 'register' ),
			'title' => $title,
			'link' => 'javascript:void(0);'
		) );
	}
endif;

if( !function_exists('jm_get_page_post_job_package_step') ) :
	function jm_get_page_post_job_package_step(){
		$title = __('Choose a package','noo');
		if( jm_is_woo_job_posting() && isset($_REQUEST['package_id']) ) {
			$link = esc_url(remove_query_arg('package_id', add_query_arg('action','job_package')));
		} else {
			$link = 'javascript:void(0);';
		}
		
		return apply_filters( 'jm_page_post_job_package_step', array(
			'actions' => array( 'job_package' ),
			'title' => $title,
			'link' => $link
		) );
	}
endif;

if( !function_exists('jm_get_page_post_job_post_step') ) :
	function jm_get_page_post_job_post_step(){
		$title = __('Describe your company and vacancy','noo');
		$link_args = array('action'=>'post_job');
		$job_id = isset($_GET['job_id']) ? absint($_GET['job_id']) : 0;
		if($job_id) {
			$link_args['job_id'] = $job_id;
		}
		$link = esc_url(add_query_arg($link_args));
		
		return apply_filters( 'jm_page_post_job_post_step', array(
			'actions' => array( 'post_job' ),
			'title' => $title,
			'link' => $link
		) );
	}
endif;

if( !function_exists('jm_get_page_post_job_preview_step') ) :
	function jm_get_page_post_job_preview_step(){
		$title = __('Preview and submit your job','noo');
		
		return apply_filters( 'jm_get_page_post_job_preview_step', array(
			'actions' => array( 'preview_job' ),
			'title' => $title,
			'link' => 'javascript:void(0);'
		) );
	}
endif;
