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
	public function addConsignment(Courier $courier) :void;
}