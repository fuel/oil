		if (Input::method() == 'POST')
		{
			$<?php echo $singular_name; ?> = Model_<?php echo $model_name; ?>::forge(array(
<?php foreach ($fields as $field): ?>
				'<?php echo $field['name']; ?>' => Input::post('<?php echo $field['name']; ?>'),
<?php endforeach; ?>
			));

			if ($<?php echo $singular_name; ?> and $<?php echo $singular_name; ?>->save())
			{
				Session::set_flash('success', 'Added <?php echo $singular_name; ?> #'.$<?php echo $singular_name; ?>->id.'.');

				Response::redirect('<?php echo $uri; ?>');
			}

			else
			{
				Session::set_flash('error', 'Could not save <?php echo $singular_name; ?>.');
			}
		}

		$this->template->title = "<?php echo \Str::ucwords($plural_name); ?>";
		$this->template->content = View::forge('<?php echo $view_path ?>/create');
