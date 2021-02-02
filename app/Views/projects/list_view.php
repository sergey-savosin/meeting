<p>Список проектов:</p>

<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>Наименование</th>
			<th>Код</th>
			<th>Дата создания</th>
			<th>Начало ознакомления</th>
			<th>Начало голосования ОП</th>
			<th>Начало голосования ДП</th>
			<th>Завершение голосования</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($projects_query->getResult() as $result) : ?>
		<tr>
			<td>
				<?php echo $result->project_name ;?>
			</td>
			<td>
				<?php echo anchor(base_url('project/edit/' . $result->project_code), $result->project_code) ;?>
			</td>
			<td>
				<?php echo $result->project_created_at; ?>
			</td>
			<td>
				<?php echo $result->project_acquaintance_start_date; ?>
			</td>
			<td>
				<?php echo $result->project_main_agenda_start_date; ?>
			</td>
			<td>
				<?php echo $result->project_additional_agenda_start_date; ?>
			</td>
			<td>
				<?php echo $result->project_meeting_finish_date; ?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Добавьте новый проект
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

		<?php echo form_open('project/index', 'role="form", enctype="multipart/form-data"'); ?>
			<div class="form-group">

				<label for="projectName">Название проекта
				</label>
				<textarea class="form-control" rows="1" name="projectName" id="projectName"><?php
					echo set_value('projectName'); ?></textarea>

				<label for="projectCode">Код проекта
				</label>
				<textarea class="form-control" rows="1" name="projectCode" id="projectCode"><?php
					echo set_value('projectCode'); ?></textarea>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success">Добавить проект
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>