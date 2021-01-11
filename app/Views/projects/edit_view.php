<!-- Form - begin form section -->
<p class="h4">Редактирование проекта.</p>

<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open('project/edit', 'role="form"') ; ?>
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

<p class="h4">Документы</p>

<table class="table table-hover">
	<thead>
		<tr>
			<th><?php echo lang('app.documents_title');?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($documents_query->getResult() as $result) : ?>
		<tr>
			<td>
				<?php $cpt = $result->doc_caption ?? $result->doc_filename;
					echo $cpt; ?>

				<?php echo anchor(base_url('document/download/' . $result->doc_id),
				lang('app.document_download')) ;?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>

<?php echo anchor(base_url('project/edit_document/' . $project_query->project_code), 'Редактировать список документов') ?>
<hr>
<p class="h4">Вопросы основной повестки</p>
<hr>
<p class="h4">Участники голосования</p>
