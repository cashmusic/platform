<?php
namespace PayPal\Handler;
interface IPPHandler {
	/**
	 * 
	 * @param PPHttpConfig $httpConfig
	 * @param PPRequest $request 
	 */
	public function handle($httpConfig, $request, $options);
}