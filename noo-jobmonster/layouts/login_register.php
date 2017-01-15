<?php 
$can_register = Noo_Member::can_register();
$form = 'login';
if((isset($_GET['action']) && $_GET['action'] === 'register') || (isset($_GET['mode']) && $_GET['mode'] === 'register') ){
	$form = Noo_Member::can_register() ? 'register' : 'login';
}

?>
<div class="jpanel jpanel-login">
	<div class="jpanel-title">
		<h3><?php echo $can_register ? __('Login or create an account','noo') : __('Login','noo');?></h3>
	</div>
	<div class="jpanel-body">
		<div class="account-actions">
			<?php if($can_register):?>
				<a href="<?php echo esc_url(add_query_arg(array('action'=>'login','mode'=>'register')));?>#jform" class="btn btn-<?php echo 'register' == $form ? 'primary': 'default'; ?>"><?php _e('Register','noo')?></a>
				<a href="<?php echo esc_url(add_query_arg(array('action'=>'login','mode'=>'login')))?>#jform" class="btn btn-<?php echo 'login' == $form ? 'primary': 'default'; ?>"><span class="hidden-xs"><?php _e('Already have account ? ','noo')?></span><span class="login-label"><?php _e('Login','noo')?></span></a>
			<?php endif;?>
		</div>
		<div class="account-form">
			<?php if('login' == $form):?>
				<div class="account-log-form">
					<?php Noo_Member::ajax_login_form(__('Continue','noo'));?>
				</div>
			<?php endif;?>
			<?php if('register' == $form):?>
				<div class="account-reg-form">
					<?php Noo_Member::ajax_register_form(__('Continue','noo'))?>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>
