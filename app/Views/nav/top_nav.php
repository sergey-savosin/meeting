<!-- Fixed navbar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container-fluid">
<div class="navbar-header">
	<button type="button" 
		class="navbar-toggle" data-toggle="collapse" 
		aria-expanded="false" data-target="#myNavbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>
	<a class="navbar-brand" href="#"><?php
		echo lang('app.system_system_name'); ?></a>
</div>
<div class="collapse navbar-collapse" id="myNavbar">
	<ul class="nav navbar-nav">
	<!--li class="active"><?php echo anchor('create', 'Create'); ?></li-->
	<li <?php if($uri->getSegment(1) == '' || $uri->getSegment(1) == 'Documents') {echo 'class="active"';}; ?> >
		<?php echo anchor(base_url('/'), lang('app.top_nav_view_documents')); ?>
	</li>
	<li <?php if($uri->getSegment(1) == 'votes') {echo 'class="active"';}; ?>>
		<?php echo anchor(base_url('votes'), lang('app.top_nav_view_votes')); ?>
	</li>
	<li <?php if($uri->getSegment(1) == 'additionalagendavotes') {echo 'class="active"';}; ?>>
		<?php echo anchor(base_url('additionalagendavotes'), lang('app.top_nav_view_additionalagendavotes')); ?>
	</li>
	</ul>
	<ul class="nav navbar-nav navbar-right">
		<li><?php if (session('user_login_code') == FALSE) {
			echo anchor('user/login', lang('app.top_nav_login'));
			} ?>
		</li>
		<li><?php $login_code = session('user_login_code');
			if ($login_code) { ?><p class="navbar-text">
				<?php echo(lang('app.top_nav_login_code').' '.$login_code); ?>
				</p>
			<?php } ?>
		</li>
		<li><?php $login_code = session('user_login_code');
			if ($login_code) {
				echo anchor('user/logout', lang('app.top_nav_logout'));
			} ?>
		</li>
	</ul>
	</div><!--/.navbar-collapse-->
</div>
</div>

<div class="container theme-showcase" role="main" style="margin-top:80px">
