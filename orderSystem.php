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
	 * @param Courier $courier - specific courier supplied as dependency injection for flexibility and easy testing.
	 */
	public function addConsignment(Courier $courier, ConsignmentData $consignmentData) :void;
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
		// Send consignment numbers to couriers using courier-specific methods
		$storage = $this->consignmentStorage;

		$couriers = $storage->getBatchConsignmentsGroupedByCourier($this->batchID);

		foreach ($couriers as $courierName => $consignmentList)
		{
			$courier = new $courierName;

			$courier->sendConsignments($consignmentList);
		}

		// Close current batch
		$this->batchID = null;
	}

	public function addConsignment(Courier $courier, ConsignmentData $consignmentData) :void
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
}


class BasicOrderDispatchSystem extends OrderDispatchSystem
{
	// All methods inherited from parent class
}