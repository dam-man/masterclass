<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 12:32
 */

namespace App;

use App\Api\EmailInterface;

class Client implements EmailInterface
{
	/**
	 * @var DBConnect
	 */
	private $db;
	/**
	 * @var object
	 */
	private $client;

	/**
	 * Client constructor.
	 *
	 * @param null $client_id
	 */
	public function __construct($client_id = null)
	{
		$this->db = Factory::getDatabaseConnection();

		if ($client_id)
		{
			$this->client = $this->getClientById($client_id);
		}
	}

	/**
	 * Getting cusomter information from the DB
	 *
	 * @return object
	 *
	 * @param $client_id
	 */
	public function getClientById($client_id = null)
	{
		$result = new \stdClass;

		if (empty($client_id))
		{
			return ! empty($this->client) ? $this->client : $result;
		}

		$this->db->select(['client.*', 'country.name', 'state.name'])
		         ->from('clients as client')
		         ->join('countries as country', 'client.country = country.id')
		         ->join('states as state', 'client.region = state.id')
		         ->where('client.id', $client_id);

		return $this->db->loadResult();
	}

	/**
	 * Return the firstname
	 *
	 * @return null
	 */
	public function getFirstname()
	{
		return ! empty($this->client->firstname) ? $this->client->firstname : null;
	}

	/**
	 * return the last name
	 *
	 * @return null
	 */
	public function getLastname()
	{
		return ! empty($this->client->lastname) ? $this->client->lastname : null;
	}

	/**
	 * Return the email address
	 *
	 * @return null
	 */
	public function getEmailAddress()
	{
		return ! empty($this->client->email) ? $this->client->email : null;
	}
}