<?php

use App\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
	private $user;

	public function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->user = new User('jhendriks');
	}

	public function testIfPasswordIsValid()
	{
		$verified = $this->user->verifyPassword('geheim');

		$this->assertTrue($verified);
	}

	public function testGetGivenUserName()
	{
		$firstName = $this->user->getUserName();

		$this->assertEquals('jhendriks', $firstName);
	}

	public function testGetFirstName()
	{
		$firstName = $this->user->getFirstName();

		$this->assertEquals('Jesse', $firstName);
	}
}