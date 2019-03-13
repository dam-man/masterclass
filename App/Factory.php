<?php

namespace App;

class Factory
{
	public static $database;

	/**
	 * Checking if there is a database connection already.
	 * If not, instantiate a new connection.
	 *
	 * @return mixed
	 */
	public static function getDatabaseConnection()
	{
		if ( ! isset(self::$database))
		{
			self::$database = DBConnect::getInstance();
		}

		return self::$database;
	}

	/**
	 * Checks the input from a user.
	 *
	 * @param        $name
	 * @param null   $default
	 * @param null   $type
	 *
	 * @return bool|float|int|mixed|null
	 */
	static public function getInput($name = null, $default = null, $type = null)
	{
		$variable = null;

		// Checks if the GET is set for this request.
		if (isset($_GET[$name]))
		{
			$variable = $_GET[$name];
		}

		// Checks if the GET is set for this request.
		if (isset($_POST) && ! empty($_POST[$name]) && ! isset($_GET[$name]))
		{
			$variable = $_POST[$name];
		}

		if ($variable)
		{
			return static::filterInput($variable, $default, $type);
		}

		return $default;
	}

	/**
	 * @param null $var
	 * @param null $default
	 * @param null $type
	 *
	 * @return array|bool|float|int|mixed|null|string|string[]
	 */
	static private function filterInput($var = null, $default = null, $type = null)
	{
		$filteredVar = null;

		if (isset($var))
		{
			switch ($type)
			{
				case 'int':
					$filteredVar = (int) $var;
					break;
				case 'bigint':
					$filteredVar = (float) $var;
					break;
				case 'bool':
					$filteredVar = (bool) $var;
					break;
				case 'word':
					$filteredVar = preg_replace('/(*UTF8)[^a-zA-Z_\p{L}$]/i', '', $var);
					break;
				case 'words':
					$filteredVar = preg_replace('/[^0-9a-zA-Z \|\-\_\.]*$/', '', $var);
					break;
				case 'alnum':
					$filteredVar = preg_replace('/[^A-Z0-9]/i', '', $var);
					break;
				case 'uuid':
					$filteredVar = preg_replace('/(*UTF8)[^0-9]/i', '', $var);
					break;
				case 'url':
					$filteredVar = preg_replace('/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i', '', $var);
					break;
				case 'cmd':
					$result      = preg_replace('/(*UTF8)[^a-zA-Z0-9-_\s\p{L}$]/i', '', $var);
					$filteredVar = ltrim($result, '.');
					break;
				case 'username':
					$result      = preg_replace('/(*UTF8)[^a-zA-Z0-9-_.\s\p{L}$]/i', '', $var);
					$filteredVar = ltrim($result, '.');
					break;
				case 'raw':
					$filteredVar = $var;
					break;
				case 'array':
					$filteredVar = (array) $var;
					break;
				case 'datetime':
					$filteredVar = preg_replace('/(*UTF8)[^0-9 \-\:]/i', '', $var);
					break;
				case 'date':
					$filteredVar = preg_replace('~^\d{2}/\d{2}/\d{4}$~', '', $var);
					break;
				case 'email':
					$filteredVar = preg_replace('/(*UTF8)[^a-zA-Z0-9-_\s\@\.\p{L}$]/i', '', $var);
					break;
				default:
					$filteredVar = strtolower(preg_replace("/[^a-z]+/i", "-", $var));
			}
		}

		if ( ! $filteredVar)
		{
			return $default;
		}

		return $filteredVar;
	}
}