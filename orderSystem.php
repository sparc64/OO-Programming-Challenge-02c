<?php

interface OrderDispatchInterface
{
	/**
	 * Method to start new batch that would form 'dispatch period' (normally at the start of a working day)
	 */
	public function startNewBatch() :void;

	/**
	 * Method to end current batch / 'dispatch period' (normally at the end of a working day)
	 */
	public function endCurrentBatch() :void;

	/**
	 * Method to add single shipment (consignment) to current batch
	 *
	 * @param CourierInterface $courier - specific courier supplied as dependency injection for flexibility and easy testing.
	 * @param array $consignmentData - information about consignment provided by user
	 */
	public function addConsignment(CourierInterface $courier, array $consignmentData) :void;
}


abstract class OrderDispatchSystem implements OrderDispatchInterface
{
	protected $consignmentStorage;
	protected $batchID;

	public function __construct(Storage $storage)
	{
		$this->consignmentStorage = $storage;
	}

	public function startNewBatch() :void
	{
		$this->batchID = $this->generateBatchID();
	}

	public function endCurrentBatch() :void
	{
		$this->sendAllConsignments();

		// Close current batch
		$this->batchID = null;
	}

	public function addConsignment(CourierInterface $courier, array $consignmentData) :void
	{
		// TODO: remove hardcoded field names
		$data = array(
			'consignmentID' => $courier::generateConsignmentID(),
			'batchID' => $this->batchID,
			'courierName' => $courier::getCourierName()
		);

		$data = array_merge($data, $consignmentData);

		$this->consignmentStorage->save($data);
	}

	protected function generateBatchID() :int
	{
		return uniqid('', true);
	}

	/**
	 * Send consignment numbers to couriers using courier-specific methods
	 */
	protected function sendAllConsignments() :void
	{
		$batchConsignments = $this->consignmentStorage->load(array('batchID' => $this->batchID));

		$consignmentsGroupedByCourier = self::groupConsignmentsByCourier($batchConsignments);

		foreach ($consignmentsGroupedByCourier as $courierName => $consignmentList)
		{
			$courier = new $courierName;

			$courier->sendConsignments($consignmentList);
		}
	}

	/**
	 * Method for grouping list of consignments by courier name.
	 *
	 * @param array $consignmentsList - raw list of consignments
	 *
	 * @return array - consignments grouped by courier ('key' = 'courier name'; 'value' = list of consignments)
	 */
	protected static function groupConsignmentsByCourier(array $consignmentsList) :array
	{
		$groupedConsignments = array();

		foreach ($consignmentsList as $consignment)
		{
			$groupedConsignments[$consignment['courierName']] = $consignment;
		}

		return $groupedConsignments;
	}
}


class BasicOrderDispatchSystem extends OrderDispatchSystem
{
	// All methods inherited from parent class
}