		$data['<?php echo $plural ?>'] = <?php echo $model; ?>::find_all();
		$this->template->title = "<?php echo ucfirst($plural); ?>";
		$this->template->content = View::forge('<?php echo $controller_uri ?>/index', $data);
