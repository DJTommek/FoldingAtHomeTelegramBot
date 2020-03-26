<?php

class Factory
{
	private static $objects = array();

	static function get_database(): Database {
		if (!isset(self::$objects['database'])) {
			self::$objects['database'] = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASS);
		}
		return self::$objects['database'];
	}
}