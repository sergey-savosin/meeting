	<div class="h2">Результаты голосования. Проект <?php echo $project->project_name; ?>.</div>
	<div class="h3">Вопросы основной повестки</div>
	<?php foreach ($general_answers as $user_id => $answers) : ?>
		<div class="panel panel-default panel-primary">
			<div class="panel-heading">
				<label>Пользователь:</label> <?php echo $user_id; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $qs_title => $ans_value): ?>
				<ul>
					<li class="list-group-item list-group-item-info">Вопрос: <?php echo $qs_title ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans_value ?></li>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>

	<div class="h3">Принятие вопросов дополнительной повестки</div>
	<?php foreach ($accept_additional_answers as $user_id => $answers) : ?>
		<div class="panel panel-default panel-success">
			<div class="panel-heading">
				<label>Пользователь:</label> <?php echo $user_id; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $qs_title => $ans_value): ?>
				<ul>
					<li class="list-group-item list-group-item-info">Вопрос: <?php echo $qs_title ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans_value ?></li>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>

	<div class="h3">Вопросы дополнительной повестки</div>
	<!--p><?php echo "Total voices: $total_voices, half voices: $half_voices";?> </p-->
	<?php foreach ($additional_answers as $user_id => $answers) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Пользователь:</label> <?php echo $user_id; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $qs_title => $ans): ?>
				<ul>
					<li class="list-group-item list-group-item-info">Вопрос: <?php echo $qs_title ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans['ans_value']
						/*.'(y '.$ans['yes_count']
						.' n '.$ans['no_count']
						.' dbt '.$ans['doubt_count']
						.')'*/ ?></li>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>