<table class="table table-hover">
	<thead>
		<tr>
			<th class="h4"><?php echo lang('app.additional_questions_list_header') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($additional_questions_query->getResult() as $question_result) : ?>
		<tr>
			<td>
				<?php echo $question_result->qs_title; ?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>

<!-- <p class="lead"><?php echo lang('app.additional_questions_list_header') ?></p>
<?php foreach ($additional_questions_query->getResult() as $question_result) : ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo $question_result->qs_title; ?>
		</div>
	</div>
<?php endforeach ; ?> -->

<!-- Form - begin form section -->
<p class="h4"><?php echo lang('app.additional_questions_form_instruction');?></p>

<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open('additionalquestions/index', 'role="form"') ; ?>
	<div class="form-group">
		<label for="qs_title"><?php echo lang('app.additional_questions_title'); ?>
		</label>
		<textarea class="form-control" rows="3" name="qs_title" id="qs_title"><?php
			echo set_value('qs_title'); ?></textarea>
	</div>
	<div class="form-group">
		<button type="submit" class="btn btn-success"><?php
		echo lang('app.question_send_button'); ?>
		</button>
	</div>
<?php echo form_close(); ?>

<div class="h4">
	<?php echo anchor(base_url('/'), lang('app.additional_questions_goto_documents'))?>
</div>
<hr>