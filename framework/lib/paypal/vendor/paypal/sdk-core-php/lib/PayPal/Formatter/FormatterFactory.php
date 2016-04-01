<?php
namespace PayPal\Formatter;
class FormatterFactory {
	public static function factory($bindingType) {
		switch ($bindingType) {
			case 'NV':
				return new PPNVPFormatter();
				break;
			case 'SOAP':
				return new PPSOAPFormatter();
				break;
			default:
				throw new \InvalidArgumentException("Invalid value for bindingType. You passed $bindingType");	
		}
	}
}