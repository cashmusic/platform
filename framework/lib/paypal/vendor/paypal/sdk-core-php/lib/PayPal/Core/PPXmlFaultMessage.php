<?php
namespace PayPal\Core;

/**
 * Base class for SOAP Fault message
 * Contains redundant code from PPXmlMessage in order
 * to be able to extend 'Exception' here.
 *
 * TODO: Refactor XMLMessage functionality to a visitor
 * class so it can be made an interface
 */
abstract class PPXmlFaultMessage extends \Exception {
	/**
	 * @param array $map
	 * @param string $isRoot
	 */
	public function init(array $data = array()) {

		foreach($data[0]['children'] as $c) {
			if($c['name'] == 'detail') {
				$map = $c['children'][0]['children'];
				break;
			}
		}

		if (!isset($map)) {
			return;
		}

		if (($first = reset($map)) && !is_array($first) && !is_numeric(key($map))) {
			parent::init($map, false);
			return;
		}

		$propertiesMap = PPUtils::objectProperties($this);
		$arrayCtr = array();		
		foreach ($map as $element) {
		
			if (empty($element) || empty($element['name'])) {
				continue;

			} elseif (!array_key_exists($property = strtolower($element['name']), $propertiesMap)) {
				if (!preg_match('~^(.+)[\[\(](\d+)[\]\)]$~', $property, $m)) {
					continue;
				}

				$element['name'] = $m[1];
				$element['num'] = $m[2];
			}
			$element['name'] = $propertiesMap[strtolower($element['name'])];
			if(PPUtils::isPropertyArray($this, $element['name'])) {				
				$arrayCtr[$element['name']] = isset($arrayCtr[$element['name']]) ? ($arrayCtr[$element['name']]+1) : 0;				
				$element['num'] = $arrayCtr[$element['name']];
			} 
			if (!empty($element["attributes"]) && is_array($element["attributes"])) {
				foreach ($element["attributes"] as $key => $val) {
					$element["children"][] = array(
						'name' => $key,
						'text' => $val,
					);
				}

				if (isset($element['text'])) {
					$element["children"][] = array(
						'name' => 'value',
						'text' => $element['text'],
					);
				}

				$this->fillRelation($element['name'], $element);

			} elseif (!empty($element['text'])) {
				$this->{$element['name']} = $element['text'];

			} elseif (!empty($element["children"]) && is_array($element["children"])) {
				$this->fillRelation($element['name'], $element);
			}
		}		
	}



	/**
	 * @param string $property
	 * @param array $element
	 */
	private function fillRelation($property, array $element)
	{
		if (!class_exists($type = PPUtils::propertyType($this, $property))) {
			trigger_error("Class $type not found.", E_USER_NOTICE);
			return; // just ignore
		}

		if (isset($element['num'])) { // array of objects
			$this->{$property}[$element['num']] = $item = new $type();
			$item->init($element['children'], false);

		} else {
			$this->{$property} = new $type();
			$this->{$property}->init($element["children"], false);
		}
	}
}
