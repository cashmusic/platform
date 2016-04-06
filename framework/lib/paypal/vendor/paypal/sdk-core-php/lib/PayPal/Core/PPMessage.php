<?php
namespace PayPal\Core;
use PayPal\Core\PPUtils;
/**
 * @author 
 */
abstract class PPMessage
{

	/**
	 * @param string $prefix
	 * @return string
	 */
	public function toNVPString($prefix = '')
	{
		$nvp = array();
		foreach (get_object_vars($this) as $property => $defaultValue) {
			
			if (($propertyValue = $this->{$property}) === NULL || $propertyValue == NULL) {
				continue;
			}

			if (is_object($propertyValue)) {
				$nvp[] = $propertyValue->toNVPString($prefix . $property . '.'); // prefix

			} elseif (is_array($defaultValue) || is_array($propertyValue)) {
				foreach (array_values($propertyValue) as $i => $item) {
					if (!is_object($item)){
                        $nvp[] = $prefix . $property . "($i)" . '=' . urlencode($item);
					}else{
                        $nvp[] = $item->toNVPString($prefix . $property . "($i).");
                    }
				}

			} else {
				// Handle classes with attributes
				if($property == 'value' && ($anno = PPUtils::propertyAnnotations($this, $property)) != NULL && isset($anno['value']) ) {
					$nvpKey = substr($prefix, 0, -1); // Remove the ending '.'
				} else {
					$nvpKey = $prefix . $property ;
				}
				$nvp[] = $nvpKey . '=' . urlencode($propertyValue);
			}
		}

		return implode('&', $nvp);
	}



	/**
	 * @param array $map
	 * @param string $prefix
	 */
	public function init(array $map = array(), $prefix = '')
	{
		if (empty($map)) {
			return;
		}

		$map = PPUtils::lowerKeys($map);

		foreach (get_object_vars($this) as $property => $defaultValue) {
			if (array_key_exists($propKey = strtolower($prefix . $property), $map) &&
					$this->isBuiltInType(($type = PPUtils::propertyType($this, $property)))){
				$type = PPUtils::propertyType($this, $property);				
				$this->{$property} = urldecode($map[$propKey]);
				continue; // string

			} elseif (!$filtered = PPUtils::filterKeyPrefix($map, $propKey)) {
				continue; // NULL
			}
		
			if (!class_exists($type = PPUtils::propertyType($this, $property)) && !$this->isBuiltInType($type)) {
				trigger_error("Class $type not found.", E_USER_NOTICE);
				continue; // just ignore
			}

			if (is_array($defaultValue) || PPUtils::isPropertyArray($this, $property)) { // array of objects				
				if($this->isBuiltInType($type)) { // Array of simple types					
					foreach($filtered as $key => $value) {
						$this->{$property}[trim($key, "()")] = urldecode($value);
					}
				} else { // Array of complex objects	
					$delim = '.';					
					for ($i = 0; $itemValues = PPUtils::filterKeyPrefix($filtered, "($i)") ;$i++) {					
						$this->{$property}[$i] = $item = new $type();
						$item->init(PPUtils::filterKeyPrefix($itemValues, "."));
						if(array_key_exists("", $itemValues)) {
							$item->value = urldecode($itemValues[""]);
						}
					}
					// Handle cases where we have a list of objects
					// with just the value present and all attributes values are null 
					foreach($filtered as $key => $value) {						
						$idx = trim($key, "()");						
						if(is_numeric($idx) && (is_null($this->{$property}) || !array_key_exists($idx, $this->{$property})) ) {
							$this->{$property}[$idx] = new $type;
							$this->{$property}[$idx]->value = urldecode($value);
						}						
					}
				}				
			} else { // one object
				$this->{$property} = new $type();
				$this->{$property}->init(PPUtils::filterKeyPrefix($filtered, '.')); // unprefix
				if(array_key_exists("", $filtered)) {
					$this->{$property}->value = urldecode($filtered[""]);
				}
			}
		}
	}

	private function isBuiltInType($typeName) {
		static $types = array('string', 'int', 'integer', 'bool', 'boolean', 'float', 'decimal', 'long', 'datetime', 'double');
		return in_array(strtolower($typeName), $types);
	}
}
