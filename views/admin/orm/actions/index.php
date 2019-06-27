		$query = Model_<?php echo $model_name; ?>::query();

		$pagination = Pagination::forge('<?php echo $plural_name."_pagination" ?>', array(
			'total_items' => $query->count(),
			'uri_segment' => 'page',
		));

		$data['<?php echo $plural_name ?>'] = $query->rows_offset($pagination->offset)
			->rows_limit($pagination->per_page)
			->get();

		$this->template->set_global('pagination', $pagination, false);

		$this->template->title   = "<?php echo ucfirst($plural_name); ?>";
		$this->template->content = View::forge('<?php echo $view_path; ?>/index', $data);