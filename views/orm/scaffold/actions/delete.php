		if ($<?php echo $singular_name; ?> = Model_<?php echo $model_class; ?>::find($id))
		{
			$<?php echo $singular_name; ?>->delete();

			Session::set_flash('notice', 'Deleted <?php echo $singular_name; ?> #'.$id);
		}

		else
		{
			Session::set_flash('notice', 'Could not delete <?php echo $singular_name; ?> #'.$id);
		}

		Response::redirect('<?php echo $uri; ?>');
