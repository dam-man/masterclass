<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 12:32
 */

namespace App;

class Client
{
	/**
	 * @var DBConnect
	 */
	private $db;

	public function __construct()
	{
		$this->db = Factory::getDatabaseConnection();
	}

	/**
	 * Getting cusomter information from the DB
	 *
	 * @return object
	 *
	 * @param $client_id
	 */
	public function getClientById($client_id)
	{
		$this->db->select(['client.*', 'country.name', 'state.name'])
		         ->from('clients as client')
		         ->join('countries as country', 'client.country = country.id')
		         ->join('states as state', 'client.region = state.id')
		         ->where('client.id', $client_id);

		return $this->db->loadResult();
	}
}