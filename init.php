<?php

if (Kohana::$environment !== Kohana::PRODUCTION)
{
	// Clockwork logging endpoint
	Route::set('__clockwork', '__clockwork(/<id>)', ['id' => '[^/,;?]++'])
		->defaults(array(
			'controller' => 'Clockwork'
		));

	spl_autoload_register(
		function($class)
		{
			$directories = array(
				'vendor/clockwork',
			);

			foreach ($directories as $directory)
			{
				if (Kohana::auto_load($class, $directory))
					return;
			}
		}
	);

	// Attach the log writer
	Kohana::$log->attach(new Log_Clockwork);
}