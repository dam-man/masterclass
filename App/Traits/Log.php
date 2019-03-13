<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 15:04
 */

namespace App\Traits;

trait Log
{
	/**
	 * Using the Trait for the log file, this makes all kind of stubs.
	 *
	 * @param $data
	 * @param $file
	 *
	 * @return bool
	 */
	public function saveStubDatatoTxtFile($data, $file)
	{
		// Faking email with data from the objects
		if ( ! file_put_contents(BASE_PATH . '/tmp/' . $file, print_r($data, true)))
		{
			return false;
		}

		return true;
	}
}