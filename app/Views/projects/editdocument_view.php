<!-- Form - begin form section -->
<p class="h4">Проект "<?php
	echo $project_query->project_name?>". Редактирование документов.</p>

<div class="panel panel-default panel-success">
	<div class="panel-heading">
		Список документов
	</div>
	<div class="panel-body">
		<?php foreach($documents_query->getResult() as $result) : ?>
		<ul>
			<li class="list-group-item list-group-item-info">
				<?php $cpt = $result->doc_caption ?? $result->doc_filename;
					echo $cpt; ?>

				<?php echo anchor(base_url('document/download/' . $result->doc_id),
				lang('app.document_download')) ;?>
				<?php echo anchor(base_url('project/delete_document/' . $result->doc_id),
					'Удалить'); ?>
			</li>
		</ul>
		<?php endforeach ; ?>
	</div>
</div>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Добавьте документ
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

		<?php echo form_open('project/edit_document', 'role="form", enctype="multipart/form-data"'); ?>
			<div class="form-group">
				<?php echo form_hidden('ProjectId', $project_query->project_id) ?>
				<?php echo form_hidden('ProjectCode', $project_query->project_code) ?>

				<label for="DocCaption">Название документа
				</label>
				<textarea class="form-control" rows="1" name="DocCaption" id="DocCaption"><?php
					echo set_value('DocCaption'); ?></textarea>


				<?php $data = [
					'name' => 'documentFile[]',
					'id' => 'documentFile',
					'accept' => 'image/png, image/jpeg']; 
				echo form_upload($data); ?>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success">Добавить документ
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>

<div class="h4">
	<?php echo anchor(base_url('/project/edit/'.$project_query->project_code), 'Вернуться к настройке проекта')?>
</div>
<hr>