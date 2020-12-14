	<div class="h2">Результаты голосования. Проект <?php echo $project->project_name; ?>.</div>

	<div class="h3">Итоги по вопросам. Вопросы основной повестки</div>
	<table class="table table-hover">
		<thead>
			<tr>
				<th>Номер</th>
				<th>Текст вопроса</th>
				<th>Кол-во Да</th>
				<th>Кол-во Нет</th>
				<th>Кол-во Воздерж</th>
				<th>Кол-во Не проголосовавших</th>
				<th>% Да</th>
				<th>% Нет</th>
				<th>% Воздерж.</th>
				<th>Всего голосов</th>
				<th>Принятое решение</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($project_general_answers as $key => $result) : ?>
				<tr>
					<td><?php echo $key; ?></td>
					<td><?php echo $result['qs_title']; ?></td>
					<td><?php echo $result['YesVotesNumber']; ?></td>
					<td><?php echo $result['NoVotesNumber']; ?></td>
					<td><?php echo $result['AbstainVotesNumber']; ?></td>
					<td><?php echo $result['MissedVotesNumber']; ?></td>
					<td><?php echo $result['YesVotesPercent']; ?></td>
					<td><?php echo $result['NoVotesPercent']; ?></td>
					<td><?php echo $result['AbstainVotesPercent']; ?></td>
					<td><?php echo $total_voices; ?></td>
					<td><?php echo $result['QuestionVotingResult']; ?></td>
				</tr>
				<?php endforeach ; ?>
		</tbody>
	</table>

	<div class="h3">Итоги по вопросам. Принятие дополнительной повестки</div>
	<table class="table table-hover">
		<thead>
			<tr>
				<th>Номер</th>
				<th>Текст вопроса</th>
				<th>Кол-во Да</th>
				<th>Кол-во Нет</th>
				<th>Кол-во Воздерж</th>
				<th>Кол-во Не проголосовавших</th>
				<th>% Да</th>
				<th>% Нет</th>
				<th>% Воздерж.</th>
				<th>Всего голосов</th>
				<th>Принятое решение</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($project_accept_additional_answers as $key => $result) : ?>
				<tr>
					<td><?php echo $key; ?></td>
					<td><?php echo $result['qs_title']; ?></td>
					<td><?php echo $result['YesVotesNumber']; ?></td>
					<td><?php echo $result['NoVotesNumber']; ?></td>
					<td><?php echo $result['AbstainVotesNumber']; ?></td>
					<td><?php echo $result['MissedVotesNumber']; ?></td>
					<td><?php echo $result['YesVotesPercent']; ?></td>
					<td><?php echo $result['NoVotesPercent']; ?></td>
					<td><?php echo $result['AbstainVotesPercent']; ?></td>
					<td><?php echo $total_voices; ?></td>
					<td><?php echo $result['QuestionVotingResult']; ?></td>
				</tr>
				<?php endforeach ; ?>
		</tbody>
	</table>

	<div class="h3">Итоги по вопросам. Вопросы дополнительной повестки</div>
	<table class="table table-hover">
		<thead>
			<tr>
				<th>Номер</th>
				<th>Текст вопроса</th>
				<th>Кол-во Да</th>
				<th>Кол-во Нет</th>
				<th>Кол-во Воздерж</th>
				<th>Кол-во Не проголосовавших</th>
				<th>% Да</th>
				<th>% Нет</th>
				<th>% Воздерж.</th>
				<th>Всего голосов</th>
				<th>Принятое решение</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($project_additional_answers as $key => $result) : ?>
				<tr>
					<td><?php echo $key; ?></td>
					<td><?php echo $result['qs_title']; ?></td>
					<td><?php echo $result['YesVotesNumber']; ?></td>
					<td><?php echo $result['NoVotesNumber']; ?></td>
					<td><?php echo $result['AbstainVotesNumber']; ?></td>
					<td><?php echo $result['MissedVotesNumber']; ?></td>
					<td><?php echo $result['YesVotesPercent']; ?></td>
					<td><?php echo $result['NoVotesPercent']; ?></td>
					<td><?php echo $result['AbstainVotesPercent']; ?></td>
					<td><?php echo $total_voices; ?></td>
					<td><?php echo $result['QuestionVotingResult']; ?></td>
				</tr>
				<?php endforeach ; ?>
		</tbody>
	</table>

	<div class="h3">Кто как проголосовал. Вопросы основной повестки</div>
	<?php foreach ($question_general_answers as $qs_title => $answers) : ?>
		<div class="panel panel-default panel-primary">
			<div class="panel-heading">
				<label>Вопрос:</label> <?php echo $qs_title; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $user_member_name => $ans): ?>
				<ul>
					<li class="list-group-item list-group-item-info">Участник: <?php echo $user_member_name ?></li>
					<li class="list-group-item">Кол-во госолов: <?php echo $ans['user_voices'] ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans['ans_value'] ?></li>
					<?php if (!empty($ans['ans_comment'])): ?><li class="list-group-item">
						Комментарий: <?php echo $ans['ans_comment']
						?></li><?php endif; ?>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>

	<div class="h3">Кто как проголосовал. Принятие вопросов дополнительной повестки</div>
	<?php foreach ($question_accept_additional_answers as $qs_title => $answers) : ?>
		<div class="panel panel-default panel-success">
			<div class="panel-heading">
				<label>Вопрос:</label> <?php echo $qs_title; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $user_member_name => $ans
				): ?>
				<ul>
					<li class="list-group-item list-group-item-info">Участник: <?php echo $user_member_name ?></li>
					<li class="list-group-item">Кол-во госолов: <?php echo $ans['user_voices'] ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans['ans_value'] ?></li>
					<?php if (!empty($ans['ans_comment'])): ?><li class="list-group-item">
						Комментарий: <?php echo $ans['ans_comment']
						?></li><?php endif; ?>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>

	<div class="h3">Кто как проголосовал. Вопросы дополнительной повестки</div>
	<!--p><?php echo "Total voices: $total_voices, half voices: $half_voices";?> </p-->
	<?php foreach ($question_additional_answers as $qs_title => $answers) : ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<label>Вопрос:</label> <?php echo $qs_title; ?>
			</div>
			<div class="panel-body">
				<?php foreach ($answers as $user_member_name => $ans): ?>
				<ul>
					<li class="list-group-item list-group-item-info">Участник: <?php echo $user_member_name ?></li>
					<li class="list-group-item">Кол-во госолов: <?php echo $ans['user_voices'] ?></li>
					<li class="list-group-item">Ответ: <?php echo $ans['ans_value'] ?></li>
					<?php if (!empty($ans['ans_comment'])): ?><li class="list-group-item">
						Комментарий: <?php echo $ans['ans_comment']
						?></li><?php endif; ?>
				</ul>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach ; ?>