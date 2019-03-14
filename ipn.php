<?php

// Middels deze weg komen de betalingen binnen vanuit de betaalservices.

define('BASE_PATH', __DIR__);

/**
 * USED PATTERNS IN THIS APPLICATION:
 *
 * - Singleton in App/Factory namespace to connect the database.
 * - Observers voor verwerking
 * - Adapter voor email verzending
 * - Abstract Class voor de Observer
 * - Interface voor de client Class so we're sure that all information is in it :)
 * - Factory in Factory.php voor bijvoorbeeld gebruik Singleton
 * - Trait in de observer to save all kind of data in a txt file.
 *
 */

require_once __DIR__ . '/autoloader.php';
require_once __DIR__ . '/App/Observers/Transaction.php';

use App\Transaction;
use App\Observers\ConfirmationObserver;
use App\Observers\InvoiceObserver;
use App\Observers\TransportObserver;
use App\Observers\CompleteOrderObserver;

// Received Payment from provider -- Step 4:
$received_payment = [
	'transId' => '069E46384829D511B9A0E62BCE6C011A',
	'orderId' => 1552395313,
	'amount'  => 233.00,
	'state'   => 'PAID',
];

$transaction  = new Transaction($received_payment['orderId']);
$confirmation = new ConfirmationObserver;
$invoicing    = new InvoiceObserver;
$transport    = new TransportObserver;
$complete     = new CompleteOrderObserver;

// Attach observers

// Step 5
$transaction->attach($confirmation);

// Step 6 & 7
$transaction->attach($invoicing);

// Step 8 + 9
$transaction->attach($transport);

// Step 10 + 11
$transaction->attach($complete);

// Perform the required action in Observers
$transaction->updateData($received_payment);

// Detach all observers again
$transaction->detach($confirmation);
$transaction->detach($invoicing);
$transaction->detach($transport);
$transaction->detach($complete);

echo '<pre>';

echo '<h3>Results From the Transaction Observer</h3>';

var_dump('Order ID: ' . $transaction->getOrderId());
var_dump($transaction->getTransactionResults());
var_dump($transaction->getClientData());

echo '</pre>';