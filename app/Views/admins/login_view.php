<?php if ($login_name_correct != true) : ?>
	<div class="alert alert-danger"><?php 
		echo lang('app.admin_login_error'); ?>
	</div>
<?php elseif ($login_password_correct != true) : ?>
	<div class="alert alert-danger"><?php 
		echo lang('app.admin_password_error'); ?>
	</div>
<?php endif; ?>
<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<div class="container">
	<?php echo form_open('Admin/login', 'class="form-singin" role="form"'); ?>
	<h2 class="form-signin-heading"><?php
		echo lang('app.admin_login_header'); ?>
	</h2>
	<input name="admin_login_name" class="form-control"
		placeholder="<?php echo lang('app.admin_login_name'); ?>"
		required autofocus>
	<input name="admin_login_password" class="form-control"
		placeholder="<?php echo lang('app.admin_login_password'); ?>"
		required>
	<button class="btn btn-lg btn-primary btn-block" type="submit"><?php
		echo lang('app.admin_login_signin'); ?>
	</button>
	<?php echo form_close(); ?>
</div>
<hr>
<div class="btn btn-default">
	<?php echo anchor(base_url('admin/add'), 'Новая учётная запись...') ?>
</div>