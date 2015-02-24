<?php echo '<?php echo Form::open(array("class"=>"form-horizontal")); ?>' ?>


	<fieldset>
<?php foreach ($fields as $field): ?>
		<div class="form-group">
			<?php echo "<?php echo Form::label('". \Inflector::humanize($field['name']) ."', '{$field['name']}', array('class'=>'col-sm-2 control-label')); ?>\n"; ?>
<?php switch($field['type']):

				case 'text':
                                        echo "\t\t<div class='col-sm-10'>\n";
					echo "\t\t\t\t<?php echo Form::textarea('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular_name}) ? \${$singular_name}->{$field['name']} : ''), array('class' => 'form-control', 'rows' => 8, 'placeholder'=>'".\Inflector::humanize($field['name'])."')); ?>\n";
                                        echo "\t\t</div>\n";
				break;

				default:
                                        echo "\t\t<div class='col-sm-4'>\n";
					echo "\t\t\t\t<?php echo Form::input('{$field['name']}', Input::post('{$field['name']}', isset(\${$singular_name}) ? \${$singular_name}->{$field['name']} : ''), array('class' => 'form-control', 'placeholder'=>'".\Inflector::humanize($field['name'])."')); ?>\n";
                                        echo "\t\t</div>\n";

endswitch; ?>
                        

		</div>
<?php endforeach; ?>
		<div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                                <?php echo '<?php'; ?> echo Form::submit('submit', 'Save', array('class' => 'btn btn-primary')); <?php echo '?>'; ?>
                        </div>
		</div>
	</fieldset>
<?php if ($csrf): ?>
	<?php echo '<?php'; ?> echo Form::hidden(Config::get('security.csrf_token_key'), Security::fetch_token()); <?php echo '?>'; ?>
<?php endif; ?>
<?php echo '<?php'; ?> echo Form::close(); <?php echo '?>'; ?>
