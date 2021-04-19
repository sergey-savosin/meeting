<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Добавление учётной записи
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

		<?php echo form_open('admin/add', 'role="form", enctype="multipart/form-data"'); ?>
			<div class="form-group">

				<label for="projectName">Имя учётной записи
				</label>
				<textarea 
					class="form-control" rows="1" name="admin_name" 
					id="admin_name"><?php
					echo set_value('admin_name'); ?></textarea>

				<label for="projectName">Пароль
				</label>
				<textarea 
					class="form-control" rows="1" name="admin_password" 
					id="admin_password"><?php
					echo set_value('admin_password'); ?></textarea>

			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success">Добавить учётную запись
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>