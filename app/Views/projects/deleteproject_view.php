<!-- Form - begin form section -->
<p class="h4">Удаление собрания "<?php echo $project_query->project_name ?>".</p>

<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open(
	'project/delete_project/'.$project_query->project_id, 'role="form"') ; ?>
	<div class="form-group">
		<button type="submit" class="btn btn-success">Подтверждаю удаление
		</button>
	</div>
<?php echo form_close(); ?>
<hr>


<hr>
<div class="h4">
	<?php echo anchor(base_url('/project/index'), '<== Вернуться к списку проектов')?>
</div>