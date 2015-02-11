<?php
/**
 * The AdminHelper class provides a single location for various formatting and 
 * quick processing methods needed throughout the admin
 * 
 * Most functions that are simple/static framework wrappers or data formatting should go here
 *
 * @package admin.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */abstract class AdminHelper  {

	public static function doLogin($email_address,$password,$require_admin=true) {
		global $admin_primary_cash_request;
		$admin_primary_cash_request->processRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => $email_address, 
				'password' => $password,
				'require_admin' => $require_admin
			)
		);
		return $admin_primary_cash_request->response['payload'];
	}

	/**********************************************
	 *
	 * PAGE/UI RENDERING DETAILS
	 *
	 *********************************************/

	public static function getPageMenuDetails() {
		$pages_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/interface/en/menu.json'),true);
		// remove non-multi links
		$platform_type = CASHSystem::getSystemSettings('instancetype');
		if ($platform_type == 'multi') {
			unset($pages_array['settings/update'],$pages_array['people/contacts']);
		}
		// make an array for return
		$return_array = array(
			'page_title' => 'CASH Music',
			'tagline' => null,
			'section_menu' => '',
			'link_text' => null
		);

		// generate submenu markup
		$current_endpoint = '';
		$previous_endpoint = '';
		$menustr = '';

		foreach ($pages_array as $page_endpoint => $page) {
			$exploded = explode('/',$page_endpoint);
			$current_endpoint = $exploded[0];
			if ($current_endpoint !== $previous_endpoint) {
				if ($previous_endpoint !== '') {
					$menustr .= '</ul>';
					$return_array[$previous_endpoint . '_section_menu'] = $menustr;
				}
				$menustr = '<ul>';
				$previous_endpoint = $current_endpoint;
			}

			$menulevel = substr_count($page_endpoint, '/');
			if ($menulevel == 1 && !isset($page['hide'])) { // only show top-level menu items
				$menustr .= "<li><a class=\"{$page['add_class']}\" href=\"" . ADMIN_WWW_BASE_PATH . "/$page_endpoint/\"><span>{$page['page_name']}</span><div class=\"icon icon-{$page['menu_icon']}\"></div><!--icon--></a></li>";
			}
		}

		// find the right page title
		$endpoint = str_replace('_','/',BASE_PAGENAME);
		$endpoint_parts = explode('/',$endpoint);
		if (isset($pages_array[$endpoint])) {
			$current_title = '';
			if (count($endpoint_parts) > 1) {
				$current_title .= $pages_array[$endpoint_parts[0]]['page_name'] . ': ';
			}
			$current_title .= $pages_array[$endpoint]['page_name'];
			$return_array['page_title'] = $current_title;
			if (isset($pages_array[$endpoint]['tagline'])) {
				$return_array['tagline'] = $pages_array[$endpoint]['tagline'];
			}
		}

		// set link text for the main template
		$return_array['link_text'] = array(
			'link_main_page' => $pages_array['mainpage']['page_name'],
			'link_menu_assets' => $pages_array['assets']['page_name'],
			'link_menu_people' => $pages_array['people']['page_name'],
			'link_menu_commerce' => $pages_array['commerce']['page_name'],
			'link_menu_calendar' => $pages_array['calendar']['page_name'],
			'link_menu_elements' => $pages_array['elements']['page_name'],
			'link_menu_help' => $pages_array['help']['page_name'],
			'link_menu_help_gettingstarted' => $pages_array['help/gettingstarted']['page_name'],
			'link_youraccount' => $pages_array['account']['page_name'],
			'link_settings' => $pages_array['settings']['page_name'],
			'link_settings_connections' => $pages_array['settings/connections']['page_name']
		);

		return $return_array;
	}

	public static function getUiText() {
		$text_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/interface/en/interaction.json'),true);
		return $text_array;
	}

	public static function getPageComponents() {
		if (file_exists(dirname(__FILE__).'/../components/text/en/pages/' . BASE_PAGENAME . '.json')) {
			$components_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/text/en/pages/' . BASE_PAGENAME . '.json'),true);
		} else {
			$components_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/text/en/pages/default.json'),true);
		}
		return $components_array;
	}

	/**********************************************
	 *
	 * CONNECTION DETAILS
	 *
	 *********************************************/
	
	/**
	 * Finds settings matching a specified scope and echoes them out formatted
	 * for a dropdown box in a form
	 *
	 */public static function echoConnectionsOptions($scope,$selected=false,$return=false) {

		AdminHelper::getConnectionsByScope($scope);
		
		$all_connections = '<option value="0">None</option>';

		// echo out the proper dropdown bits
		if ($applicable_settings_array) {
			$settings_count = 1;
			foreach ($applicable_settings_array as $setting) {
				$echo_selected = '';
				if ($setting['id'] == $selected) { $echo_selected = ' selected="selected"'; }
				$all_connections .= '<option value="' . $setting['id'] . '"' . $echo_selected . '>' . $setting['name'] . '</option>';
			}
			if ($return) {
				return $all_connections;
			} else {
				echo $all_connections;
			}
		}
	}

	//get connections scope
	public static function getConnectionsByScope($scope){
		
		// get system settings:
		$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
		$applicable_settings_array = $page_data_object->getConnectionsByScope($scope);

		return $applicable_settings_array;
	}

	/**
	 * Returns the name given to a specific Connection
	 *
	 */public static function getConnectionName($connection_id) {
		$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
		$connection_name = false;
		$connection_details = $page_data_object->getConnectionDetails($connection_id);
		if ($connection_details) {
			$connection_name = $connection_details['name'];
		}
		return $connection_name;
	}

	/**********************************************
	 *
	 * ELEMENT DETAILS
	 *
	 *********************************************/
	public static function getElementAppJSON($element_type) {
		$element_dirname = CASH_PLATFORM_ROOT.'/elements/' . $element_type;
		if (file_exists($element_dirname . '/app.json')) {
			$app_json = json_decode(file_get_contents($element_dirname . '/app.json'),true);
			return $app_json;
		} else {
			return false;
		}
	}

	public static function getElementTemplate($element_type) {
		$app_json = AdminHelper::getElementAppJSON($element_type);
		if ($app_json) {
			// count sections
			$sections_required = array();
			$sections_optional = array();
			foreach ($app_json['options'] as $section_name => $details) {
				foreach ($details['data'] as $data => $values) {
					if (isset($values['required'])) {
						if ($values['required']) {
							$sections_required[$section_name] = $details;
							break;
						}
					}
				}
				if (!isset($sections_required[$section_name])) {
					$sections_optional[$section_name] = $details;
				}
			}
			$all_sections = array_merge($sections_required,$sections_optional);
			$total_sections = count($sections_required);

			$template =	'<div class="gallery elementinstructions"><h5>Learn more:</h5>' .
						'<p class="big">{{details_longdescription}}</p><p>{{{details_instructions}}}</p></div>' . 
						'<div class="elementform"><form method="post" action="{{www_path}}/elements/{{#element_id}}edit/{{element_id}}{{/element_id}}{{^element_id}}add/' . $element_type . '{{/element_id}}" class="multipart" data-parts="' . $total_sections . '">' .
						'<input type="hidden" name="{{form_state_action}}" value="makeitso">' .
						'{{#element_id}}<input type="hidden" name="element_id" value="{{element_id}}" />{{/element_id}}' .
						'<input type="hidden" name="element_type" value="' . $element_type . '" />' .
						'<input type="hidden" name="in_campaign" id="in_campaign" value="" />' .
						'<div class="section basic-information row" data-section-name="Element name">' .
						'<p class="section-description">Give the element a name for your own reference.</p>' .
						'<label for="element_name">Element name</label>' .
						'<input type="text" id="element_name" name="element_name" value="{{#element_name}}{{element_name}}{{/element_name}}"{{^element_name}} placeholder="Name your element"{{/element_name}} class="required" />' .
						'</div>';

			$current_section = 1;
			foreach ($all_sections as $section_name => $details) {
				$template .= '<div class=" section part-' . $current_section . '" data-section-name="' . $details['group_label']['en'] . '">' .
						     '<h5 class="section-header">' . $details['group_label']['en'] . '</h5>' .
						     '<p class="section-description">' . $details['description']['en'] . '</p>' .
						     '<div class="row">';
				$current_data = 0;
				$current_count = 0;
				$total_count = count($details);
				$data_keys = array_keys($details['data']);
				foreach ($details['data'] as $data => $values) {
					$current_count++;
					$nextnotsmall = true;
					if (isset($data_keys[$current_data + 1])) {
						if ($details['data'][$data_keys[$current_data + 1]]['displaysize'] !== 'small') {
							$nextnotsmall = true;
						} else {
							$nextnotsmall = false;
						}
					}
					if ($current_count == 4 || 
						$current_data == ($total_count) || 
						$values['displaysize'] !== 'small' ||
						$nextnotsmall
					) {
						switch ($current_count) {
							case 4:
								$column_width_text = "pure-u-1 pure-u-md-1-2";
								break;
							case 3:
								$column_width_text = "pure-u-1 pure-u-md-1-3";
								break;
							case 2:
								$column_width_text = "pure-u-1 pure-u-md-1-2";
								break;
							case 1:
								$column_width_text = "pure-u-1";
								break;
						}

						// single small element — make sure it's not full width
						if ($values['displaysize'] == 'small' && $current_count == 1) {
							$column_width_text = "pure-u-1 pure-u-md-1-3";
						}

						for ($i=1; $i < $current_count+1; $i++) { 
							$template .= '<div class="' . $column_width_text . '">' .
										 // contents
										 AdminHelper::drawInput(
										 	$data_keys[$current_data - ($current_count - $i)],
										 	$details['data'][$data_keys[$current_data - ($current_count - $i)]]
										 ) .
										 '</div>';
						}

						// single small element — make sure it's not full width
						if ($values['displaysize'] == 'small' && $current_count == 1) {
							$template .= '<div class="pure-u-1 pure-u-md-1-3"></div><div class="pure-u-1 pure-u-md-1-3"></div>';
						}

						if ($current_data !== ($total_count - 1)) {
							$template .= '</div><div class="row">';
						}
						$current_count = 0;
					}
					$current_data++;
				}
				$template .= '</div></div>';
				$current_section++;
			}

			$template .= '<br /><input class="button" type="submit" value="{{element_button_text}}" /></form></div>';
			return $template;
		} else {
			return false;
		}
	}

	public static function drawInput($input_name,$input_data) {
		// label (for everything except checkboxes)
		if ($input_data['type'] !== 'boolean') {
			$return_str = '<label for="' . $input_name . '">' . $input_data['label']['en'] . '</label>';
		}

		/*
		 start outputting markup depending on type
		*/
		if ($input_data['type'] == 'text' || $input_data['type'] == 'number') {
			if ($input_data['type'] == 'text' && $input_data['displaysize'] == 'large') {
				$return_str .= '<textarea id="' . $input_name . '" name="' . $input_name . '" class="';
			} else {
				$return_str .= '<input type="text" id="' . $input_name . '" name="' . $input_name . '" value="{{#options_' . $input_name . 
				'}}{{options_' . $input_name . '}}{{/options_' . $input_name . '}}{{^options_' . $input_name . '}}{{element_copy_' . $input_name . '}}{{/options_' . $input_name . '}}" class="';
			}
		}
		if ($input_data['type'] == 'select') {
			$return_str .= '<select id="' . $input_name . '" name="' . $input_name . '">';
		}
		if ($input_data['type'] == 'boolean') {
			$return_str = '<label class="checkbox" for="' . $input_name . '"><input type="checkbox" class="checkorradio" id="' . $input_name . '" name="' . $input_name . '"';
		}

		/*
		 declare any classes that need declaring (form validation or special functionality)
		*/
		if (isset($input_data['required'])) {
			if ($input_data['required']) {
				$return_str .= ' required';
			}
		}
		if ($input_data['type'] == 'number') {
			$return_str .= ' number';
		}

		/*
		 close out markup
		*/
		if ($input_data['type'] == 'text' || $input_data['type'] == 'number') {
			if ($input_data['type'] == 'text' && $input_data['displaysize'] == 'large') {
				$return_str .= '">{{#options_' . $input_name . 
				'}}{{options_' . $input_name . '}}{{/options_' . $input_name . '}}{{^options_' . $input_name . '}}{{element_copy_' . $input_name . '}}{{/options_' . $input_name . '}}</textarea>';
			} else {
				if (isset($input_data['placeholder'])) {
					$return_str .= ' placeholder="' . $input_data['placeholder']['en'] . '"';
				}
				$return_str .= '" />';
			}
		}
		if ($input_data['type'] == 'select') {
			$return_str .= '{{{options_' . $input_name . '}}}</select>';
		}
		if ($input_data['type'] == 'boolean') {
			$return_str .= '{{#options_' . $input_name . '}} checked="checked"{{/options_' . $input_name . '}} /> ' .
						   $input_data['label']['en'] . '</label>';
		}

		return $return_str;
	}

	public static function elementFormSubmitted($post_data) {
		if (isset($post_data['doelementadd']) || isset($post_data['doelementedit'])) {
			return true;
		} else {
			return false;
		}
	}

	public static function handleElementFormPOST($post_data,&$cash_admin) {
		global $admin_primary_cash_request;
		if (AdminHelper::elementFormSubmitted($post_data)) {

			// first create the options array
			$options_array = array();
			// now populate it from the POST data, fixing booleans
			$app_json = AdminHelper::getElementAppJSON($post_data['element_type']);
			if ($app_json) {
				foreach ($app_json['options'] as $section_name => $details) {
					foreach ($details['data'] as $data => $values) {
						if ($values['type'] == 'boolean') {
							if (isset($post_data[$data])) {
								$options_array[$data] = 1;
							} else {
								$options_array[$data] = 0;
							}
						} else {
							$options_array[$data] = $post_data[$data];
						}
					}
				}
			}

			if (isset($post_data['doelementadd'])) {
				// Adding a new element:
				$cash_admin->setCurrentElementState('add');
				$admin_primary_cash_request->processRequest(
					array(
						'cash_request_type' => 'element', 
						'cash_action' => 'addelement',
						'name' => $post_data['element_name'],
						'type' => $post_data['element_type'],
						'options_data' => $options_array,
						'user_id' => AdminHelper::getPersistentData('cash_effective_user')
					)
				);
				if ($admin_primary_cash_request->response['status_uid'] == 'element_addelement_200') {

					$current_campaign = false;
					if ($post_data['in_campaign']) {
						$current_campaign = $post_data['in_campaign'];
					} else {
						$current_campaign = AdminHelper::getPersistentData('current_campaign');
					}

					if ($current_campaign) {
						$cash_admin->requestAndStore(	
							array(
								'cash_request_type' => 'element', 
								'cash_action' => 'addelementtocampaign',
								'campaign_id' => $current_campaign,
								'element_id' => $admin_primary_cash_request->response['payload']
							)
						);
						// handle differently for AJAX and non-AJAX
						if ($cash_admin->page_data['data_only']) {
							AdminHelper::formSuccess('Success. New element added.','/');
						} else {
							$cash_admin->setCurrentElement($admin_primary_cash_request->response['payload']);
						}
					} else {
						// handle differently for AJAX and non-AJAX
						if ($cash_admin->page_data['data_only']) {
							AdminHelper::formSuccess('Success. New element added.','/elements/edit/' . $admin_primary_cash_request->response['payload']);
						} else {
							$cash_admin->setCurrentElement($admin_primary_cash_request->response['payload']);
						}
					}					
				} else {
					// handle differently for AJAX and non-AJAX
					if ($cash_admin->page_data['data_only']) {
						AdminHelper::formFailure('Error. Something just didn\'t work right.','/elements/add/' . $post_data['element_type']);
					} else {
						$cash_admin->setErrorState('element_add_failure');
					}
				}
			} elseif (isset($post_data['doelementedit'])) {
				// Editing an existing element:
				$cash_admin->setCurrentElementState('edit');
				$admin_primary_cash_request->processRequest(
					array(
						'cash_request_type' => 'element', 
						'cash_action' => 'editelement',
						'id' => $post_data['element_id'],
						'name' => $post_data['element_name'],
						'options_data' => $options_array
					)
				);
				if ($admin_primary_cash_request->response['status_uid'] == 'element_editelement_200') {
					// handle differently for AJAX and non-AJAX
					if ($cash_admin->page_data['data_only']) {
						// AJAX
						AdminHelper::formSuccess('Success. Edited.','/elements/edit/' . $post_data['element_id']);
					} else {
						// non-AJAX
						$cash_admin->setCurrentElement($post_data['element_id']);
					}
				} else {
					// handle differently for AJAX and non-AJAX
					if ($cash_admin->page_data['data_only']) {
						// AJAX
						AdminHelper::formFailure('Error. Something just didn\'t work right.','/elements/edit/' . $post_data['element_id']);
					} else {
						// non-AJAX
						$cash_admin->setErrorState('element_edit_failure');
					}
				}
			}

			AdminHelper::setBasicElementFormData($cash_admin);
		}
	}

	public static function setBasicElementFormData(&$cash_admin) {
		$current_element = $cash_admin->getCurrentElement();
		if ($current_element) {
			// Current element found, so fill in the 'edit' form:
			$cash_admin->page_data['element_id'] = $current_element['id'];
			$cash_admin->page_data['element_name'] = $current_element['name'];
		}
	}

	/**
	 * Finds settings matching a specified scope and echoes them out formatted
	 * for a dropdown box in a form
	 *
	 */public static function echoTemplateOptions($type='page',$selected=null,$return=true) {
		global $cash_admin;

				$templates_array = array(
					array(
						'id' => -2,
						'name' => 'Use dark theme'
					),
					array(
						'id' => -1,
						'name' => 'Use light theme'
					),
					array(
						'id' => 0,
						'name' => 'Custom'
					)
				);
				if ($selected === null) {
					$selected = -2;
				} else {
					if ($selected > 0) {
						$templates_array[2]['id'] = $selected;
					}
				}

				$all_templates = '';
				foreach ($templates_array as $template) {
					$echo_selected = '';
					if ($template['id'] == $selected) { $echo_selected = ' selected="selected"'; }
					$all_templates .= '<option value="' . $template['id'] . '"' . $echo_selected . '>' . $template['name'] . '</option>';
				}
				if ($return) {
					return $all_templates;
				} else {
					echo $all_templates;
				}
		
	}

	/**********************************************
	 *
	 * SIMPLE DATA FORMATTING
	 *
	 *********************************************/

	public static function createdModifiedFromRow($row,$top=false) {
		$addtoclass = '';
		if ($top) { $addtoclass = '_top'; }
		$markup = '<div class="smalltext fadedtext created_mod' . $addtoclass . '">Created: ' . date('M jS, Y',$row['creation_date']); 
		if ($row['modification_date']) { 
			$markup .= ' (Modified: ' . date('F jS, Y',$row['modification_date']) . ')'; 
		}
		$markup .= '</div>';
		return $markup;
	}

	/**
	 * Spit out human readable byte size
	 * swiped from comments: http://us2.php.net/manual/en/function.memory-get-usage.php
	 *
	 * @param $bytes (int)
	 * @param $precision (int)
	 * @return string
	 */function bytesToSize($bytes, $precision = 2) {
	    $unit = array('B','KB','MB','GB','TB','PB','EB');
	    if (!$bytes) {
	    	return 'unknown';
	    }
		return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision) . ' ' . $unit[$i];
	}

	/**********************************************
	 *
	 * MISCELLANEOUS
	 *
	 *********************************************/

	public static function parseMetaData($post_data) {
		$metadata_and_tags = array(
			'metadata_details' => array(),
			'tags_details' => array()
		);
		foreach ($post_data as $key => $value) {
			if (substr($key,0,3) == 'tag' && $value !== '') {
				$metadata_and_tags['tags_details'][] = $value;
				$metadata_and_tags['total_tags'] = count($metadata_and_tags['tags_details']);
			}
			if (substr($key,0,11) == 'metadatakey' && $value !== '') {
				$metadatavalue = $_POST[str_replace('metadatakey','metadatavalue',$key)];
				if ($metadatavalue) {
					$metadata_and_tags['metadata_details'][$value] = $metadatavalue;
				}
			}
		}
		return $metadata_and_tags;
	}

	/**
	 * Performs a sessionGet() CASH Request for the specified variable
	 *
	 */public static function getPersistentData($var) {
		global $admin_primary_cash_request;
		$result = $admin_primary_cash_request->sessionGet($var);
		return $result;
	}

	public static function getActivity($current_userdata=false) {
		global $admin_primary_cash_request, $cash_admin;
		$session_news = $admin_primary_cash_request->sessionGet('admin_newsfeed');
		if (!$session_news) {
			/*
			$tumblr_seed = new TumblrSeed();
			$tumblr_request = $tumblr_seed->getTumblrFeed('blog.cashmusic.org',0,'platformnews');

			$dashboard_news_img = null;
			$dashboard_news = "<p>News could not be read. So let's say no news is good news.</p>";
			$doc = new DOMDocument();
			@$doc->loadHTML($tumblr_request[0]->{'regular-body'});
			$imgs = $doc->getElementsByTagName('img');
			if ($imgs->length) {
				$dashboard_news_img = $imgs->item(0)->getAttribute('src');
			}
			$ps = $doc->getElementsByTagName('p');
			foreach ($ps as $p) {
				if ($p->nodeValue) {
					$dashboard_news = '<p><b><i>' . $tumblr_request[0]->{$tumblr_request[0]->type . '-title'} . ':</i></b> ' . 
						$p->nodeValue . ' <a href="' . $tumblr_request[0]->{'url-with-slug'} . '" class="usecolor1" target="_blank">' . 'Read more.</a></p>';
					break;
				}
			}


			if ($tumblr_request[0]->{'unix-timestamp'} > (time() - 604800)) {
				// store all that tumblr junk in our array and move on
				$session_news = array(
					'cash_news_date'    => $tumblr_request[0]->{'unix-timestamp'},
					'cash_news_content' => $dashboard_news,
					'cash_news_img'     => $dashboard_news_img
				);
			} else {
				$session_news = array(
					'cash_news_date'    => false,
					'cash_news_content' => false,
					'cash_news_img'     => false
				);
			}
			*/

			$last_login = 0;
			if (is_array($current_userdata)) {
				if (array_key_exists('last_login', $current_userdata)) {
					$last_login = $current_userdata['last_login'];
				} 
			}

			// get recent activity
			$activity_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'getrecentactivity',
					'user_id' => $cash_admin->effective_user_id,
					'since_date' => $last_login
				)
			);
			$activity = $activity_request->response['payload'];
			$session_news['activity'] = $activity;

			// store it in the session for later
			$admin_primary_cash_request->sessionSet('admin_newsfeed',$session_news);
		}
		return $session_news;
	}

	/**********************************************
	 *
	 * FORM HELPER FUNCTIONS
	 *
	 *********************************************/

	public static function controllerRedirect($location) {
		if (isset($_REQUEST['data_only'])) {
			echo json_encode(
				array(
					'doredirect'  => true,
					'location'    => ADMIN_WWW_BASE_PATH . $location
				)
			);
			exit();
		} else {
			header('Location: ' . ADMIN_WWW_BASE_PATH . $location);
		}
	}

	public static function formSuccess($message=false,$location=false) {
		if (!$location) {
			$location = REQUESTED_ROUTE;
		}
		if (isset($_REQUEST['forceroute'])) {
			// we force a route using JS for certain lightboxed forms — really used 
			// as an override that should take precenece over the standard $location
			$location = $_REQUEST['forceroute'];
		}
		if (isset($_REQUEST['data_only'])) {
			echo json_encode(
				array(
					'doredirect'  => true,
					'location'    => ADMIN_WWW_BASE_PATH . $location,
					'showmessage' => $message
				)
			);
			exit();
		} else {
			if ($location == REQUESTED_ROUTE) { 
				if ($message) {
					global $cash_admin;
					$cash_admin->page_data['page_message'] = $message;
				}
			} else {
				header('Location: ' . ADMIN_WWW_BASE_PATH . $location);
			}
		}
	}

	public static function formFailure($error_message,$location=false) {
		if (!$location) {
			$location = REQUESTED_ROUTE;
		}
		if (isset($_REQUEST['forceroute'])) {
			// we force a route using JS for certain lightboxed forms — really used 
			// as an override that should take precenece over the standard $location
			$location = $_REQUEST['forceroute'];
		}
		if (isset($_REQUEST['data_only'])) {
			echo json_encode(
				array(
					'doredirect'  => true,
					'location'    => ADMIN_WWW_BASE_PATH . $location,
					'showerror'   => $error_message
				)
			);
			exit();
		} else {
			global $cash_admin;
			$cash_admin->page_data['error_message'] = $error_message;
		}
	}

	public static function drawCountryCodeUL($selected='USA') {
		$all_codes = array(
			'USA','Brazil','Canada','Czech Republic','France','Germany','Italy','Japan','United Kingdom',
			'',
			'Afghanistan',
			'Albania',
			'Algeria',
			'Andorra',
			'Angola',
			'Antigua &amp; Deps',
			'Argentina',
			'Armenia',
			'Australia',
			'Austria',
			'Azerbaijan',
			'Bahamas',
			'Bahrain',
			'Bangladesh',
			'Barbados',
			'Belarus',
			'Belgium',
			'Belize',
			'Benin',
			'Bhutan',
			'Bolivia',
			'Bosnia Herzegovina',
			'Botswana',
			'Brazil',
			'Brunei',
			'Bulgaria',
			'Burkina',
			'Burundi',
			'Cambodia',
			'Cameroon',
			'Canada',
			'Cape Verde',
			'Central African Rep',
			'Chad',
			'Chile',
			'China',
			'Colombia',
			'Comoros',
			'Congo',
			'Costa Rica',
			'Croatia',
			'Cuba',
			'Cyprus',
			'Czech Republic',
			'Denmark',
			'Djibouti',
			'Dominica',
			'Dominican Republic',
			'East Timor',
			'Ecuador',
			'Egypt',
			'El Salvador',
			'Equatorial Guinea',
			'Eritrea',
			'Estonia',
			'Ethiopia',
			'Fiji',
			'Finland',
			'France',
			'Gabon',
			'Gambia',
			'Georgia',
			'Germany',
			'Ghana',
			'Greece',
			'Grenada',
			'Guatemala',
			'Guinea',
			'Guinea-Bissau',
			'Guyana',
			'Haiti',
			'Honduras',
			'Hungary',
			'Iceland',
			'India',
			'Indonesia',
			'Iran',
			'Iraq',
			'Ireland',
			'Israel',
			'Italy',
			'Ivory Coast',
			'Jamaica',
			'Japan',
			'Jordan',
			'Kazakhstan',
			'Kenya',
			'Kiribati',
			'Korea North',
			'Korea South',
			'Kosovo',
			'Kuwait',
			'Kyrgyzstan',
			'Laos',
			'Latveria',
			'Latvia',
			'Lebanon',
			'Lesotho',
			'Liberia',
			'Libya',
			'Liechtenstein',
			'Lithuania',
			'Luxembourg',
			'Macedonia',
			'Madagascar',
			'Malawi',
			'Malaysia',
			'Maldives',
			'Mali',
			'Malta',
			'Marshall Islands',
			'Mauritania',
			'Mauritius',
			'Mexico',
			'Micronesia',
			'Moldova',
			'Monaco',
			'Mongolia',
			'Montenegro',
			'Morocco',
			'Mozambique',
			'Myanmar, (Burma)',
			'Namibia',
			'Nauru',
			'Nepal',
			'Netherlands',
			'New Zealand',
			'Nicaragua',
			'Niger',
			'Nigeria',
			'Norway',
			'Oman',
			'Pakistan',
			'Palau',
			'Panama',
			'Papua New Guinea',
			'Paraguay',
			'Peru',
			'Philippines',
			'Poland',
			'Portugal',
			'Qatar',
			'Romania',
			'Russian Federation',
			'Rwanda',
			'St Kitts &amp; Nevis',
			'St Lucia',
			'Saint Vincent &amp; the Grenadines',
			'Samoa',
			'San Marino',
			'Sao Tome &amp; Principe',
			'Saudi Arabia',
			'Senegal',
			'Serbia',
			'Seychelles',
			'Sierra Leone',
			'Singapore',
			'Slovakia',
			'Slovenia',
			'Solomon Islands',
			'Somalia',
			'South Africa',
			'Spain',
			'Sri Lanka',
			'Sudan',
			'Suriname',
			'Swaziland',
			'Sweden',
			'Switzerland',
			'Syria',
			'Taiwan',
			'Tajikistan',
			'Tanzania',
			'Thailand',
			'Togo',
			'Tonga',
			'Trinidad &amp; Tobago',
			'Tunisia',
			'Turkey',
			'Turkmenistan',
			'Tuvalu',
			'Uganda',
			'Ukraine',
			'United Arab Emirates',
			'United Kingdom',
			'United States',
			'Uruguay',
			'Uzbekistan',
			'Vanuatu',
			'Vatican City',
			'Venezuela',
			'Vietnam',
			'Yemen',
			'Zambia',
			'Zimbabwe'
		);
		$all_options = '';
		$has_selected = false;
		foreach ($all_codes as $code) {
			$all_options .= '<option value="' . $code . '"';
			if (!$has_selected && $code == $selected) {
				$all_options .= ' selected="selected"';
				$has_selected = true;
			}
			$all_options .= '>' . $code . '</option>';
		}
		return $all_options;
	}

	public static function drawTimeZones($selected='US/Pacific') {
		$all_zones = array(
			'US/Alaska',
			'US/Arizona',
			'US/Central',
			'US/East-Indiana',
			'US/Eastern',
			'US/Hawaii',
			'US/Mountain',
			'US/Pacific',
			'US/Samoa',
			'Africa/Cairo',
			'Africa/Casablanca',
			'Africa/Harare',
			'Africa/Monrovia',
			'Africa/Nairobi',
			'America/Bogota',
			'America/Buenos_Aires',
			'America/Caracas',
			'America/Chihuahua',
			'America/La_Paz',
			'America/Lima',
			'America/Mazatlan',
			'America/Mexico_City',
			'America/Monterrey',
			'America/Santiago',
			'America/Tijuana',
			'Asia/Almaty',
			'Asia/Baghdad',
			'Asia/Baku',
			'Asia/Bangkok',
			'Asia/Chongqing',
			'Asia/Dhaka',
			'Asia/Hong_Kong',
			'Asia/Irkutsk',
			'Asia/Jakarta',
			'Asia/Jerusalem',
			'Asia/Kabul',
			'Asia/Kamchatka',
			'Asia/Karachi',
			'Asia/Kathmandu',
			'Asia/Kolkata',
			'Asia/Krasnoyarsk',
			'Asia/Kuala_Lumpur',
			'Asia/Kuwait',
			'Asia/Magadan',
			'Asia/Muscat',
			'Asia/Novosibirsk',
			'Asia/Riyadh',
			'Asia/Seoul',
			'Asia/Singapore',
			'Asia/Taipei',
			'Asia/Tashkent',
			'Asia/Tbilisi',
			'Asia/Tehran',
			'Asia/Tokyo',
			'Asia/Ulaanbaatar',
			'Asia/Urumqi',
			'Asia/Vladivostok',
			'Asia/Yakutsk',
			'Asia/Yekaterinburg',
			'Asia/Yerevan',
			'Atlantic/Azores',
			'Atlantic/Cape_Verde',
			'Atlantic/Stanley',
			'Australia/Adelaide',
			'Australia/Brisbane',
			'Australia/Canberra',
			'Australia/Darwin',
			'Australia/Hobart',
			'Australia/Melbourne',
			'Australia/Perth',
			'Australia/Sydney',
			'Canada/Atlantic',
			'Canada/Newfoundland',
			'Canada/Saskatchewan',
			'Europe/Amsterdam',
			'Europe/Athens',
			'Europe/Belgrade',
			'Europe/Berlin',
			'Europe/Bratislava',
			'Europe/Brussels',
			'Europe/Bucharest',
			'Europe/Budapest',
			'Europe/Copenhagen',
			'Europe/Dublin',
			'Europe/Helsinki',
			'Europe/Istanbul',
			'Europe/Kiev',
			'Europe/Lisbon',
			'Europe/Ljubljana',
			'Europe/London',
			'Europe/Madrid',
			'Europe/Minsk',
			'Europe/Moscow',
			'Europe/Paris',
			'Europe/Prague',
			'Europe/Riga',
			'Europe/Rome',
			'Europe/Sarajevo',
			'Europe/Skopje',
			'Europe/Sofia',
			'Europe/Stockholm',
			'Europe/Tallinn',
			'Europe/Vienna',
			'Europe/Vilnius',
			'Europe/Volgograd',
			'Europe/Warsaw',
			'Europe/Zagreb',
			'Greenland',
			'Pacific/Auckland',
			'Pacific/Fiji',
			'Pacific/Guam',
			'Pacific/Midway',
			'Pacific/Port_Moresby'
		);
		$all_options = '';
		$has_selected = false;
		foreach ($all_zones as $zone) {
			$all_options .= '<option value="' . $zone . '"';
			if (!$has_selected && $zone == $selected) {
				$all_options .= ' selected="selected"';
				$has_selected = true;
			}
			$all_options .= '>' . $zone . '</option>';
		}
		return $all_options;
	}

	/**
	 * Tell it what you need. It makes dropdowns. It's a dropdown robot travelling
	 * at the speed of light — it'll make a supersonic nerd of you. Don't stop it.
	 *
	 * @return array
	 */public static function echoFormOptions($base_type,$selected=0,$range=false,$return=false) {
		global $admin_primary_cash_request;
		$available_options = false;

		if (is_array($base_type)) {
			$available_options = array();
			foreach ($base_type as $key => $value) {
				$available_options[] = array(
					'id' => $key,
					'display' => $value
				);
			}
			$display_information = 'display';
			$all_options = '';
		} else {

			if (substr($base_type,0,7) == 'connect') {
				$scope = explode('_',$base_type);
				return AdminHelper::echoConnectionsOptions($scope[1],$selected,true);
			}

			$all_options = '<option value="0">None</option>';
			switch ($base_type) {
				case 'assets':
					$plant_name = 'asset';
					$action_name = 'getassetsforuser';
					$display_information = 'title';
					if ($range) {
						if (!in_array($selected,$range)) {
							$range[] = $selected;
						}
					}
					break;
				case 'people_lists':
					$plant_name = 'people';
					$action_name = 'getlistsforuser';
					$display_information = 'name';
					break;
				case 'venues':
				case 'calendar_venues':
					$plant_name = 'calendar';
					$action_name = 'getallvenues';
					$display_information = 'name';
					break;	
				case 'items':
				case 'commerce_items':
					$plant_name = 'commerce';
					$action_name = 'getitemsforuser';
					$display_information = 'name';
					break;
			}
			global $admin_primary_cash_request;
			$admin_primary_cash_request->processRequest(
				array(
					'cash_request_type' => $plant_name, 
					'cash_action' => $action_name,
					'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
					'parent_id' => 0
				)
			);
			if (is_array($admin_primary_cash_request->response['payload']) && ($admin_primary_cash_request->response['status_code'] == 200)) {
				$available_options = $admin_primary_cash_request->response['payload'];
			}
		}

		if (is_array($available_options)) {
			foreach ($available_options as $item) {
				$doloop = true;
				if ($range) {
					if (!in_array($item['id'],$range)) {
						$doloop = false;
					}
				}
				if ($doloop) {
					$selected_string = '';
					if ($item['id'] === $selected) { 
						$selected_string = ' selected="selected"';
					}
					$all_options .= '<option value="' . $item['id'] . '"' . $selected_string . '>' . $item[$display_information] . '</option>';
				}
			}
		} else {
			$all_options = false;
		}

		if ($return) {
			return $all_options;
		} else {
			echo $all_options;
		}
	}

	/**
	 * MONIES
	 *
	 * @return array
	 */public static function echoCurrencyOptions($selected='USD') {
		$currencies = CASHSystem::getCurrencySymbol('all');
		$all_options = '';
		$has_selected = false;
		foreach ($currencies as $currency => $symbol) {
			$all_options .= '<option value="' . $currency . '"';
			if (!$has_selected && $currency == $selected) {
				$all_options .= ' selected="selected"';
				$has_selected = true;
			}
			$all_options .= '>' . $currency . ' / ' . $symbol . '</option>';
		}
		return $all_options;
	}
	
} // END class 
?>