<div class="h2"> Проект: <?php echo $project->project_name; ?></div>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		<?php echo lang('app.documents_title');?>
	</div>
	<div class="panel-body">
		<?php foreach($documents_query->getResult() as $result) : ?>
			<ul>
				<li class="list-group-item">
				<label>Документ:</label> <?php
					$cpt = $result->doc_caption ?? $result->doc_filename;
					echo $cpt; ?>

				<?php echo anchor(base_url('document/download/' . $result->doc_id),
					lang('app.document_download')) ;?>
				</li>
			</ul>
		<?php endforeach ; ?>
	</div>
</div>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Вопросы основной повестки.
	</div>
	<div class="panel-body">
		<?php foreach($general_questions as $question) : ?>
			<ul>
				<li class="list-group-item list-group-item-info">
					<label>Вопрос:</label> <?php echo $question['qs_title']; ?>
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
		<?php echo lang('app.documents_additional_questions_list_title');?>
	</div>
	<div class="panel-body">
		<?php foreach($additional_questions as $question) : ?>
		<ul>
			<li class="list-group-item list-group-item-info">
				<label>Вопрос:</label> <?php echo $question['qs_title']; ?>
			</li>
			<?php $comment = $question['qs_comment']; if(!empty($comment)): ?>
				<li class="list-group-item">
					<label>Описание:</label><?php echo $comment; ?></li>
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
			echo base_url('additionalquestions');
		} else {
			echo '#';
		}?>'">
	<?php echo lang('app.votes_goto_additionalquestions')?>
</button>
<hr>
<p>
	<?php echo "Текущее время: $current_date, статус ознакомления: $acquaintance_stage_state";?>
</p>
