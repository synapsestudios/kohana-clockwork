<?php defined('SYSPATH') OR die('No direct script access.');

class Log_Clockwork extends Log_Writer {

	public function write( array $messages)
	{
		foreach ($messages as $message)
		{
			Clockwork\DataSource\KohanaDataSource::$logs[] = [
				'time'    => $message['time'],
				'level'   => $this->_log_levels[$message['level']],
				'message' => $this->format_message($message, 'body in file:line'),
			];
		}
	}
}