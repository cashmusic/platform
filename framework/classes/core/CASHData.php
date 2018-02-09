<?php

namespace CASHMusic\Core;

use CASHMusic\Core\CASHSystem as CASHSystem;
/**
 * 所有分支和种子课程的数据存取。 CASHData摘出部分 
 * 数据处理，规定CASHDBA 对象为$this->db, 并为所有表格提供函数 
 * 来进入元数据。
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
 * 该文件由John Luini 和 chime.com 慷慨赞助
 * jon luini 和 chime.com 大力支持 cashmusic 提供进一步容易使用的开源工具
 * 给音乐家所做的努力!
 *
 */
abstract class CASHData {
	protected $db = false,
			  $cash_session_timeout = 10800,
			  $cash_session_data = null,
			  $cash_session_id = null,
			  $cache_enabled = false,
			  $cache_dir = null;

	/**
	 *
	 * 数据库连接
	 * 创建并安装新的CASHDBA
	 *
	 */

	/**
	 * 从/settings/cashmusic.ini.php里摄取数据连接属性
	 * 并打开相应的连接 
	 *
	 * @return void
	 */protected function connectDB() {
		$cash_db_settings = CASHSystem::getSystemSettings();
		require_once(CASH_PLATFORM_ROOT.'/classes/core/CASHDBA.php');
		$this->db = new CASHDBA(
			$cash_db_settings['hostname'],
			$cash_db_settings['username'],
			$cash_db_settings['password'],
			$cash_db_settings['database'],
			$cash_db_settings['driver']
		);
	}

	/**
	 *
	 * 会话处理器 
	 * CASH 会话管理. 使用手动执行缓存（仅会话id）
	 * 以及数据存储来持续. 允许多个网站服务器共用单一数据后台运行。
	 * 而且也意味着我们没有故意踩踏其他 
	 * 应用程序的会话数据。
	 *
	 */

	/**
	 * 把空白的登记（或创建空白）加入到标准的 CASH 会话.
	 * 只有在找到现有session_id 时才重设久数据
	 *
	 * @return boolean
	 */protected function resetSession() {
		if ($this->sessionGet('session_id','script')) {
			$session_id = $this->sessionGet('session_id','script');
			if (!$this->db) $this->connectDB();
			$this->db->setData(
				'sessions',
				array(
					'data' => json_encode(array()),
					'expiration_date' => time() + $this->cash_session_timeout
				),
				array(
					'session_id' => array(
						'condition' => '=',
						'value' => $session_id
					)
				)
			);
			$GLOBALS['cashmusic_script_store'] = array();
			$this->sessionSet('session_id',$session_id,'script');
		} else {
			$GLOBALS['cashmusic_script_store'] = array();
		}
	}

	/**
	 * 在用户机台上设置初始CASH session_id 和缓存 
	 *
	 * @return boolean
	 */public function startSession($force_session_id=false,$sandbox=false) {
		// 如果 'session_id' 已经设置在脚本商店，那么我们已经
		// 在此脚本里开始会话，请不要不必要地重复数据。
		$newsession = false;
		$expiration = false;
		$generate_key = false;
		$previous_session = false;
		if (!$this->db) $this->connectDB();
		if ($force_session_id) {
			$this->sessionSet('session_id',$force_session_id,'script');
		}
		if (!$this->sessionGet('start_time','script') || $force_session_id) {
			// 首先请确保我们已经有了一个有效对话 
			$current_session = $this->getAllSessionData();
			if ($current_session['persistent'] && isset($current_session['expiration_date'])) {
				// 找到会话数据，核对有效期 
				if ($current_session['expiration_date'] < time()) {
					$this->sessionClearAll();
					$current_session['persistent'] = false;
				}
			}
			$expiration = time() + $this->cash_session_timeout;
			$current_ip = CASHSystem::getRemoteIP();
			if ($force_session_id || $sandbox) {
				$session_id = $force_session_id;
			} else {
				$session_id = $this->getSessionID();
			}
			if ($session_id) {
				$session_exists = $this->db->getData(
					'sessions',
					'id',
					array(
						"session_id" => array(
							"condition" => "=",
							"value" => $session_id
						)
					)
				);
				if ($session_exists) {
					// 如果有还没有过期的现有对话，请使用它
					$previous_session = array(
						'session_id' => array(
							'condition' => '=',
							'value' => $session_id
						)
					);
				}
			} else {
				// 创建新对话 
				$newsession = true;
				$session_id = md5($current_ip['ip'] . rand(10000,99999)) . time(); // IP + random, hashed, plus timestamo
				$previous_session = false;
			}
			$session_data = array(
				'session_id' => $session_id,
				'expiration_date' => $expiration,
				'client_ip' => $current_ip['ip'],
				'client_proxy' => $current_ip['proxy']
			);
			if (!$current_session['persistent']) {
				// 没有现成的对话，设置空白数据 
				$session_data['data'] = json_encode(array(
					'created' => time()
				));
			}
			// 设置对话信息 
			$this->sessionSet('session_id',$session_id,'script');
			$this->sessionSet('start_time',time(),'script');

			// 设置数据库对话数据 
			$this->db->setData(
				'sessions',
				$session_data,
				$previous_session
			);

			if (!$sandbox && !$force_session_id) {
				// 设置客户端缓存 
				if (!headers_sent()) {
					// 还没有页眉，我们可以只是把缓存发送过去 
					setcookie('cashmusic_session', $session_id, $expiration, '/');
				}
			}
		} else {
			$session_id = $this->sessionGet('session_id','script');
		}

		// 错误记录 
		// error_log('starting session: ' . $session_id);

		return array(
			'newsession' => $newsession,
			'expiration' => $expiration,
			'id' => $session_id
		);
	}

	/**
	 * 返回一系列所有现有‘持续’和‘脚本’范围数据。
	 *
	 * @return array
	 */public function getAllSessionData() {
		$return_array = array(
			'persistent' => false,
			'script' => false
		);
		// 首先添加 script-scope stuff if set:
		if (isset($GLOBALS['cashmusic_script_store'])) {
			$return_array['script'] = $GLOBALS['cashmusic_script_store'];
		}
		$session_id = $this->getSessionID();
		if ($session_id) {
			if (!$this->db) $this->connectDB();
			$result = $this->db->getData(
				'sessions',
				'data,expiration_date',
				array(
					"session_id" => array(
						"condition" => "=",
						"value" => $session_id
					)
				)
			);
			if ($result) {
				$return_array['persistent'] = json_decode($result[0]['data'],true);
				$return_array['expiration_date'] = $result[0]['expiration_date'];
			}
		}
		return $return_array;
	}

	/**
	 * 返回CASH session_id
	 *
	 * @return boolean
	 */protected function getSessionID() {
		if (!$this->sessionGet('session_id','script') && isset($_COOKIE['cashmusic_session'])) {
			$this->sessionSet('session_id',$_COOKIE['cashmusic_session'],'script');
		}
		return $this->sessionGet('session_id','script');
	}

	/**
	 * 用新回应开取代 script-scoped 'cash_last_response'
	 *
	 * @param {array} $response - 新的 CASHResponse
	 * @param {boolean} $reset_session_id [default: false] - 如果是真的，那么新的 
	 *        会话id 会产生作为保障手段
	 * @return boolean
	 */protected function sessionSetLastResponse($response) {
		$this->sessionSet('cash_last_response',$response,'script');
		return true;
	}

	/**
	 * 返回现有的 script-scoped 'cash_last_response'数值
	 *
	 * @return array|false
	 */public function sessionGetLastResponse() {
		return $this->sessionGet('cash_last_response','script');
	}

	/**
	 * 设置  script-scoped 'cash_last_response' 为假的 
	 *
	 * @return array|false
	 */public function sessionClearLastResponse() {
		$this->sessionSet('cash_last_response',false,'script');
		return true;
	}

	/**
	 * 添加新数据到 CASH 会话 — 'persistent' (db)或者 'script' ($GLOBALS) 域
	 *
	 * @param {string} $key - 和新数据关联的密匙
	 * @param {*} $value - 要安装的数据 
	 * @return boolean
	 */public function sessionSet($key,$value,$scope='persistent') {
		if ($scope == 'persistent') {
			$session_id = $this->getSessionID();
			if ($session_id) {
				$session_data = $this->getAllSessionData();
				if (!$session_data['persistent']) {
					$this->resetSession();
					$session_data['persistent'] = array();
				}
				$session_data['persistent'][(string)$key] = $value;
				$expiration = time() + $this->cash_session_timeout;
				if (!$this->db) $this->connectDB();
				$this->db->setData(
					'sessions',
					array(
						'expiration_date' => $expiration,
						'data' => json_encode($session_data['persistent'])
					),
					array(
						'session_id' => array(
							'condition' => '=',
							'value' => $session_id
						)
					)
				);
				return true;
				// 记录错误
				// error_log('writing ' . $key . '(' . json_encode($value) . ') to session: ' . $session_id);
			}
			return false;
		} else {
			// 设域为  'script' -- 或你知道的任何东西 
			if (!isset($GLOBALS['cashmusic_script_store'])) {
				$GLOBALS['cashmusic_script_store'] = array();
			}
			$GLOBALS['cashmusic_script_store'][(string)$key] = $value;
			return true;
		}
	}

	/**
	 * 从 CASH 会话 返回数据到‘持续’或‘脚本’($GLOBALS) 域.
	 *
	 * @param {string} $key - 和所要求的数据相关联的密匙 
	 * @return *|false
	 */public function sessionGet($key,$scope='persistent') {
		if ($scope == 'persistent') {
			$session_data = $this->getAllSessionData();
			if (isset($session_data['persistent'][(string)$key])) {

				// 记录错误 
				// $session_id = $this->getSessionID();
				// error_log('reading ' . $key . '(' . json_encode($session_data['persistent'][(string)$key]) . ') from session: ' . $session_id);

				return $session_data['persistent'][(string)$key];
			} else {

				// 记录错误 
				// $session_id = $this->getSessionID();
				// error_log('reading ' . $key . '(false/empty) from session: ' . $session_id);

				return false;
			}
		} else {
			if (isset($GLOBALS['cashmusic_script_store'][(string)$key])) {
				return $GLOBALS['cashmusic_script_store'][(string)$key];
			} else {
				return false;
			}
		}
	}

	/**
	 * 从特定的密匙上移除密匙/数值输入 
	 *
	 * @param {string} $key - 要移除的密匙 
	 * @return void
	 */public function sessionClear($key,$scope='persistent') {
		if ($scope == 'persistent') {
			$session_data = $this->getAllSessionData();
			if (!$session_data['persistent']) {
				$this->resetSession();
			} else if (isset($session_data['persistent'][(string)$key])) {
				unset($session_data['persistent'][(string)$key]);
				$session_id = $this->getSessionID();
				$expiration = time() + $this->cash_session_timeout;
				$this->db->setData(
					'sessions',
					array(
						'expiration_date' => $expiration,
						'data' => json_encode($session_data['persistent'])
					),
					array(
						'session_id' => array(
							'condition' => '=',
							'value' => $session_id
						)
					)
				);
			}
		} else {
			if (isset($GLOBALS['cashmusic_script_store'][(string)$key])) {
				unset($GLOBALS['cashmusic_script_store'][(string)$key]);
			}
		}
	}

	/**
	 * 在数据库中重设会话，把缓存作废 
	 *
	 * @return void
	 */public function sessionClearAll() {
		$this->resetSession();
		// set the client-side cookie
		if (!headers_sent()) {
			// 如果页眉已经发送，缓存将 
			// 在下个 sessionStart()清除
			if (isset($_COOKIE['cashmusic_session'])) {
				setcookie('cashmusic_session', null, -1, '/');
			}
		}
	}

	/**
	 *
	 * 元数据
	 * 元数据可以通过域表格（别名） 和 id方式被应用到任何表格 
	 * 这些函数可以进入任何厂房.
	 *
	 */

	public function setMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key,$data_value) {
		// 试着找到准确的密匙/数值吻合 
		$selected_tag = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key,$data_value);
		if (!$selected_tag) {
			$data_key_exists = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key);
			if ($data_key == 'tag' || !$data_key_exists) {
				// 没有吻合的标签或密匙，因此我们可以创建一个新的 
				$result = $this->db->setData(
					'metadata',
					array(
						'scope_table_alias' => $scope_table_alias,
						'scope_table_id' => $scope_table_id,
						'type' => $data_key,
						'value' => $data_value,
						'user_id' => $user_id
					)
				);
			} else {
				// 密匙已存在并不是一个标签，因此我们需要编辑数值 
				$result = $this->db->setData(
					'metadata',
					array(
						'value' => $data_value
					),
					array(
						'id' => array(
							'condition' => '=',
							'value' => $data_key_exists[0]['id']
						)
					)
				);
			}
			return $result;
		} else {
			// 完全吻合: 元数据如要求存在，返回到真实 
			return $selected_tag['id'];
		}
	}

	public function getMetaData($scope_table_alias,$scope_table_id,$user_id,$data_key,$data_value=false) {
		// 为查询设置选项. 留下 $data_value 好扩大结果范围 
		// 默认 
		$options_array = array(
			"scope_table_alias" => array(
				"condition" => "=",
				"value" => $scope_table_alias
			),
			"scope_table_id" => array(
				"condition" => "=",
				"value" => $scope_table_id
			),
			"type" => array(
				"condition" => "=",
				"value" => $data_key
			),
			"user_id" => array(
				"condition" => "=",
				"value" => $user_id
			)
		);
		// 如果设置 $data_value, 把它添加到选项中进行二次检索（标签）
		if ($data_value) {
			$options_array['value'] = array(
				"condition" => "=",
				"value" => $data_value
			);
		}
		// 进行查询
		$result = $this->db->getData(
			'metadata',
			'*',
			$options_array
		);
		if ($result) {
			if ($data_value && $data_key != 'tag') {
				// $data_value 以为着一个独特的设置，可以直接进入数组 
				return $result[0];
			} else {
				// 如何没有 $data_value 设置，会出现多个结果（仅标签）
				return $result;
			}
		} else {
			return false;
		}
	}

	public function removeMetaData($metadata_id) {
		$result = $this->db->deleteData(
			'metadata',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $metadata_id
				)
			)
		);
		return $result;
	}

	public function removeAllMetaData($scope_table_alias,$scope_table_id,$user_id=false,$ignore_or_match='match',$data_key=false) {
		// 提前设置表格/id . 如果没有指出用户，则它将移除全部 
		// 给定表格+id的元数据  — 在删除母项目时将被主要使用
		$conditions_array = array(
			'scope_table_alias' => array(
				'condition' => '=',
				'value' => $scope_table_alias
			),
			'scope_table_id' => array(
				'condition' => '=',
				'value' => $scope_table_id
			)
		);
		if ($user_id) {
			// 如果 存在$user_id ，再次检索 
			$conditions_array['user_id'] = array(
				'condition' => '=',
				'value' => $user_id
			);
		}
		if ($data_key) {
			$key_condition = "=";
			if ($ignore_or_match = 'ignore') {
				$key_condition = "!=";
			}
			$conditions_array['type'] = array(
				"condition" => $key_condition,
				"value" => $data_key
			);
		}
		$result = $this->db->deleteData(
			'metadata',
			$conditions_array
		);
		return $result;
	}

	public function getAllMetaData($scope_table_alias,$scope_table_id,$data_key=false,$ignore_or_match='match') {
		$options_array = array(
			"scope_table_alias" => array(
				"condition" => "=",
				"value" => $scope_table_alias
			),
			"scope_table_id" => array(
				"condition" => "=",
				"value" => $scope_table_id
			)
		);
		// 每个用户每个表格+id 的大多数$data_keys 是独特的，但标签需要多个 
		// 因此我们要添加一个过滤器. 通过 'tag' 作为最后的选项来取得getAllMetaData
		// 为单个表格+id取得所有标签行数组
		if ($data_key) {
			$key_condition = "=";
			if ($ignore_or_match == 'ignore') {
				$key_condition = "!=";
			}
			$options_array['type'] = array(
				"condition" => $key_condition,
				"value" => $data_key
			);
		}
		$result = $this->db->getData(
			'metadata',
			'*',
			$options_array
		);
		if ($result) {
			$return_array = array();
			foreach ($result as $row) {
				if ($data_key == 'tag' && $ignore_or_match == 'match') {
					$return_array[] = $row['value'];
				} else {
					if ($row['type'] !== 'tag') {
						$return_array[$row['type']] = $row['value'];
					}
				}
			}
			return $return_array;
		} else {
			return false;
		}
	}

	public function setAllMetaData($scope_table_alias,$scope_table_id,$user_id,$tags=false,$metadata=false,$delete_existing=false) {
		// 也需要添加$ignore_or_match='match',$data_key=false 到 removeAllMetaData 上

		if ($tags) {
			if ($delete_existing) {
				// 如果delete_existing 已设，移除所有标签 
				$this->removeAllMetaData($scope_table_alias,$scope_table_id,$user_id,'match','tag');
			}
			// 首先取得现有标签，然后移除没在列表中的那些。
			$current_tags = $this->getAllMetaData($scope_table_alias,$scope_table_id,$user_id,'match','tag');
			if ($current_tags) {
				foreach ($current_tags as $tag) {
					if (!in_array($tag, $tags)) {
						$tag_details = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,'tag',$tag);
						$tag_id = $tag_details[0]['id'];
						$this->removeMetaData($tag_id);
					}
				}
			}
			// 在所有已通过的标签上运行 setMetaData - 将编辑现有标签并添加新的
			foreach ($tags as $tag) {
				$this->setMetaData($scope_table_alias,$scope_table_id,$user_id,'tag',$tag);
			}
		}
		if ($metadata) {
			if ($delete_existing) {
				// 如果delete_existing 已设，移除所有非标签的元数据 
				$this->removeAllMetaData($scope_table_alias,$scope_table_id,$user_id,'ignore','tag');
			}
			$current_metadata = $this->getAllMetaData($scope_table_alias,$scope_table_id,$user_id,'ignore','tag');
			if ($current_metadata) {
				foreach ($current_metadata as $key => $value) {
					if (!array_key_exists($key, $metadata)) {
						$metadata_details = $this->getMetaData($scope_table_alias,$scope_table_id,$user_id,$key,$value);
						$metadata_id = $metadata_details['id'];
						$this->removeMetaData($tag_id);
					}
				}
			}
			foreach ($metadata as $key => $value) {
				$this->setMetaData($scope_table_alias,$scope_table_id,$user_id,$key,$value);
			}
		}
	}

	/**
	 *
	 * 更新/数据缓存
	 * 阅读和编写数据到文件的函数 — 二者对原始数据和结构性JSON 都是很有用的.
	 * 主要作用于从API获取更新等.
	 *
	 */

	/**
	 * 为 JSON/更新缓存准备好基础文件缓存 — 关键只是进行测试 
	 * 来确保缓存目录存在并可书写. primeCache() 将成功
	 * 设置 $this->cache_enabled 为真实.
	 *
	 * @return void
	 */public function primeCache($cache_dir=false) {
	 	if (!$this->cache_enabled) {
			if (!$cache_dir) {
				$cache_dir = CASH_PLATFORM_ROOT.'/cache';
			}
			if (file_exists($cache_dir)) {
				$this->cache_dir = $cache_dir;
				if (is_writable($cache_dir) && is_readable($cache_dir)) {
					$this->cache_enabled = true;
				}
			} else {
				if (mkdir($cache_dir)) {
					$this->cache_dir = $cache_dir;
					$this->cache_enabled = true;
				}
			}
		}
	}

	/**
	 * Sets the contents of a given cache file. Setting $encode will tell it to
	 * encode the data as JSON or not.
	 *
	 * @return string or decoded JSON object/array
	 */public function setCacheData($cache_name, $data_name, $data, $encode=true) {
	 	$this->primeCache();
		if ($this->cache_enabled) {
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

	/**
	 * 拿到给定缓存文件的内容. 如果$force_last 已设置，无论如何，它始终将
	 * 忽略终结状态并只是返回数据到文件中去.
	 * 设置$decode 将告诉它是否把数据解析为JSON.
	 *
	 * @return string or decoded JSON object/array
	 */public function getCacheData($cache_name, $data_name, $force_last=false, $decode=true, $associative=true) {
	 	$this->primeCache();
		if ($decode) {
			$file_extension = '.json';
		} else {
			$file_extension = '.utf8';
		}
		$datafile = $this->cache_dir . '/' . $cache_name . '/' . $data_name . $file_extension;
		if ($this->cache_enabled && file_exists($datafile)) {
			if ($force_last || $this->getCacheExpirationFor($datafile) >= 0) {
				if ($decode) {
					return json_decode(@file_get_contents($datafile),$associative);
				} else {
					return @file_get_contents($datafile);
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * 依照已过的期间，测试是否给定的数据已经过期.
	 *
	 * @return int (remaining time in seconds) or false
	 */private function getCacheExpirationFor($datafile, $cache_duration=600) {
		$expiration = @filemtime($datafile) + $cache_duration;
		if ($expiration) {
			$remaining = $expiration - time();
			return $remaining;
		} else {
			return false;
		}
	}

	/**
	 * 取一个缓存名称，数据名称和 URL — 首先找缓存数据的变量，
	 * 然后
	 *
	 * @return int (remaining time in seconds) or false
	 */public function getCachedURL($cache_name, $data_name, $data_url, $format='json', $decode=true) {
		$url_contents = $this->getCacheData($cache_name,$data_name,false,$decode);
		if (!$url_contents) {
			$url_contents = CASHSystem::getURLContents($data_url);
			if (!$url_contents) {
				$url_contents = $this->getCacheData($cache_name,$data_name,true,$decode);
			} else {
				if ($format == 'json') {
					$url_contents = json_decode($url_contents, true);
				}
				$this->setCacheData($cache_name,$data_name,$url_contents);
			}
		}
		return $url_contents;
	}

	/**
	 *
	 * 连接
	 * 取得更多的第三方连接信息
	 *
	 */

	/**
	 * 为connection_id 返回连接类型 
	 *
	 * @return string or false
	 */public function getConnectionDetails($connection_id) {
		$result = $this->db->getData(
			'connections',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $connection_id
				)
			)
		);
		if ($result) {
			return $result[0];
		} else {
			return false;
		}
	}

	/**
	 * 为connection_id 返回连接类型 
	 *
	 * @return string or false
	 */protected function getConnectionType($connection_id) {
		$result = $this->getConnectionDetails($connection_id);
		if ($result) {
			return $result['type'];
		} else {
			return false;
		}
	}


} // 课程结束
?>
