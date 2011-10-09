		$<?php echo $singular; ?> = <?php echo $model; ?>::find_one_by_id($id);

		if (Input::method() == 'POST')
		{
<?php foreach ($fields as $field): ?>
			$<?php echo $singular; ?>-><?php echo $field['name']; ?> = Input::post('<?php echo $field['name']; ?>');
<?php endforeach; ?>

			if ($<?php echo $singular; ?>->save())
			{
				Session::set_flash('notice', 'Updated <?php echo $singular; ?> #' . $id);

				Response::redirect('<?php echo $controller_uri; ?>');
			}

			else
			{
				Session::set_flash('notice', 'Could not update <?php echo $singular; ?> #' . $id);
			}
		}

		else
		{
			$this->template->set_global('<?php echo $singular; ?>', $<?php echo $singular; ?>, false);
		}

		$this->template->title = "<?php echo ucfirst($plural); ?>";
		$this->template->content = View::forge('<?php echo $controller_uri; ?>/edit');
