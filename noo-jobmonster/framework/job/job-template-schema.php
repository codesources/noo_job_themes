<?php
if( !function_exists( 'jm_job_single_page_schema' ) ) :
	function jm_job_single_page_schema( $schema = array() ) {
		if( is_singular( 'noo_job' ) ) {
			$schema['itemscope'] = '';
			$schema['itemtype'] = 'http://schema.org/JobPosting';
		}

		return $schema;
	}
	add_filter( 'noo_site_schema', 'jm_job_single_page_schema' );
endif;

if( !function_exists( 'jm_job_single_title_schema' ) ) :
	function jm_job_single_title_schema( $schema = array() ) {
		if( is_singular( 'noo_job' ) ) {
			$schema['itemprop'] = 'title';
		}

		return $schema;
	}
	add_filter( 'noo_page_title_schema', 'jm_job_single_title_schema' );
endif;
