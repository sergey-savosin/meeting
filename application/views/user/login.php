<?php if (isset($login_fail)) : ?>
	<div class="alert alert-danger"><?php 
		echo $this->lang->line('user_login_error'); ?>
	</div>
<?php endif; ?>
<?php echo validation_errors(); ?>

<div class="container">
	<?php echo form_open('user/login', 'class="form-singin", role="form"'); ?>
	<h2 class="form-signin-heading"><?php
		echo $this->lang->line('user_login_header'); ?>
	</h2>
	<input name="usr_code" class="form-control"
		placeholder="<?php echo $this->lang->line('user_login_code'); ?>"
		required autofocus>
	<button class="btn btn-lg btn-primary btn-block" type="submit"><?php
		echo $this->lang->line('user_login_signin'); ?>
	</button>
	<?php echo form_close(); ?>
</div>
