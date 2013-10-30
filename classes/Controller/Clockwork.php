<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Clockwork extends Controller {

	public function action_index()
	{
		// Clockwork debugger/profiler
		$clockwork = new Clockwork\Clockwork();

		$clockwork->setStorage(new Clockwork\Storage\FileStorage(Kohana::$cache_dir));

		$data = $clockwork->getStorage()->retrieveAsJson($this->request->param('id'));

		if ( ! $data)
		{
			$this->response
				->status(404)
				->body('Data not found');
		}
		else
		{
			$this->response->body($data);
		}
	}
}