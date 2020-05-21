<table class="table table-hover">
	<thead>
		<tr>
			<th><?php echo $this->lang->line('documents_title');?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($documents_query->result() as $result) : ?>
		<tr>
			<td>
				<?php echo $result->doc_filename; ?>

				<?php echo anchor('document/download/' . $result->doc_id,
				$this->lang->line('document_download')) ;?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>

<table class="table table-hover">
	<thead>
		<tr>
			<th><?php echo $this->lang->line('documents_general_questions_list_title');?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($general_questions_query->result() as $result) : ?>
		<tr>
			<td>
				<?php echo $result->qs_title; ?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>

<table class="table table-hover">
	<thead>
		<tr>
			<th><?php echo $this->lang->line('documents_additional_questions_list_title');?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($additional_questions_query->result() as $result) : ?>
		<tr>
			<td>
				<?php echo $result->qs_title; ?>
			</td>
		</tr>
		<?php endforeach ; ?>
	</tbody>
</table>

<?php if ($acquaintance_stage_state == 'early'): ?>
	<p>
		Этап ознакомления с документами ещё не начался. Дополнительные вопросы добавлять нельзя.
	</p>
<?php elseif ($acquaintance_stage_state == 'late'): ?>
	<p>
		Этап ознакомления с документами завершён. Дополнительные вопросы добавлять нельзя.
	</p>
<?php endif; ?>

<button type="button" class="btn btn-link <?php 
	if ($acquaintance_stage_state != 'active') {echo 'disabled';} ?>"
	onclick="window.location.href='<?php 
		if ($acquaintance_stage_state == 'active') {
			echo site_url('additionalquestions');
		} else {
			echo '#';
		}?>'">
	<?php echo $this->lang->line('votes_goto_additionalquestions')?>
</button>
<hr>
<p>
	<?php echo "Текущее время: $current_date, статус ознакомления: $acquaintance_stage_state";?>
</p>
