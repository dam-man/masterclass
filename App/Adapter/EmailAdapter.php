<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 13:50
 */

namespace App\Adapter;

use App\Client;

class EmailAdapter
{
	private $client;

	/**
	 * EmailAdapter constructor.
	 *
	 * @param Client $user
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Getting full name of the person
	 *
	 * @return string
	 */
	public function getUserFullname()
	{
		return ($this->client->getFirstname() . ' ' . $this->client->getLastname());
	}


}