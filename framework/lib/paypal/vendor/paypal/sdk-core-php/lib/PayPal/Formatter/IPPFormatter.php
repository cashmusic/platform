<?php
namespace PayPal\Formatter;
/**
 * Interface for all classes that format objects to
 * and from a on-wire representation
 * 
 * For every new payload format, write a new formatter
 * class that implements this interface
 *
 */
interface IPPFormatter {
	
	/**
	 * 
	 * @param PPRequest $request The request to format
	 * @param array $options Any formatter specific options 
	 *   to be passed in 
	 */
	public function toString($request, $options=array());
	
	public function toObject($string, $options=array());
}