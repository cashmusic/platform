<?php
class AssetCache {
	public $enabled = false;
	public $writable = false;
	public $readable = false;
	public $cache_dir = null;
	public $cache_dir_webroot = null;
	
	public function __construct($cache_dir,$cache_dir_webroot) {
		if (file_exists($cache_dir)) {
			$this->enabled = true;
			$this->cache_dir = $cache_dir;
			$this->cache_dir_webroot = $cache_dir_webroot;
			if (is_writable($cache_dir)) {
				$this->writable = true;
			}
			if (is_readable($cache_dir)) {
				$this->readable = true;
			}
		}
	}

	public function setData($cache_name, $data_name, $data, $expires, $encode=true) {
		if ($this->enabled && $this->writable) {
			if ($encode) {
				$payload = json_encode($data);
				$file_extension = '.json';
			} else {
				$payload = $data;
				$file_extension = '.utf8';
			}
			$datafile = $this->cache_dir . '/' . $cache_name . '/' . $data_name . $file_extension;
			if (!file_exists($this->cache_dir . '/' . $cache_name)) {
				mkdir($this->cache_dir . '/' . $cache_name, 0777, true);
			}
			$success = file_put_contents($datafile, $payload);
			if ($success) {
				$this->setExpirationFor($cache_name, $data_name, $expires);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function getData($cache_name, $data_name, $force_last=false, $decode=true) {
		if ($decode) {
			$file_extension = '.json';
		} else {
			$file_extension = '.utf8';
		}
		$datafile = $this->cache_dir . '/' . $cache_name . '/' . $data_name . $file_extension;
		if ($this->enabled && $this->readable && file_exists($datafile)) {
			if ($force_last || $this->getExpirationFor($cache_name, $data_name) >= 0) {
				if ($decode) {
					return json_decode(@file_get_contents($datafile));
				} else {
					return @file_get_contents($datafile);
				}
			}
		} else {
			return false;
		}
	}
	
	public function setFile($cache_name, $file_uri, $expires) {
		if ($this->enabled && $this->writable) {
			if (!file_exists($this->cache_dir . '/' . $cache_name)) {
				mkdir($this->cache_dir . '/' . $cache_name, 0777, true);
			}
			$file_name = getFileName($file_uri);
			$success = copy($file_uri, $this->cache_dir . '/' . $cache_name . '/' . $file_name);
			if ($success) {
				$this->setExpirationFor($cache_name, $file_name, $expires);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function getFile($cache_name, $file_uri, $force_last=false) {
		$file_name = getFileName($file_uri);
		if ($this->enabled && $this->readable && file_exists($this->cache_dir . '/' . $cache_name . '/' . $file_name)) {
			if ($force_last || $this->getExpirationFor($cache_name, $file_name) >= 0) {
				return $this->cache_dir_webroot . '/' . $cache_name . '/' . $file_name;
			}
		} else {
			return false;
		}
	}
	
	private function getFileName($file_uri) {
		$replacees = array('http://','/');
		$replacers = array('','_');
		return str_replace($replacees,$replacers,substr($file_uri,0,strpos($file_uri, '?')));
	}
	
	private function setExpirationFor($cache_name, $data_or_file_name, $expires) {
		$expiration = 0;
		$expiration_name = $this->getExirationName($data_or_file_name);
		if ($expires) {
			$expiration = $expires * 60 + time();
		}
		$success = file_put_contents($this->cache_dir . '/' . $cache_name . '/' . $expiration_name, $expiration);
		if ($success) {
			return true;
		} else {
			return false;
		}
	}
	
	private function getExpirationFor($cache_name, $data_or_file_name) {
		$expiration_name = $this->getExirationName($data_or_file_name);
		if (file_exists($this->cache_dir . '/' . $cache_name . '/' . $expiration_name)) {
			$expiration = (int) @file_get_contents($this->cache_dir . '/' . $cache_name . '/' . $expiration_name, $expiration);
			if ($expiration != 0) {
				$remaining = $expiration - time();
			}
			return $remaining;
		} else {
			return false;
		}
	}
	
	private function getExirationName($data_or_file_name) {
		$expiration_name = '.' . str_replace('.','_',$data_or_file_name);
		return $expiration_name;
	}
}
?>