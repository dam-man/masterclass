<?php

namespace App;

class User
{
	private $userName;
	private $firstName = 'Jesse';
	private $password  = 'geheim';

	public function __construct($userName)
	{
		$this->userName = $userName;
	}

	public function verifyPassword($password)
	{
		if ($this->password === $password)
		{
			return true;
		}

		return false;
	}

	public function getUserName()
	{
		return $this->userName;
	}

	public function getFirstName()
	{
		return $this->firstName;
	}
}