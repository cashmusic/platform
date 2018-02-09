<?php

namespace CASHMusic\Core;

use CASHMusic\Core\CASHData as CASHData;
use CASHMusic\Core\CASHSystem as CASHSystem;

/**
 * CASHConnection 从数据库里存储和检索第三方API连接设置。
 * API 设置的定义以JSON 平面文件格式存储在/settings/connections里。 
 * 并在该课程里读入. 实际API key和所需要的设置是以JSON格式存在在数据库里的设置表格里。
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * 版权 (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * 参照 http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * 该文件由Anchor Brain慷慨赞助。 
 * Anchor Brain: 总部在Providence 的一家唱片公司，他们主要发行的乐队有： 
 * Doomsday Student, What Cheer? Brigade, Six Finger Satellite. 网站: anchorbrain.com
 *
 */class CASHConnection extends CASHData {
	public $user_id,$connection_id,$connection_name,$creation_date;

	public function __construct($user_id=false,$connection_id=false) {
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		$this->settings = null;
		$this->connectDB();
	}

	/**
	 *
	 * 平台 / 普通用户设置
	 * 这些功能不处理特定的设置，它们查找平台上有什么是现成的，以及为指定用户寻找各种设置等。
	 *
	 */

	/**
	 * 查找所有 JSON 格式的设置文件, 建构类型输入的数组。
	 *
	 * @返回数组
	 */public function getConnectionTypes($filter_by_scope=false,$force_all=false) {
		if ($settings_dir = opendir(CASH_PLATFORM_ROOT.'/settings/connections')) {
			$settings_types = false;
			$tmp_array = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/connections/supported.json'),true);
			$filter_array = $tmp_array['public'];

			if (defined('SHOW_BETA')) {
				if (SHOW_BETA) {
					if (is_array($tmp_array['beta']) && !$force_all) {
						$filter_array = array_merge($tmp_array['public'],$tmp_array['beta']);
					}
				}
			}
			while (false !== ($file = readdir($settings_dir))) {
				if (substr($file,0,1) != "." && !is_dir($file)) {
					$tmp_key = strtolower(substr_replace($file, '', -5));
					$add_to_settings = true;
					if (!$force_all) {
						if (!in_array($tmp_key, $filter_array)) {
							$add_to_settings = false;
						}
					}
					if ($add_to_settings) {
						$tmp_value = json_decode(file_get_contents(CASH_PLATFORM_ROOT.'/settings/connections/'.$file),true);
						if ($filter_by_scope) {
							if (!in_array($filter_by_scope, $tmp_value['scope'])) {
								$tmp_value = false;
							}
						}
						if ($tmp_value !== false) {
							if (!$settings_types) { $settings_types = array(); }
							$settings_types["$tmp_key"] = $tmp_value;
						}
					}
				}
			}
			closedir($settings_dir);
			return $settings_types;
		} else {
			return false;
		}
	}

	/**
	 * 帮指定用户返回所有设置
	 *
	 * @返回数组
	 */public function getAllConnectionsforUser() {
		if ($this->user_id) {
			$result = $this->db->getData(
				'connections',
				'*',
				array(
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
			return $result;
		} else {
			return false;
		}
	}

	/**
	 *
	 * 特定会话功能
	 * 这些返回或设置单个设置
	 *
	 */

	/**
	 * 为CASHConnection对象示例的id返回解密JSON设置。 
	 *
	 * @返回设置对象
	 */public function getConnectionSettings($id_override=false) {
		if (!$id_override) {
			$connection_id = $this->connection_id;
		} else {
			$connection_id = $id_override;
		}
		if ($connection_id) {
			$result = $this->db->getData(
				'connections',
				'name,data,creation_date',
				array(
					"id" => array(
						"condition" => "=",
						"value" => $connection_id
					),
					"user_id" => array(
						"condition" => "=",
						"value" => $this->user_id
					)
				)
			);
			if ($result) {
				$this->settings = json_decode(CASHSystem::simpleXOR(base64_decode($result[0]['data'])),true);
				$this->connection_name = $result[0]['name'];
				$this->creation_date = $result[0]['creation_date'];;
				return $this->settings;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 *
	 * @返回设置对象 
	 */public function getConnectionsByType($settings_type) {
		$result = $this->db->getData(
			'connections',
			'*',
			array(
				"type" => array(
					"condition" => "=",
					"value" => $settings_type
				),
				"user_id" => array(
					"condition" => "=",
					"value" => $this->user_id
				)
			)
		);
		return $result;
	}

	/**
	 * 为特地的连接范围返回解密JSON
	 *
	 * @返回设置对象
	 */public function getConnectionsByScope($scope) {
		$connection_types_data = $this->getConnectionTypes($scope);
		$applicable_settings_array = false;
		$all_connections = $this->getAllConnectionsforUser();
		$filtered_connections = array();

		if (is_array($all_connections)) {
			foreach ($all_connections as $key => $data) {
				if (is_array($connection_types_data)) {
					if (array_key_exists($data['type'],$connection_types_data)) {
						$filtered_connections[] = $data;
					}
				}
			}
		}

		if (count($filtered_connections)) {
			foreach ($filtered_connections as &$connection) {
				$connection['data'] = json_decode(CASHSystem::simpleXOR(base64_decode($connection['data'])),true);
			}
			return $filtered_connections;
		} else {
			return false;
		}
	}

	/**
	 * 返回特定设置
	 *
	 * @param {string} 设置名称
	 * @返回设置对象
	 */public function getSetting($setting_name) {
		if (isset($this->settings[(string)$setting_name])) {
			return $this->settings[(string)$setting_name];
		} else {
			return false;
		}
	}

	/**
	 *
	 *
	 * @param {array} settings_data: 设置数据作为组合数组
	 * @return boolean
	 */public function setSettings($settings_name,$settings_type,$settings_data,$connection_id=false) {
		$settings_data = json_encode($settings_data);
		if ($connection_id) {
			$settings_condition = array(
				'id' => array(
					'condition' => '=',
					'value' => $connection_id
				)
			);
		} else {
			$settings_condition = false;
		}

		$result = $this->db->setData(
			'connections',
			array(
				'name' => $settings_name,
				'type' => $settings_type,
				'user_id' => $this->user_id,
				'data' => base64_encode(CASHSystem::simpleXOR($settings_data))
			),
			$settings_condition
		);
		return $result;
	}

	/**
	 *
	 *
	 * @param {array} settings_data: 设置数据作为组合数组
	 * @return boolean
	 */public function updateSettings($settings_data) {
		$settings_data = json_encode($settings_data);

		$result = $this->db->setData(
			'connections',
			array(
				'data' => base64_encode(CASHSystem::simpleXOR($settings_data))
			),
			array(
				'id' => array(
					'condition' => '=',
					'value' => $this->connection_id
				)
			)
		);
		return $result;
	}

	/**
	 *
	 *
	 * @param {int} connection_id
	 * @return boolean
	 */public function deleteSettings($connection_id) {
		$result = $this->db->deleteData(
			'connections',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $connection_id
				)
			)
		);
		return $result;
	}

	/**
	 * 确保每个用户有独特的指定名字/类型组合。
	 *
	 * @return boolean
	 */private function checkUniqueName($settings_name,$settings_type) {
		$result = $this->db->getData(
			'connections',
			'name',
			array(
				'type' => array(
					'condition' => '=',
					'value' => $settings_type
				),
				'name' => array(
					'condition' => '=',
					'value' => $settings_name
				),
				'user_id' => array(
					'condition' => '=',
					'value' => $this->user_id
				)
			)
		);
		if ($result) {
			return false;
		} else {
			return true;
		}
	}
} // 课程结束
?>
