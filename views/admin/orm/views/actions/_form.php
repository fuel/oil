<?php echo '<?php echo Form::open(); ?>' ?>


	<fieldset>
<?php foreach ($fields as $field): ?>
		<div class="form-group">
			<?php echo "<?php echo Form::label('". \Inflector::humanize($field['name']) ."', '{$field['name']}', array('class'=>'control-label')); ?>\n"; ?>

<?php switch($field['type']):

				case 'text':
					echo "\t\t\t\t<?php echo Form::textarea('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular_name}) ? \${$singular_name}->{$field['name']} : ''), array('class' => 'form-control', 'rows' => 8, 'placeholder'=>'".\Inflector::humanize($field['name'])."')); ?>\n";
				break;

				default:
					echo "\t\t\t\t<?php echo Form::input('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular_name}) ? \${$singular_name}->{$field['name']} : ''), array('class' => 'form-control', 'placeholder'=>'".\Inflector::humanize($field['name'])."')); ?>\n";

endswitch; ?>

		</div>
<?php endforeach; ?>
		<div class="form-group">
			<?php echo '<?php'; ?> echo Form::submit('submit', 'Save', array('class' => 'btn btn-primary')); <?php echo '?>'; ?>

			<div class="pull-right">
				<?php echo '<?php'; ?> if (Uri::segment(3) === 'edit'): <?php echo '?>'; ?>
					<div class="btn-group">
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/view/'.$<?php echo $singular_name; ?>->id, 'View', array('class' => 'btn btn-info')); <?php echo '?>'; ?>
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>', 'Back', array('class' => 'btn btn-default')); <?php echo '?>'; ?>
					</div>
				<?php echo '<?php'; ?> else: <?php echo '?>'; ?>
					<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>', 'Back', array('class' => 'btn btn-link')); <?php echo '?>'; ?>
				<?php echo '<?php'; ?> endif <?php echo '?>'; ?>
			</div>
		</div>
	</fieldset>
<?php if ($csrf): ?>
	<?php echo '<?php'; ?> echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); <?php echo '?>'; ?>
<?php endif; ?>
<?php echo '<?php'; ?> echo Form::close(); <?php echo '?>'; ?>
