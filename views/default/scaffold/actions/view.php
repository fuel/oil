		$data['<?php echo $singular ?>'] = <?php echo $model ?>::find($id);
		
		$this->template->title = "<?php echo ucfirst($singular) ?>";
		$this->template->content = View::factory('<?php echo $controller_uri ?>/view', $data);
