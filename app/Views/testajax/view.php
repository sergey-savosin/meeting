<?php if (isset($validation)) {echo $validation->listErrors('my_list');} ?>

<?php echo form_open('testajax/index', 'role="form"') ; ?>

<div class="h3">Test Ajax</div>
<div class="alert alert-success" id="message" >
		</div>
<div class="form-group">
	<label>E-mail:</label>
	<input type="text" name="email" id="email" class="form-control" placeholder="E-mail">			
</div>

<div class="form-group">
	<button type="submit1" class="btn btn-success" id="btn">
		Press me
	</button>
</div>

<?php echo form_close(); ?>

	<script type="text/javascript" src="<?php echo base_url('assets/js/jquery-3.5.1.js')?>"></script>
	<script type="text/javascript">
		$(function() {
			$( "#btn" ).click(function(event)
			{
				event.preventDefault();
				var email = $("#email").val();

				$.ajax(
					{
						type:"post",
						url: "<?php echo base_url('testajax');?>",
						data: {email:email},
						success: function(response)
						{
							console.log(response);
							$("#message").html(response);
							//$("#cartmessage").show();
						},
						error: function()
						{
							alert("Invalide!");
						}
					}
				);
			});
		});
	</script>
