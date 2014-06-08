<?php

class Controller_Admin_Article extends Controller_Admin
{

	public function action_index()
	{
		$data['articles'] = Model_Article::find('all');
		$this->template->title = "Articles";
		$this->template->content = \View::forge('admin/article/index', $data);

	}

	public function action_view($id = null)
	{
		$data['article'] = Model_Article::find($id);

		$this->template->title = "Article";
		$this->template->content = \View::forge('admin/article/view', $data);

	}

	public function action_create()
	{
		if (\Input::method() == 'POST')
		{
			$val = Model_Article::validate('create');

			if ($val->run())
			{
				$article = Model_Article::forge(array(
					'title' => \Input::post('title'),
					'body' => \Input::post('body'),
				));

				if ($article and $article->save())
				{
					\Session::set_flash('success', e('Added article #'.$article->id.'.'));

					\Response::redirect('admin/article');
				}

				else
				{
					\Session::set_flash('error', e('Could not save article.'));
				}
			}
			else
			{
				\Session::set_flash('error', $val->error());
			}
		}

		$this->template->title = "Articles";
		$this->template->content = \View::forge('admin/article/create');

	}

	public function action_edit($id = null)
	{
		$article = Model_Article::find($id);
		$val = Model_Article::validate('edit');

		if ($val->run())
		{
			$article->title = \Input::post('title');
			$article->body = \Input::post('body');

			if ($article->save())
			{
				\Session::set_flash('success', e('Updated article #' . $id));

				\Response::redirect('admin/article');
			}

			else
			{
				\Session::set_flash('error', e('Could not update article #' . $id));
			}
		}

		else
		{
			if (\Input::method() == 'POST')
			{
				$article->title = $val->validated('title');
				$article->body = $val->validated('body');

				\Session::set_flash('error', $val->error());
			}

			$this->template->set_global('article', $article, false);
		}

		$this->template->title = "Articles";
		$this->template->content = \View::forge('admin/article/edit');

	}

	public function action_delete($id = null)
	{
		if ($article = Model_Article::find($id))
		{
			$article->delete();

			\Session::set_flash('success', e('Deleted article #'.$id));
		}

		else
		{
			\Session::set_flash('error', e('Could not delete article #'.$id));
		}

		\Response::redirect('admin/article');

	}

}
