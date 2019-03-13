<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 13:55
 */

namespace App\Api;

interface EmailInterface
{
	public function getFirstname();
	public function getLastname();
	public function getEmailAddress();
}