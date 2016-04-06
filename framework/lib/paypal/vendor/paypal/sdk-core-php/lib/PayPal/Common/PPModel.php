<?php 
namespace PayPal\Common;

/**
 * Generic Model class that all API domain classes extend
 * Stores all member data in a hashmap that enables easy 
 * JSON encoding/decoding
 */
class PPModel {

	private $_propMap = array();	
		
	public function __get($key) {
		return $this->_propMap[$key];
	}
	
	public function __set($key, $value) {
		$this->_propMap[$key] = $value;
	}
	
	public function __isset($key) {
		return isset($this->_propMap[$key]);
	}
	
	public function __unset($key) {
		unset($this->_propMap[$key]);
	}
	
	
	private function _convertToArray($param) {
		$ret = array();		
		foreach($param as $k => $v) {
			if($v instanceof PPModel ) {				
				$ret[$k] = $v->toArray();
			} else if (is_array($v)) {
				$ret[$k] = $this->_convertToArray($v);
			} else {
				$ret[$k] = $v;
			}
		}
		return $ret;
	}
	
	public function fromArray($arr) {
		
		foreach($arr as $k => $v) {
			if(is_array($v)) {
				$clazz = PPReflectionUtil::getPropertyClass(get_class($this), $k);
				
				if(PPArrayUtil::isAssocArray($v)) {
					$o = new $clazz();
					$o->fromArray($v);
					$this->__set($k, $o);
				} else {
					$arr =  array();		
					foreach($v as $nk => $nv) {
						if(is_array($nv)) {
							$o = new $clazz();
							$o->fromArray($nv);
							$arr[$nk] = $o;
						} else {
							$arr[$nk] = $nv;
						}
					}
					$this->__set($k, $arr);
				} 
			}else {
				$this->$k = $v;
			}
		}
	}
	
	public function fromJson($json) {
		$this->fromArray(json_decode($json, true));
	}
	
	public function toArray() {		
		return $this->_convertToArray($this->_propMap);
	}
	
	public function toJSON() {		
		return json_encode($this->toArray());
	}
}