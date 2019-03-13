<?php

use App\Client;
use PHPUnit\Framework\TestCase;

class ClientTests extends TestCase
{
	private $client;

	private $client_id;

	/**
	 * ConfirmationCustomer constructor.
	 *
	 * @param null   $name
	 * @param array  $data
	 * @param string $dataName
	 */
	public function __construct()
	{
		parent::__construct();

		$this->client     = new Client;
		$this->client_id = 1;
	}

	/**
	 * Checks if the client_id is exsisting in our order database.
	 */
	public function testIfclientIsInDatabase()
	{
		$client = $this->client->getClientById($this->client_id);

		$this->assertIsObject($client);
	}
}