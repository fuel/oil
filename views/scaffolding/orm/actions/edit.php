<?php printf(
'		is_null($id) and Response::redirect("%1$s");

		if ( ! $%2$s = Model_%4$s::find($id))
		{
			Session::set_flash("error", "Could not find %3$s #$id");
			Response::redirect("%1$s");
		}

		$val = Model_%4$s::validate("edit");

		if ($val->run())
		{
', $uri, $singular_name, \Inflector::humanize($singular_name), $model_name);
foreach ($fields as $field)
{
	printf('			$%s->%2$s = Input::post("%2$s");
', $singular_name, $field['name']);
}
printf(
'			if ($%1$s->save())
			{
				Session::set_flash("success", "Updated %2$s #$id");
				Response::redirect("%3$s");
			}
			else
			{
				Session::set_flash("error", "Could not update %2$s #$id");
			}
		}
		else
		{
			Session::set_flash("error", $val->error());
		}
		$this->template->set_global("%1$s", $%1$s, false);
		$this->template->title = "%2$s";
		$this->template->content = View::forge("%4$s/edit");
', $singular_name, \Inflector::humanize($singular_name), $uri, $view_path);
