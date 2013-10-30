<?php defined('SYSPATH') OR die('No direct script access.');

class Response extends Kohana_Response {

	/**
	 * Sends the response status and all set headers.
	 *
	 * @param   boolean     $replace    replace existing headers
	 * @param   callback    $callback   function to handle header output
	 * @return  mixed
	 */
	public function send_headers($replace = FALSE, $callback = NULL)
	{
		// Record clockwork debugging information when not in production.
		if (Kohana::$environment !== Kohana::PRODUCTION)
		{
			$clockwork = new Clockwork\Clockwork();

			$data_source = new Clockwork\DataSource\KohanaDataSource();

			$data_source->set_response($this);

			// Write logs so they are included with the clockwork data
			Kohana::$log->write();

			$clockwork
				->addDataSource($data_source)
				->setStorage(new Clockwork\Storage\FileStorage(Kohana::$cache_dir))
				->resolveRequest()
				->storeRequest();

			$this
				->headers('X-Clockwork-Id', $clockwork->getRequest()->id)
				->headers('X-Clockwork-Version', Clockwork\Clockwork::VERSION)
				->headers('X-Clockwork-Path', '__clockwork/');
		}

		return parent::send_headers($replace, $callback);
	}

}
