<?php

namespace App\Observers;

abstract class AbstractTransaction
{
	// Attach Observer to the Observer Class.
	abstract public function attach(AbstractObserver $observer_in);

	// Detach Observer to the Observer Class.
	abstract public function detach(AbstractObserver $observer_in);

	// Notifier
	abstract protected function notify();

	// Get data from the main obswrver.
	abstract public function getData();

	// Setting results from the observers
	abstract public function setTransactionResults($result, $type = 'log');

	// Getting results from the observers.
	abstract public function getTransactionResults();

	// Setting client data to the transaction
	abstract public function setClientData($data);

	// Getting the cleint data from the transaction
	abstract public function getClientData();

	// Getting the barcode from the transaction
	abstract public function getPostNLBarcode();

	// Getting the created invoice ID from the transaction
	abstract public function getInvoiceId();

	// Making the orderId available
	abstract public function getOrderId();
}