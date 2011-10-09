		$data['<?php echo $singular ?>'] = <?php echo $model ?>::find_one_by_id($id);

		$this->template->title = "<?php echo ucfirst($singular) ?>";
		$this->template->content = View::forge('<?php echo $controller_uri ?>/view', $data);
