<?php printf(
'		if (Input::method() == "POST")
		{
			$val = Model_%1$s::validate("create");
			if ($val->run())
			{
				$%2$s = Model_%1$s::forge(array(
', $model_name, $singular_name);
foreach ($fields as $field)
{
	printf('					"%1$s" => Input::post("%1$s"),
', $field['name']);
}
printf(
'				));

				if ($%1$s and $%1$s->save())
				{
					Session::set_flash("success", "Added %2$s #{$%1$s->id}");
					Response::redirect("%3$s");
				}
				else
				{
					Session::set_flash("error", "Could not save %2$s");
				}
			}
			else
			{
				Session::set_flash("error", $val->error());
			}
		}
		$this->template->title = "%2$s";
		$this->template->content = View::forge("%4$s/create");
', $singular_name, \Inflector::humanize($singular_name), $uri, $view_path);
