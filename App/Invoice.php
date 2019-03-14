<?php
/**
 * Created by PhpStorm.
 * User: rdam
 * Date: 13-3-2019
 * Time: 15:18
 */

namespace App;

use App\Traits\Log;

class Invoice
{
	use Log;

	/**
	 * @var DBConnect
	 */
	private $db;
	/**
	 * @var array
	 */
	private $payment = [];
	/**
	 * @var object
	 */
	private $details;
	/**
	 * @var mixed
	 */
	private $orderId;

	/**
	 * Invoice constructor.
	 *
	 * @param array $payment
	 */
	public function __construct($payment = [])
	{
		$this->db      = Factory::getDatabaseConnection();
		$this->payment = $payment;
		$this->orderId = $payment['orderId'];
		$this->details = (new Order)->getFullOrderDetails($this->orderId);
	}

	/**
	 * Creating a DEBET invoice to the customer.
	 *
	 * @return bool|mixed
	 */
	public function createDebetInvoice()
	{
		$state = ($this->payment['state'] === 'PAID') ? 1 : 0;

		$invoice = [
			'client_id'   => $this->details->client->id,
			'order_id'    => $this->orderId,
			'payment_id'  => $this->payment['transId'],
			'netto_price' => $this->details->order->price,
			'bruto_price' => $this->details->order->price - $this->details->order->vat_amount,
			'vat_amount'  => $this->details->order->vat_amount,
			'state'       => $state,
		];

		return $this->save($invoice);
	}

	/**
	 * Creating invoices per Artist
	 * There can be more artists in an order, so we can create more than one invoice.
	 */
	public function createCreditInvoice()
	{
		// Getting fee information
		$results = $this->getArtistsFeesByOrderId($this->orderId);

		foreach ($results as $result)
		{
			$invoice = [
				'client_id'   => $result['client_id'],
				'order_id'    => $this->orderId,
				'payment_id'  => $this->payment['transId'],
				'netto_price' => -abs($result['netto_price']),
				'bruto_price' => -abs($result['fees']),
				'vat_amount'  => -abs($result['vat']),
				'state'       => 1,
			];

			if ( ! $id = $this->save($invoice))
			{
				return false;
			}

			if ( ! $this->send($id, 'C', $result['client_id']))
			{
				return false;
			}

			$payout = [
				'artist_id' => $result['artist_id'] ,
				'earnings' =>
			];
		}

		return true;
	}

	private function savePayoutForArtist()
	{
		if ( ! $id = $this->db->insert('invoices', $invoice))
		{
			return false;
		}

		return $id;
	}

	/**
	 * Saving the invoice to the database.
	 *
	 * @param $invoice
	 *
	 * @return bool|mixed
	 */
	private function save($invoice = [])
	{
		if ( ! $id = $this->db->insert('invoices', $invoice))
		{
			return false;
		}

		return $id;
	}

	/**
	 * Creating and sending an invoice
	 *
	 * @param        $invoice_id
	 * @param string $type
	 * @param int    $client_id
	 *
	 * @return bool
	 */
	public function send($invoice_id, $type = 'D', $client_id = 0)
	{
		// Define invoice Type
		$type = ($type === 'D') ? 'invoice.txt' : 'credit-invoice.txt';

		// Getting some details again
		$details = $this->details;

		if ($client_id !== 0)
		{
			$details->client = (new Client)->getClientById($client_id);
		}

		// Getting invoice ID
		$details->invoice_id = $invoice_id;

		// Simulate PDF generation :D
		if ( ! $this->saveStubDatatoTxtFile($details, $type))
		{
			return false;
		}

		/// Mailing PDF file to the customer with above generated PDF

		return true;
	}

	/**
	 * Getting artist feest per order
	 *
	 * @param $orderId
	 *
	 * @return array
	 */
	public function getArtistsFeesByOrderId($orderId)
	{
		$data = [];

		$this->db->select([
			'artist.id as artist_id', 'product.id', 'product.name', 'artist.client_id',
			'(SUM(product.price) / 100) * artist.fees_per_order_pct as fees', 'artist.fees_per_order_pct as artist_fees', 'country.vat_percentage',
		])
		         ->from('ordered_products as ordered')
		         ->join('products as product', 'product.id = ordered.product_id')
		         ->join('artists as artist', 'product.artist_id = artist.id')
		         ->join('clients as client', 'artist.client_id = client.id')
		         ->join('countries as country', 'client.country = country.id')
		         ->where('order_id', $orderId)
		         ->group('artist.client_id');

		$results = $this->db->loadResultList();

		foreach ($results as &$result)
		{
			// Add some calculations to the array
			$result['vat']         = ($result['fees'] / 100) * $result['vat_percentage'];
			$result['netto_price'] = ($result['fees'] - $result['vat']);

			// Assign the product info for the invoice lines
			$data[] = $result;
		}

		return $data;
	}
}