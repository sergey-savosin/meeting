<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open('additionalagendavotes/index', 'role="form"') ; ?>
	<div class="h3">Вопросы дополнительной повестки</div>
	<?php foreach ($questions as $question) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Вопрос:</label> <?php $qs_id = $question['qs_id']; echo $question['qs_title'] ?>
				<?php if (!empty($question['qs_comment'])): ?>
					<br>
					<label>Описание:</label> <?php echo $question['qs_comment'];?>
				<?php endif; ?>
				<?php if (!empty($question['documents'])): ?>
					<br>
					<label>Документы:</label>
					<?php foreach ($question['documents'] as $doc_id => $doc): ?>
						<?php echo '['.anchor(base_url('document/download/' . $doc_id),
							$doc['doc_filename']).']' ;?>
					<?php endforeach; ?>

				<?php endif ?>
			</div>
			<div class="panel-body">
				<div class="form-group">
					<label>Ответ:</label>
					<div class="radio">
						<label class="radio-inline" 
							for="yes-<?php echo $qs_id; ?>">
						<input
							type="radio" 
							value="0"
							id="yes-<?php echo $qs_id; ?>"
							name="optradio[<?php echo $qs_id; ?>]"
							<?php if (set_value("optradio[$qs_id]",
								$question['ans_number']) === "0") {echo 'checked';}?>
							>Да</label>
						<label class="radio-inline"><input 
							type="radio" 
							value="1"
							id="no"
							name="optradio[<?php echo $qs_id; ?>]"
							<?php if (set_value("optradio[$qs_id]",
								$question['ans_number']) === "1") {echo 'checked';}?>
							>Нет</label>
						<label class="radio-inline"><input 
							type="radio" 
							value="2"
							id="abstain"
							name="optradio[<?php echo $qs_id; ?>]"
							<?php if (set_value("optradio[$qs_id]",
								$question['ans_number']) === "2") {echo 'checked';}?>
							>Воздержался</label>
					</div>
					<label>Комментарий:</label>
					<textarea
						class="form-control" rows="1" 
						id="comment"
						name="comment[<?php echo $qs_id; ?>]" 
						><?php 
							echo set_value("comment[$qs_id]",
							$question['ans_comment']); ?></textarea>
				</div>

			</div>
		</div>

	<?php endforeach ; ?>

<?php if ($opened_questions_count == 0):?>
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
		if ( ($opened_questions_count == 0) || ($additional_agenda_stage_state != 'active') )
		{echo 'disabled';}?> >
		<?php echo lang('app.votes_send_button'); ?>
	</button>
</div>
<?php echo form_close(); ?>
<hr>
<p>
	<?php echo "Текущее время: $current_date, статус ознакомления: $additional_agenda_stage_state";?>
</p>
