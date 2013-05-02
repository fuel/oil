<?php printf(
'		is_null($id) and Response::redirect("%1$s");
		if ( ! $data["%3$s"] = Model_%4$s::find($id))
		{
			Session::set_flash("error", "Could not find %2$s #$id");
			Response::redirect("%1$s");
		}
		$this->template->title = "%2$s";
		$this->template->content = View::forge("%5$s/view", $data);
', $uri, \Inflector::humanize($singular_name), $singular_name, $model_name, $view_path);
