<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open('votes/index', 'role="form"') ; ?>
	<div class="h3">Вопросы основной повестки</div>
	<?php foreach ($questions_query->getResult() as $question_result) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Вопрос:</label> <?php echo $question_result->qs_title; ?>
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label>Ответ:</label>
					<div class="radio">
						<label class="radio-inline" for="yes-<?php echo $question_result->qs_id; ?>">
						<input
							type="radio" 
							value="0"
							id="yes-<?php echo $question_result->qs_id; ?>"
							name="optradio[<?php echo $question_result->qs_id; ?>]"
							<?php if (set_value("optradio[$question_result->qs_id]", "$question_result->ans_number") === "0") {echo 'checked';}?>
							>Да</label>
						<label class="radio-inline"><input 
							type="radio" 
							value="1"
							id="no"
							name="optradio[<?php echo $question_result->qs_id; ?>]"
							<?php if (set_value("optradio[$question_result->qs_id]", "$question_result->ans_number") === "1") {echo 'checked';}?>
							>Нет</label>
						<label class="radio-inline"><input 
							type="radio" 
							value="2"
							id="abstain"
							name="optradio[<?php echo $question_result->qs_id; ?>]"
							<?php if (set_value("optradio[$question_result->qs_id]", "$question_result->ans_number") === "2") {echo 'checked';}?>
							>Воздержался</label>
					</div>
					<label>Комментарий:</label>
					<textarea
						class="form-control" rows="1" 
						id="comment"
						name="comment[<?php echo $question_result->qs_id; ?>]" 
						><?php 
							echo set_value("comment[$question_result->qs_id]",
							"$question_result->ans_comment"); ?></textarea>
				</div>

			</div>
		</div>
	<?php endforeach ; ?>

	<div class="h3">Принятие вопросов дополнительной повестки</div>
	<?php foreach ($accept_additional_question_query->getResult() as $accept_result) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Доп. вопрос:</label> <?php echo $accept_result->qs_title; ?>
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label>Ответ:</label>
					<div class="radio">
						<label class="radio-inline" for="yes-<?php echo $accept_result->qs_id; ?>">
						<input
							type="radio" 
							value="0"
							id="yes-<?php echo $accept_result->qs_id; ?>"
							name="optradio[<?php echo $accept_result->qs_id; ?>]"
							<?php if (set_value("optradio[$accept_result->qs_id]", "$accept_result->ans_number") === "0") {echo 'checked';}?>
							>Да</label>
						<label class="radio-inline"><input 
							type="radio" 
							value="1"
							id="no"
							name="optradio[<?php echo $accept_result->qs_id; ?>]"
							<?php if (set_value("optradio[$accept_result->qs_id]", "$accept_result->ans_number") === "1") {echo 'checked';}?>
							>Нет</label>
						<label class="radio-inline"><input 
							type="radio" 
							value="2"
							id="abstain"
							name="optradio[<?php echo $accept_result->qs_id; ?>]"
							<?php if (set_value("optradio[$accept_result->qs_id]", "$accept_result->ans_number") === "2") {echo 'checked';}?>
							>Воздержался</label>
					</div>
					<label>Комментарий:</label>
					<textarea
						class="form-control" rows="1" 
						id="comment"
						name="comment[<?php echo $accept_result->qs_id; ?>]" 
						><?php 
							echo set_value("comment[$accept_result->qs_id]",
							"$accept_result->ans_comment"); ?></textarea>
				</div>

			</div>
		</div>
	<?php endforeach ; ?>

<?php if ($opened_questions_count == 0):?>
<p>
	Вы уже проголосовали.
</p>
<?php endif;?>

<?php if ($main_agenda_stage_state == 'early'): ?>
	<p>
		Этап голосования по основной повестке ещё не начался.
	</p>
<?php elseif ($main_agenda_stage_state == 'late'): ?>
	<p>
		Этап голосования по основной повестке уже закончился.
	</p>
<?php endif; ?>

<div class="form-group">
	<button type="submit" class="btn btn-success" <?php 
		if ( ($opened_questions_count == 0) || ($main_agenda_stage_state != 'active') ) {
		echo 'disabled';}?> >
		<?php echo lang('app.votes_send_button'); ?>
	</button>
</div>
<?php echo form_close(); ?>
<hr>
<p>
	<?php echo "Текущее время: $current_date, статус ознакомления: $main_agenda_stage_state";?>
</p>