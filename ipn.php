<?php

// Middels deze weg komen de betalingen binnen vanuit de betaalservices.

/**
 * USED PATTERNS IN THIS APPLICATION:
 *
 * - Singleton in App/Factory namespace to connect the database.
 *
 *
 */

require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/App/Observers/Transaction.php';

use App\Transaction;
use App\Order;

// Received Payment from provider

$received_payment = [
	'transId' => '069E46384829D511B9A0E62BCE6C011A',
	'orderId' => 1552395313,
	'amount'  => 233.00,
	'state'   => 'PAID',
];

$transaction = new Transaction;
$order       = new Order;

$transaction->attach($order);
$transaction->updateData($received_payment);