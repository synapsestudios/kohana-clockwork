<?php defined('SYSPATH') OR die('No direct script access.');

class Profiler extends Kohana_Profiler {

	public static function get_token($token)
	{
		return Profiler::$_marks[$token];
	}
}