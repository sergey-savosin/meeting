<div class="panel panel-default panel-success">
	<div class="panel-heading">
		<?php echo lang('app.additional_questions_list_header') ?>
	</div>
	<div class="panel-body">
		<?php foreach ($additional_questions as $question) : ?>
		<ul>
			<li class="list-group-item list-group-item-info">
				<label>Вопрос: </label> <?php echo $question['qs_title']; ?>
			</li>
			<?php $comment = $question['qs_comment']; if(!empty($comment)) : ?>
				<li class="list-group-item">
					<label>Описание: </label> <?php echo $comment; ?>
				</li>
			<?php endif; ?>
			<?php foreach($question['documents'] as $doc_id => $qd) : ?>
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

<!-- Form - begin form section -->
<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		<?php echo lang('app.additional_questions_form_instruction');?>
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>
		
		<?php echo form_open('additionalquestions/index',
					'role="form", enctype="multipart/form-data"') ; ?>
			<div class="form-group">
				<label for="qs_title"><?php
					echo lang('app.additional_questions_title'); ?>
				</label>
				<textarea class="form-control" rows="1" name="qs_title" id="qs_title"><?php
					echo set_value('qs_title'); ?></textarea>
				<label for="qs_comment"> Комментарий
				</label>
				<textarea class="form-control" rows="2" name="qs_comment" id="qs_comment"><?php
					echo set_value('qs_comment'); ?></textarea>

				<label for="documentFile"> Выберите файл
				</label>
				<?php $data = [
					'name' => 'documentFile[]',
					'id'=>'documentFile',
					'accept'=>'image/png, image/jpeg',
					'multiple'=>'true'
				]; 
				echo form_upload($data); ?>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success"><?php
					echo lang('app.question_send_button'); ?>
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>

<div class="h4">
	<?php echo anchor(base_url('/'), lang('app.additional_questions_goto_documents'))?>
</div>
<hr>