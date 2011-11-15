<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<?php echo Asset::css('bootstrap.css'); ?>
	<style>
		body { margin: 40px; }
	</style>
	<?php echo Asset::js(array(
		'http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js',
		'bootstrap.js'
	)); ?>
	<script>
		$(function(){ $('.topbar').dropdown(); });
	</script>
</head>
<body>
	
	<div class="topbar">
	    <div class="fill">
	        <div class="container">
	            <h3><a href="#">My Site</a></h3>
	            <ul>
	                <li class="<?php echo Uri::segment(2) == '' ? 'active' : '' ?>">
						<?php echo Html::anchor('admin', 'Dashboard') ?>
					</li>
	                
					<?php foreach (glob(APPPATH.'') as $controller): ?>
						
						<?php
						$section_segment = basename($controller, '.php');
						$section_title = Inflector::humanize($section_segment);
						?>
						
	                <li class="<?php echo Uri::segment(2) ==  ? 'active' : '' ?>">
						<?php echo Html::anchor('admin/'.$section_segment, $section_title) ?>
					</li>
					<?php endforeach; ?>
	          </ul>
	          <form action="">
	            <input type="text" placeholder="Search" />
	          </form>
	          <ul class="nav secondary-nav">
	            <li class="menu">
	                <a href="#" class="menu">Lorem ipsum</a>
	                <ul class="menu-dropdown">
	                    <li><a href="#">Dolor sit</a></li>
	                    <li><a href="#">Amet Consequeter</a></li>
	                    <li class="divider"></li>
	                    <li><a href="#">Enough with the Latin</a></li>
	                </ul>
	            </li>
	          </ul>
	        </div>
	    </div>
	</div>
	
	<div class="container">
		<div class="row">
			<div class="span16">
				<h1><?php echo $title; ?></h1>
				<hr>
<?php if (Session::get_flash('success')): ?>
				<div class="alert-message success">
					<p>
					<?php echo implode('</p><p>', (array) Session::get_flash('success')); ?>
					</p>
				</div>
<?php endif; ?>
<?php if (Session::get_flash('error')): ?>
				<div class="alert-message error">
					<p>
					<?php echo implode('</p><p>', (array) Session::get_flash('error')); ?>
					</p>
				</div>
<?php endif; ?>
			</div>
			<div class="span16">
<?php echo $content; ?>
			</div>
		</div>
		<footer>
			<p class="pull-right">Page rendered in {exec_time}s using {mem_usage}mb of memory.</p>
			<p>
				<a href="http://fuelphp.com">FuelPHP</a> is released under the MIT license.<br>
				<small>Version: <?php echo e(Fuel::VERSION); ?></small>
			</p>
		</footer>
	</div>
</body>
</html>
