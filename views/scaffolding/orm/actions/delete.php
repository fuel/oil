<?php printf(
'		is_null($id) and Response::redirect("%1$s");

		if ($%2$s = Model_%3$s::find($id))
		{
			$%2$s->delete();
			Session::set_flash("success", "Deleted %4$s #$id");
		}
		else
		{
			Session::set_flash("error", "Could not delete %4$s #$id");
		}
		Response::redirect("%1$s");
', $uri, $singular_name, $model_name, \Inflector::humanize($singular_name));
