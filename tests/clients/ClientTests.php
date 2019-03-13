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

		$this->client = new Client(1);
	}

	/**
	 * Checks if the client_id is exsisting in our order database.
	 */
	public function testIfclientIsInDatabase()
	{
		$client = $this->client->getClientById(1);

		$this->assertIsObject($client);
	}

	public function testIfWeCanGetTheFirstName()
	{
		$firstname = $this->client->getFirstname();

		$this->assertEquals($firstname, 'Robert');
	}

	public function testIfWeCanGetTheLastName()
	{
		$lastname = $this->client->getLastname();

		$this->assertEquals($lastname, 'Dam');
	}

	public function testIfWeCanGetTheMailAddress()
	{
		$email = $this->client->getEmailAddress();

		$this->assertEquals($email, 'info@rd-media.org');
	}
}