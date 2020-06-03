	<div class="h2">Результаты голосования. Проект <?php echo $project->project_name; ?>.</div>
	<div class="h3">Вопросы основной повестки</div>
	<?php foreach ($users_list as $user_id => $answers) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Пользователь:</label> <?php echo $user_id; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $qs_title => $ans_value): ?>
				<ul>
					<li class="list-group-item">Вопрос: <?php echo $qs_title ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans_value ?></li>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>
