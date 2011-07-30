<?php echo '<?php echo Form::open(); ?>' ?>

<?php foreach ($fields as $field): ?>
	<p>
		<?php
			echo "<?php echo Form::label('". \Inflector::humanize($field['name']) ."', '{$field['name']}'); ?>\n";

			switch($field['type']):

				case 'text':
					echo "<?php echo Form::textarea('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular}) ? \${$singular}->{$field['name']} : ''), array('cols' => 60, 'rows' => 8)); ?>";
				break;

				default:
					echo "<?php echo Form::input('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular}) ? \${$singular}->{$field['name']} : '')); ?>";

			endswitch;
		?>

	</p>
<?php endforeach; ?>

	<div class="actions">
		<?php echo '<?php echo Form::submit(); ?>'; ?>
	</div>

<?php echo '<?php echo Form::close(); ?>' ?>
