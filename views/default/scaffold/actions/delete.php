		if ($<?php echo $singular; ?> = <?php echo $model; ?>::find($id))
		{
			$<?php echo $singular; ?>->delete();
			
			Session::set_flash('notice', 'Deleted <?php echo $singular; ?> #' . $id);
		}

		else
		{
			Session::set_flash('notice', 'Could not delete <?php echo $singular; ?> #' . $id);
		}

		Response::redirect('<?php echo $plural; ?>');