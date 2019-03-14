<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 14:48
 */

namespace App\Observers;

use App\Order;
use App\Invoice;

class InvoiceObserver extends AbstractObserver
{
	/**
	 * Listner for the observer.
	 *
	 * @param AbstractTransaction $transaction
	 */
	public function update(AbstractTransaction $transaction)
	{
		$result = null;

		// Payment details received form observer
		$payment = $transaction->getData();

		// Instaiate Invoice class
		$invoice = new Invoice($payment);

		// Stop if not paid
		if ($payment['state'] !== 'PAID')
		{
			return false;
		}

		// Getting invoice id
		if ( ! $invoice_id = $invoice->createDebetInvoice())
		{
			return false;
		}

		$transaction->setTransactionResults('INVOICE CREATED WITH ID: ' . $invoice_id);
		$transaction->setTransactionResults('INVOICE ' . $invoice_id . ' HAS BEEN SENT');

		if ( ! $invoice->send($invoice_id, 'D'))
		{
			return false;
		}

		if ( ! $invoice->createCreditInvoice())
		{
			$transaction->setTransactionResults('CREDIT INVOICES COULD NOT BE SENT');

			return false;
		}

		$transaction->setTransactionResults('ALL CREDIT INVOIECS HAS BEEN SENT ALSO NOW');

		return true;
	}
}