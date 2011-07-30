		$data['<?php echo $plural ?>'] = <?php echo $model; ?>::find('all');
		$this->template->title = "<?php echo ucfirst($plural); ?>";
		$this->template->content = View::factory('<?php echo $controller_uri ?>/index', $data);
