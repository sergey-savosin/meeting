<!-- Form - begin form section -->
<p class="h4">Редактирование проекта - {project name}. Вопросы ОП.</p>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Вопросы основной повестки.
	</div>
	<div class="panel-body">
		<?php foreach ($base_questions as $question) : ?>
			<ul>
			<li class="list-group-item list-group-item-info">
				<label>Вопрос:</label> <?php
					$qs_id = $question['qs_id']; echo $question['qs_title'] ?>
			</li>
				<?php if(!empty($question['qs_comment'])): ?>
					<li class="list-group-item">
						<label>Описание:</label> <?php echo $question['qs_comment']; ?>
					</li>
				<?php endif; ?>
				<?php foreach($question['documents'] as $doc_id=>$qd) : ?>
					<li class="list-group-item">
						<label>Документ:</label> <?php echo $qd['doc_filename'] ?>
					<?php echo anchor(base_url('document/download/' . $doc_id),
						lang('app.document_download')) ;?>
					</li>
				<?php endforeach ; ?>
			</ul>
		<?php endforeach ; ?>
	</div>
</div>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Добавьте вопрос ОП
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

		<?php echo form_open('project/edit_basequestion', 'role="form", enctype="multipart/form-data"'); ?>
			<div class="form-group">
				<?php echo form_hidden('ProjectId', $project_query->project_id) ?>
				<?php echo form_hidden('ProjectCode', $project_query->project_code) ?>

				<label for="QsTitle">Текст вопроса
				</label>
				<textarea class="form-control" rows="1" name="QsTitle" id="QsTitle"><?php
					echo set_value('QsTitle'); ?></textarea>

				<label for="QsComment">Комментарий
				</label>
				<textarea class="form-control" rows="1" name="QsComment" id="QsComment"><?php
					echo set_value('QsComment'); ?></textarea>
				<label for="documentFile"> Выберите файл
				</label>
				<?php $data = [
					'name' => 'documentFile[]',
					'id' => 'documentFile',
					'accept' => 'image/png, image/jpeg']; 
				echo form_upload($data); ?>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success">Добавить файл
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>

<div class="h4">
	<?php echo anchor(base_url('/project/edit/'.$project_query->project_code), 'Вернуться к настройке проекта')?>
</div>
<hr>