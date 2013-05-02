<?php printf(
'		$data["%s"] = Model_%s::find("all");
		$this->template->title = "%s";
		$this->template->content = View::forge("%s/index", $data);
', $plural_name, $model_name, \Inflector::humanize($plural_name), $view_path);
