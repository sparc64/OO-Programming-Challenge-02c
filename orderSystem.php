<?php

Interface OrderDispatchInterface
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
		$newConsignmentID = $courier::generateConsignmentID();
		$courierName = $courier::getCourierName();
		$currentBatchID = $this->batchID;

		$this->consignmentStorage->save($currentBatchID, $courierName, $newConsignmentID, $consignmentData);
	}

	protected function generateBatchID() :int
	{
		return mt_rand();
	}

	/**
	 * Send consignment numbers to couriers using courier-specific methods
	 */
	protected function sendAllConsignments() :void
	{
		$couriers = $this->consignmentStorage->getBatchConsignmentsGroupedByCourier($this->batchID);

		foreach ($couriers as $courierName => $consignmentList)
		{
			$courier = new $courierName;

			$courier->sendConsignments($consignmentList);
		}
	}
}


class BasicOrderDispatchSystem extends OrderDispatchSystem
{
	// All methods inherited from parent class
}