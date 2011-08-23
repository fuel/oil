		if (Input::method() == 'POST')
		{
			$<?php echo $singular; ?> = <?php echo $model; ?>::forge(array(
<?php foreach ($fields as $field): ?>
				'<?php echo $field['name']; ?>' => Input::post('<?php echo $field['name']; ?>'),
<?php endforeach; ?>
			));

			if ($<?php echo $singular; ?> and $<?php echo $singular; ?>->save())
			{
				Session::set_flash('notice', 'Added <?php echo $singular; ?> #' . $<?php echo $singular; ?>->id . '.');

				Response::redirect('<?php echo $controller_uri; ?>');
			}

			else
			{
				Session::set_flash('notice', 'Could not save <?php echo $singular; ?>.');
			}
		}

		$this->template->title = "<?php echo \Str::ucwords($plural); ?>";
		$this->template->content = View::forge('<?php echo $controller_uri ?>/create');
