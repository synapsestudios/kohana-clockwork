<?php
namespace Clockwork\DataSource;

use Kohana_Response;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;

/**
 * Data source providing data obtainable the Kohana framework
 */
class KohanaDataSource extends DataSource
{

	protected $_kohana_response;
	protected $_kohana_request;

	static $logs = [];

	public function set_response(Kohana_Response $response)
	{
		$this->_kohana_response = $response;
	}

	/**
	 * Add request time, method, URI, headers, get and post data, session data, cookies, response status and time to the request
	 */
	public function resolve(Request $request)
	{
		$this->_kohana_request = \Request::$initial;

		$request->time            = $this->getRequestTime();
		$request->method          = $this->getRequestMethod();
		$request->uri             = $this->getRequestUri();
		$request->headers         = $this->getRequestHeaders();
		$request->getData         = $this->getGetData();
		$request->postData        = $this->getPostData();
		$request->sessionData     = $this->getSessionData();
		$request->cookies         = $this->getCookies();
		$request->responseStatus  = $this->getResponseStatus();
		$request->responseTime    = $this->getResponseTime();
		$request->databaseQueries = $this->getDatabaseQueries();
		$request->timelineData    = $this->getTimeline();
		$request->controller      = $this->getController();
		$request->log             = $this->getLogs();

		return $request;
	}

	/**
	 * Return cookies (replace unserializable items, attempt to remove passwords)
	 */
	protected function getCookies()
	{
		return [$this->removePasswords(
					$this->replaceUnserializable($this->_kohana_request->cookie())
				)];
	}

	/**
	 * Return GET data (replace unserializable items, attempt to remove passwords)
	 */
	protected function getGetData()
	{
		return $this->removePasswords(
			$this->replaceUnserializable($this->_kohana_request->query())
		);
	}

	/**
	 * Return POST data (replace unserializable items, attempt to remove passwords)
	 */
	protected function getPostData()
	{
		return $this->removePasswords(
			$this->replaceUnserializable($this->_kohana_request->post())
		);
	}

	/**
	 * Return headers
	 */
	protected function getRequestHeaders()
	{
		$headers = array();

		foreach ($_SERVER as $key => $value)
		{
			if (substr($key, 0, 5) !== 'HTTP_')
				continue;

			$header = substr($key, 5);
			$header = str_replace('_', ' ', $header);
			$header = ucwords(strtolower($header));
			$header = str_replace(' ', '-', $header);

			if ( ! isset($headers[$header]))
			{
				$headers[$header] = array($value);
			}
			else
			{
				$headers[$header][] = $value;
			}
		}

		ksort($headers);

		return $headers;
	}

	/**
	 * Retrun request method
	 */
	protected function getRequestMethod()
	{
		return $this->_kohana_request->method();
	}

	/**
	 * Return response time in most precise form
	 */
	protected function getRequestTime()
	{
		return KOHANA_START_TIME;
	}

	/**
	 * Return request URI
	 */
	protected function getRequestUri()
	{
		if (isset($_SERVER['REQUEST_URI'])) {
			return $_SERVER['REQUEST_URI'];
		}
	}

	/**
	 * Return response status code (if available)
	 */
	protected function getResponseStatus()
	{
		return $this->_kohana_response->status();
	}

	/**
	 * Return response time (current time, assuming most application scripts have already run at this point)
	 */
	protected function getResponseTime()
	{
		return microtime(true);
	}

	/**
	 * Return session data (replace unserializable items, attemp to remove passwords)
	 */
	protected function getSessionData()
	{
		if (isset($_SESSION)) {
			return $this->removePasswords(
				$this->replaceUnserializable($_SESSION)
			);
		}
	}

	/**
	 * Returns an array of runnable queries and their durations from a database connection
	 */
	protected function getDatabaseQueries()
	{
		$data = [];

		foreach (\Profiler::groups() as $group_name => $items)
		{
			if (preg_match('/^database/s', $group_name))
			{
				foreach ($items as $sql => $benchmarks)
				{
					foreach ($benchmarks as $benchmark)
					{
						$data[] = [
							'query'    => $sql,
							'duration' => \Profiler::total($benchmark)[0] * 1000,
						];
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Return the timeline data structure (creates default Timeline instance if none set)
	 */
	public function getTimeline()
	{
		$data = [];

		foreach (\Profiler::groups() as $group_name => $items)
		{
			// Ignore database and find_file benchmarks
			if (preg_match('/^(?!database|find_file).+/', $group_name))
			{
				foreach ($items as $name => $benchmarks)
				{
					foreach ($benchmarks as $benchmark)
					{
						$token = \Profiler::get_token($benchmark);

						$data[] = [
							'description' => $name,
							'start'       => $token['start_time'],
							'end'         => $token['stop_time'],
							'duration'    => ($token['stop_time'] - $token['start_time']) * 1000,
						];
					}
				}
			}
		}

		return $data;
	}

	public function getController()
	{
		$data = [
			$this->_kohana_request->directory(),
			$this->_kohana_request->controller(),
			$this->_kohana_request->method()
		];

		$data = array_filter($data);

		return implode('::', $data);
	}

	/**
	 * Return array of application routes
	 */
	protected function getLogs()
	{
		return self::$logs;
	}
}
