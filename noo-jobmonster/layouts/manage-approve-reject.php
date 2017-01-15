<?php
$approve_title = __('Congratulation! Your resume passed our application round.','noo');
$reject_message = __("Hi,\nWe received your application for the job and found your skills and experience does not match our requirement. Thank you for your interest in our vacancy and good luck in your future career.\nBest regards.",'noo');
$reject_title = __('Unfortunately! Your resume didn\'t pass our application round.','noo');
$approve_message = __("Congratulation! \nWe received your application for the job and found your skills and experience matched our requirement. We will contact you for detail of the second selection round soon.\nBest regards.",'noo');
?>
<div id="memberModalApplication" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="memberModalApplicationLabel" aria-hidden="true">
	<div class="modal-dialog">
    	<div class="modal-content">
			<div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title text-center" id="memberModalApplicationLabel"><?php echo sprintf(__('Response to %s','noo'),$display); ?></h4>
		     </div>
		      <div class="modal-body">
		      	<form id="noo-ajax-approve-reject-application-form" method="post" class="form-horizontal">
					<div style="display: none">
						<input type="hidden" name="application_id" value="<?php echo esc_attr($application_id)?>">
						<input type="hidden" name="action" value="<?php echo esc_attr($type)?>">
						<?php wp_nonce_field('application-manage-action')?>
					</div>
					<div class="form-group text-center noo-ajax-result" style="display: none"></div>
					<div class="form-group required-field">
					 	<input type="text" class="form-control jform-validate" id="title" value="<?php echo esc_attr($type == 'approve' ? $approve_title : $reject_title) ?>" name="title" autofocus required placeholder="<?php echo esc_attr__('Title','noo')?>">
					 </div>
					 <div class="form-group required-field">
					 	<textarea class="form-control jform-validate" id="message" required placeholder="<?php esc_attr_e('Message','noo')?>" rows="5" name="message"><?php echo ($type == 'approve' ? $approve_message : $reject_message) ?></textarea>	
					 </div>
					 <div class="modal-actions text-center">
						<button class="btn btn-primary" type="submit"><?php echo esc_attr($type == 'approve' ? __('Approve','noo') : __('Reject','noo')) ?></button>
					</div>
				 </form>
		      </div>
		</div>
	</div>
</div>
