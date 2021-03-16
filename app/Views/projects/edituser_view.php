<!-- Form - begin form section -->
<p class="h4">Проект "<?php
	echo $project_query->project_name?>". Редактирование списка участников.</p>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Участники собрания
	</div>
	<div class="panel-body">
		<?php foreach ($users_query->getResult() as $user) : ?>
		<ul>
			<li class="list-group-item list-group-item-info">
				<label>Логин:</label> <?php
					echo $user->user_login_code;
					echo ' '.anchor(base_url('project/delete_user/'.$user->user_id), '[Удалить]');?>
			</li>
			<li class="list-group-item">
				<label>Имя участника:</label> <?php echo $user->user_member_name; ?>
			</li>

			<li class="list-group-item">
				<label>Тип участника:</label> <?php echo $user->usertype_name; ?>
			</li>

			<li class="list-group-item">
				<label>Может ли голосовать:</label> <?php echo $user->user_can_vote ? "Да" : "Нет"; ?>
			</li>

			<li class="list-group-item">
				<label>Количество голосов:</label> <?php echo $user->user_votes_number; ?>
			</li>

		</ul>
		<?php endforeach ; ?>
	</div>
</div>

<div class="panel panel-default panel-primary">
	<div class="panel-heading">
		Добавьте участника собрания
	</div>
	<div class="panel-body">
		<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

		<?php echo
			form_open('project/edit_user/'.$project_query->project_id
					, 'role="form", enctype="multipart/form-data"'); ?>
			<div class="form-group">
				<?php echo form_hidden('ProjectId', $project_query->project_id) ?>

				<label for="UserMemberName">Имя участника
				</label>
				<textarea class="form-control" rows="1" name="UserMemberName" id="UserMemberName"><?php
					echo set_value('UserMemberName'); ?></textarea>

				<label for="UserTypeId">Тип участника
				</label>
				<div>
					<label class="radio-inline"><input 
						type="radio" 
						value="1"
						id="Creditor"
						name="UserTypeId"
						<?php if (set_value("UserTypeId") === '1') {echo 'checked';}?>
						>Creditor</label>
					<label class="radio-inline"><input 
						type="radio" 
						value="2"
						id="Debtor"
						name="UserTypeId"
						<?php if (set_value("UserTypeId") === '2') {echo 'checked';}?>
						>Debtor</label>
					<label class="radio-inline"><input 
						type="radio" 
						value="3"
						id="Manager"
						name="UserTypeId"
						<?php if (set_value("UserTypeId") === '3') {echo 'checked';}?>
						>Manager</label>
				</div>
				<label for="UserCanVote">Может ли голосовать
				</label>
				<div>
					<input type="checkbox" name="UserCanVote"
						value="canVote" <?= set_checkbox('UserCanVote', 'canVote') ?>
						/>
				</div>

				<label for="UserVotesNumber">Количество голосов
				</label>
				<textarea class="form-control" rows="1" name="UserVotesNumber" id="UserVotesNumber"><?php
					echo set_value('UserVotesNumber'); ?></textarea>

			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success">Добавить участника
				</button>
			</div>
		<?php echo form_close(); ?>
	</div>
</div>

<div class="btn btn-default">
	<?php echo anchor(base_url('/project/edit/'.$project_query->project_id), '<= Вернуться к настройке проекта')?>
</div>
<hr>