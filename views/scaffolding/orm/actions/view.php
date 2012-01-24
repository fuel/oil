		$data['<?php echo $singular_name ?>'] = Model_<?php echo $model_name ?>::find($id);

		is_null($id) and Response::redirect('<?php echo $controller_name ?>');

		$this->template->title = "<?php echo ucfirst($singular_name) ?>";
		$this->template->content = View::forge('<?php echo $view_path ?>/view', $data);
