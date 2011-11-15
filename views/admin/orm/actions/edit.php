		$<?php echo $singular_name; ?> = Model_<?php echo $model_name; ?>::find($id);

		if (Input::method() == 'POST')
		{
<?php foreach ($fields as $field): ?>
			$<?php echo $singular_name; ?>-><?php echo $field['name']; ?> = Input::post('<?php echo $field['name']; ?>');
<?php endforeach; ?>

			if ($<?php echo $singular_name; ?>->save())
			{
				Session::set_flash('success', 'Updated <?php echo $singular_name; ?> #' . $id);

				Response::redirect('<?php echo $uri; ?>');
			}

			else
			{
				Session::set_flash('error', 'Could not update <?php echo $singular_name; ?> #' . $id);
			}
		}

		else
		{
			$this->template->set_global('<?php echo $singular_name; ?>', $<?php echo $singular_name; ?>, false);
		}

		$this->template->title = "<?php echo ucfirst($plural_name); ?>";
		$this->template->content = View::forge('<?php echo $view_path; ?>/edit');
