<div class="panel panel-primary">
	<div class="panel-heading">
		Список проектов
	</div>

<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>Наименование</th>
			<th>Дата создания</th>
			<th>Начало ознакомления</th>
			<th>Начало голосования ОП</th>
			<th>Начало голосования ДП</th>
			<th>Завершение голосования</th>
			<th>Действие</th>
			<th>Действие</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($projects_query->getResult() as $result) : ?>
		<tr>
			<td>
				<?php echo anchor(base_url('project/edit/' . $result->project_id), $result->project_name) ;?>
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
			<td>
				<?php echo anchor(base_url('voteresult/index/' . $result->project_id), "Итоги") ;?>
			</td>
			<td>
				<?php echo anchor(base_url('project/delete_project/' . $result->project_id), "Удалить") ;?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>
</div>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Добавьте новый проект
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

		<?php echo form_open('project/index', 'role="form", enctype="multipart/form-data"'); ?>
			<div class="form-group">

				<label for="projectName">Наименование собрания
				</label>
				<textarea class="form-control" rows="1" name="projectName" id="projectName"><?php
					echo set_value('projectName'); ?></textarea>

			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success">Добавить проект
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>