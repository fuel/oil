		$data['<?php echo $singular_name ?>'] = Model_<?php echo $model_class ?>::find_one_by_id($id);

		$this->template->title = "<?php echo ucfirst($singular_name) ?>";
		$this->template->content = View::forge('<?php echo $view_path ?>/view', $data);
