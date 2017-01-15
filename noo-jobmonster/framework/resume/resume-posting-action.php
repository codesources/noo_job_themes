<?php

if( !function_exists('jm_page_post_resume_login_check') ) :
	function jm_page_post_resume_login_check( $action = '' ){
		if(!Noo_Member::is_logged_in()) {
			do_action( 'jm_page_post_resume_not_login', $action );
			switch( $action ) {
				case 'login':
					break;
				default:
					jm_force_redirect(esc_url_raw(add_query_arg( 'action', 'login')));
					break;
			}
		} elseif (!Noo_Member::is_candidate()) {
			do_action( 'jm_page_post_resume_not_candidate', $action );
			jm_force_redirect( home_url('/') );
		}
	}
endif;

if( !function_exists('jm_get_page_post_resume_steps') ) :
	function jm_get_page_post_resume_steps(){
		$steps = array(
			'login' => jm_get_page_post_resume_login_step(),
			'resume_package' => jm_get_page_post_resume_package_step(),
			'resume_post' => jm_get_page_post_resume_post_step(),
			// 'resume_general' => jm_get_page_post_resume_general_step(),
			// 'resume_detail' => jm_get_page_post_resume_detail_step(),
			'resume_preview' => jm_get_page_post_resume_preview_step(),
		);

		if( !jm_is_woo_resume_posting() ) {
			unset( $steps['resume_package'] );
		}

		// if( !Noo_Resume::enable_resume_detail() ) {
		// 	unset( $steps['resume_detail'] );
		// }

		return apply_filters( 'jm_page_post_resume_steps_list', $steps );
	}
endif;

if( !function_exists('jm_get_page_post_resume_login_step') ) :
	function jm_get_page_post_resume_login_step(){
		$title = Noo_Member::can_register() ? __('Login or create an account','noo') : __('Login', 'noo');
		
		return apply_filters( 'jm_page_post_resume_login_step', array(
			'actions' => array( 'login', 'register' ),
			'title' => $title,
			'link' => 'javascript:void(0);'
		) );
	}
endif;

if( !function_exists('jm_get_page_post_resume_package_step') ) :
	function jm_get_page_post_resume_package_step(){
		$title = __('Choose a package','noo');
		if( jm_is_woo_resume_posting() && !isset( $_REQUEST['package_id'] ) ){
			$link = esc_url(remove_query_arg('package_id', add_query_arg('action','resume_package')));
		} else {
			$link = 'javascript:void(0);';
		}
		
		return apply_filters( 'jm_page_post_resume_package_step', array(
			'actions' => array( 'resume_package' ),
			'title' => $title,
			'link' => $link
		) );
	}
endif;

if( !function_exists('jm_get_page_post_resume_post_step') ) :
	function jm_get_page_post_resume_post_step(){
		$title = __('Your Resume','noo');
		$link_args = array('action'=>'resume_post');
		$resume_id = isset($_GET['resume_id']) ? absint($_GET['resume_id']) : 0;
		if($resume_id) {
			$link_args['resume_id'] = $resume_id;
		}
		$link = esc_url(add_query_arg($link_args));
		
		return apply_filters( 'jm_page_post_resume_post_step', array(
			'actions' => array( 'resume_post' ),
			'title' => $title,
			'link' => $link
		) );
	}
endif;

if( !function_exists('jm_get_page_post_resume_general_step') ) :
	function jm_get_page_post_resume_general_step(){
		$title = __('General Information','noo');
		$link_args = array('action'=>'resume_general');
		$resume_id = isset($_GET['resume_id']) ? absint($_GET['resume_id']) : 0;
		if($resume_id) {
			$link_args['resume_id'] = $resume_id;
		}
		$link = esc_url(add_query_arg($link_args));
		
		return apply_filters( 'jm_page_post_resume_general_step', array(
			'actions' => array( 'resume_general' ),
			'title' => $title,
			'link' => $link
		) );
	}
endif;

if( !function_exists('jm_get_page_post_resume_detail_step') ) :
	function jm_get_page_post_resume_detail_step(){
		$title = __('Resume Details','noo');
		$link_args = array('action'=>'resume_detail');
		$resume_id = isset($_GET['resume_id']) ? absint($_GET['resume_id']) : 0;
		if($resume_id) {
			$link_args['resume_id'] = $resume_id;
		}
		$link = esc_url(add_query_arg($link_args));
		
		return apply_filters( 'jm_page_post_resume_detail_step', array(
			'actions' => array( 'resume_detail' ),
			'title' => $title,
			'link' => $link
		) );
	}
endif;

if( !function_exists('jm_get_page_post_resume_preview_step') ) :
	function jm_get_page_post_resume_preview_step(){
		$title = __('Preview and Finish','noo');
		
		return apply_filters( 'jm_get_page_post_resume_preview_step', array(
			'actions' => array( 'resume_preview' ),
			'title' => $title,
			'link' => 'javascript:void(0);'
		) );
	}
endif;
