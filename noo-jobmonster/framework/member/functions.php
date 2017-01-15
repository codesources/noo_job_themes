<?php

if( !function_exists( 'jm_member_admin_init' ) ) :
	function jm_member_admin_init(){
		register_setting('jm_company_custom_field', 'jm_company_custom_field');
		register_setting('jm_candidate_custom_field', 'jm_candidate_custom_field');
	}
	
	add_filter('admin_init', 'jm_member_admin_init' );
endif;

if( !function_exists( 'jm_get_members' ) ) :
	function jm_get_members( $role = '' ) {
		$transient_name = 'jm_members_' . $role;

		if ( false !== ( $users = get_transient( $transient_name ) ) ) {
			return $users;
		}

		$users = get_users( array( 'role' => $role, 'orderby' => 'display_name' ) );

		set_transient( $transient_name, $users, DAY_IN_SECONDS );

		return $users;
	}
endif;

if( !function_exists( 'jm_get_member_ids' ) ) :
	function jm_get_member_ids( $role = '' ) {
		$transient_name = 'jm_member_ids_' . $role;

		if ( false !== ( $users = get_transient( $transient_name ) ) ) {
			return $users;
		}

		$users = get_users( array( 'role' => $role, 'fields' => 'ID' ) );

		set_transient( $transient_name, $users, DAY_IN_SECONDS );

		return $users;
	}
endif;

if( !function_exists( 'jm_remove_transient_members' ) ) :

	/**
	 * Remove users transient whenever a user is created or updated
	 * @param  int $user_id ID of the user
	 */
	function jm_remove_transient_members( $user_id ) {
		$role = Noo_Member::get_user_role( $user_id );
		delete_transient( 'jm_members_' . $role );
		delete_transient( 'jm_member_ids_' . $role );
	}
	add_action( 'profile_update', 'jm_remove_transient_members', 10, 1 );
	add_action( 'user_register', 'jm_remove_transient_members', 10, 1 );
endif;
