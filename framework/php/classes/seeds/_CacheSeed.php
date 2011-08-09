<?php
class AssetCache {
	public $enabled = false;
	public $cache_dir = null;
	
	public function __construct($cache_dir) {
		if (file_exists($cache_dir)) {
			$this->cache_dir = $cache_dir;
			if (is_writable($cache_dir) && is_readable($cache_dir)) {
				$this->enabled = true;
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
			return $success;
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

	private function getExpirationFor($cache_name, $data_name, $file_extension='.json', $cache_duration=1200) {
		$datafile = $this->cache_dir . '/' . $cache_name . '/' . $data_name . $file_extension;
		$expiration = @filemtime($datafile) + $cache_duration;
		if ($expiration) {
			$remaining = $expiration - time();
			return $remaining;
		} else {
			return false;
		}
	}
}
?>