<!-- Form - begin form section -->
<p class="h4">Редактирование проекта.</p>

<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open('project/edit/'.$project_query->project_code, 'role="form"') ; ?>
	<div class="form-group">
		<label for="project_code">Код проекта
		</label>
		<textarea class="form-control" rows="1" name="project_code" id="project_code"><?php
			echo set_value('project_code', $project_query->project_code); ?></textarea>

		<label for="project_code">Название проекта
		</label>
		<textarea class="form-control" rows="1" name="project_name" id="project_name"><?php
			echo set_value('project_name', $project_query->project_name); ?></textarea>
	</div>
	<div class="form-group">
		<button type="submit" class="btn btn-success">Сохранить
		</button>
	</div>
<?php echo form_close(); ?>
<hr>

<div class="panel panel-default">
	<div class="panel-heading">
		<?php echo lang('app.documents_title');?>
	</div>

	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Наименование</th>
				<th>Действие</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($documents_query->getResult() as $result) : ?>
			<tr>
				<td>
					<?php $cpt = $result->doc_caption ?? $result->doc_filename;
						echo $cpt; ?>
				</td>
				<td>
					<?php echo anchor(base_url('document/download/' . $result->doc_id),
					lang('app.document_download')) ;?>
				</td>
			</tr>
			<?php endforeach ; ?>
		</tbody>
	</table>
</div>

<?php echo anchor(base_url('project/edit_document/' . $project_query->project_code), 'Редактировать список документов') ?>
<hr>
<div class="panel panel-default">
	<div class="panel-heading">
		Вопросы основной повестки
	</div>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Текст</th>
				<th>Комментарий</th>
				<th>Документы</th>
			</tr>			
		</thead>
		<?php foreach($base_questions as $qs_id => $question) : ?>
		<tbody>
			<tr>
			<td><?php echo $question['qs_title']; ?></td>
			<td><?php echo $question['qs_comment']; ?></td>
			<td>
				<?php foreach($question['documents'] as $doc_id=>$qd) : ?>
					<?php echo '['.anchor(base_url('document/download/' . $doc_id),
						$qd['doc_filename']).']' ;?>
					</li>
				<?php endforeach ; ?>			
			</td>
			</tr>
		</tbody>
		<?php endforeach ; ?>
	</table>
</div>
<?php echo anchor(base_url('project/edit_basequestion/' . $project_query->project_code), 'Редактировать список вопросов основной повестки') ?>
<hr>
<div class="panel panel-default">
	<div class="panel-heading">
		Участники собрания
	</div>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Логин</th>
				<th>Тип</th>
				<th>Голосует</th>
				<th>Кол-во голосов</th>
				<th>Имя</th>
			</tr>			
		</thead>
		<?php foreach($users_query->getResult() as $result) : ?>
		<tbody>
			<tr>
			<td><?php echo $result->user_login_code; ?></td>
			<td><?php echo $result->usertype_name; ?></td>
			<td><?php echo $result->user_can_vote ? 'Да' : 'Нет'; ?></td>
			<td><?php echo $result->user_votes_number; ?></td>
			<td><?php echo $result->user_member_name; ?></td>
			</tr>
		</tbody>
		<?php endforeach ; ?>
	</table>
</div>
<?php echo anchor(base_url('project/edit_user/' . $project_query->project_code), 'Редактировать список участников собрания') ?>
<hr>
<div class="h4">
	Удалить текущий проект: <?php echo anchor(base_url('/project/delete_project/'
		.$project_query->project_id), 'да, удалить!') ?>
</div>
<hr>
<div class="h4">
	<?php echo anchor(base_url('/project/index'), '<== Вернуться к списку проектов')?>
</div>