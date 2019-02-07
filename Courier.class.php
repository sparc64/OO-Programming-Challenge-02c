<?php

interface CourierInterface
{
	/**
	 * Method for consignment ID generation
	 *
	 * @return string
	 */
	public static function generateConsignmentID() :string;

	/**
	 * Method for getting courier name (equal to class name)
	 *
	 * @return string - courier name
	 */
	public static function getCourierName() :string;

	/**
	 * Method to send list of consignments to courier
	 *
	 * @param array $consignmentList
	 */
	public function sendConsignments(array $consignmentList) :void;
}


abstract class Courier implements CourierInterface
{
	public static function generateConsignmentID(): string
	{
		return mt_rand();
	}

	// Courier name could be used for class init, hence final keyword
	final public static function getCourierName(): string
	{
		return static::class;
	}
}