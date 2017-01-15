<?php

if( !function_exists( 'jm_get_application_default_fields' ) ) :
	function jm_get_application_default_fields() {
		$default_fields = array(
			'application_message' => array(
					'name' => 'application_message',
					'label' => __('Message', 'noo'),
					'type' => 'textarea',
					'allowed_type' => array(
						'textarea'			=> __('Textarea', 'noo')
					),
					'value' => __('Your cover letter/message sent to the employer','noo'),
					'is_default' => true,
					'required' => true
				),
		);

		return apply_filters( 'jm_application_default_fields', $default_fields );
	}
endif;
