<?php echo validation_errors(); ?>
<?php echo form_open('additionalagendavotes/index', 'role="form"') ; ?>
	<?php foreach ($questions_query->result() as $question_result) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Вопрос:</label> <?php echo "$question_result->qs_title" ?> 
				<!--?php echo "([id: $question_result->qs_id], ".
				"yes=$question_result->ans_yes:no=$question_result->ans_no:doubt=$question_result->ans_total)"; ?-->
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
				</div>

			</div>
		</div>
		<!--<?php echo form_hidden("qs_id[$question_result->qs_id]", $question_result->qs_id); ?> -->

	<?php endforeach ; ?>

<?php if ($opened_questions_count->cnt == 0):?>
<p>
	Вы уже проголосовали.
</p>
<?php endif;?>

<?php if ($additional_agenda_stage_state == 'early'): ?>
	<p>
		Этап голосования по дополнительной повестке ещё не начался.
	</p>
<?php elseif ($additional_agenda_stage_state == 'late'): ?>
	<p>
		Этап голосования по дополнительной повестке уже закончился.
	</p>
<?php endif; ?>

<div class="form-group">
	<button type="submit" class="btn btn-success" <?php 
		if ( ($opened_questions_count->cnt == 0) || ($additional_agenda_stage_state != 'active') )
		{echo 'disabled';}?> >
		<?php echo $this->lang->line('votes_send_button'); ?>
	</button>
</div>
<?php echo form_close(); ?>
<hr>
<p>
	<?php echo "Текущее время: $current_date, статус ознакомления: $additional_agenda_stage_state";?>
</p>
