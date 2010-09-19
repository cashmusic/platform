<?php
/*
SETTINGS:
*/
$hostname = "";
$username = "";
$password = "";
$database = "";
// connect
$dblink = mysql_connect($hostname,$username,$password) or die("Unable to connect to database");
mysql_select_db($database, $dblink) or die("Unable to select database");

/*
HELPER FUNCTIONS:
*/
function get_current_ip() {
    if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
		if ($_SERVER["HTTP_CLIENT_IP"]) {
			$proxy = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$proxy = $_SERVER["REMOTE_ADDR"];
		}
		$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} else {
		if ($_SERVER["HTTP_CLIENT_IP"]) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
	}
	if (!isset($proxy)) {
		$proxy = 0;
	}
	$ipandproxy = array($ip,$proxy);	
	return $ipandproxy;
}

function generateHash($str) {
	return substr(sha1(count_chars($str).$str.'zbo'),0,32);
}

function random_str_gen($length) {
	$random= "";
	srand((double)microtime()*1000000);
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
	for($i = 0; $i < $length; $i++) { 
		$random .= substr($chars,(rand()%(strlen($chars))),1);
	}
	return $random;
}

function queryAndReturnAssoc($query) {
	global $dblink;
	$result = mysql_query($query,$dblink);
	if ($result) {
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			return $row;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function queryAndReturnMultiAssoc($query) {
	global $dblink;
	$result = mysql_query($query,$dblink);
	if ($result) {
		if (mysql_num_rows($result)) {
			$returnarray = array();
			while ($row = mysql_fetch_assoc($result)) {
				$returnarray[] = $row;
			}
			return $returnarray;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function sendEmail($subject,$fromaddress,$toaddress,$message_text,$message_title) {
	//create a boundary string. It must be unique 
	//so we use the MD5 algorithm to generate a random hash
	$random_hash = md5(date('r', time())); 
	//define the headers we want passed. Note that they are separated with \r\n
	$headers = "From: $fromaddress\r\nReply-To: $fromaddress";
	//add boundary string and mime type specification
	$headers .= "\r\nContent-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\""; 
	//define the body of the message.
	$message = "--PHP-alt-$random_hash\n";
	$message .= "Content-Type: text/plain; charset=\"iso-8859-1\"\n";
	$message .= "Content-Transfer-Encoding: 7bit\n\n";
	$message .= "$message_title\n\n";
	$message .= $message_text;
	$message .= "\n--PHP-alt-$random_hash\n"; 
	$message .= "Content-Type: text/html; charset=\"iso-8859-1\"\n";
	$message .= "Content-Transfer-Encoding: 7bit\n\n";
	$message .= "<p style=\"font-family:helvetica,arial,sans-serif;font-size:2em;margin-bottom:0.75em;margin-top:0;\"><img src=\"http://my.cashmusic.org/images/cashlogo.gif\" width=\"36\" height=\"36\" style=\"position:relative;top:9px;margin-right:8px;\" alt=\"CASH Music\" />$message_title</p>\n";
	$message .= "<p style=\"font-family:helvetica,arial,sans-serif;padding-left:44px;\">";
	$message .= str_replace("\n","<br />\n",preg_replace('/(http:\/\/(\S*))/', '<a href="\1">\1</a>', $message_text));
	$message .= "\n--PHP-alt-$random_hash--\n";
	//send the email
	$mail_sent = @mail($toaddress,$subject,$message,$headers);
	return $mail_sent;
}

/*
DATA ACCESS FUNCTIONS
*/
function addToEmail_list($email_address) {
	global $dblink;
	$query = "INSERT INTO email_list (email_address) VALUES ('$email_address')";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function addToQuestions($name,$email_address,$question,$relevant_to) {
	global $dblink;
	$query = "INSERT INTO questions (assoc_artist,questioner_name,questioner_email,question) VALUES ($relevant_to,'$name','$email_address','$question')";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function questionStats($assoc_artist) {
	global $dblink;
	$query= "SELECT * FROM questions WHERE assoc_artist=$assoc_artist";
	$result = mysql_query($query,$dblink);
	// returns number of new (unapproved/unignored) questions if true
	if (mysql_num_rows($result)) { 
		$query= "SELECT * FROM questions WHERE assoc_artist=$assoc_artist AND status=0";
		$result = mysql_query($query,$dblink);
		return mysql_num_rows($result);
	} else {
		return false;
	}
}

function getAnsweredQuestions($assoc_artist) {
	global $dblink;
	$query= "SELECT questioner_name,date_posted,question,answer FROM questions WHERE assoc_artist=$assoc_artist AND status>1 ORDER BY date_posted DESC";
	$result = mysql_query($query,$dblink);
	$questionsstring = "";
	// return true if address is present
	if (mysql_num_rows($result)) { 
		while ($row = mysql_fetch_assoc($result)) {
			$shortdate = date('M d',strtotime($row['date_posted']));
			$questionsstring .= "<p><b>$shortdate, {$row['questioner_name']} asked:</b><br /><i>{$row['question']}</i></p><p><b>Answer:</b><br />{$row['answer']}<br /><br /></p>";
		}
		echo $questionsstring;
	} else {
		$questionsstring = "<p>Sorry, there are no answered questions yet.</p>";
		echo $questionsstring;
	}
}

function getAllAnsweredQuestions($assoc_artist) {
	global $dblink;
	$query= "SELECT id,questioner_name,date_posted,question,answer FROM questions WHERE assoc_artist=$assoc_artist AND status>1 ORDER BY date_posted DESC";
	$result = queryAndReturnMultiAssoc($query);
	return $result;
}

function getQuestion($questionId) {
	$query = "SELECT * FROM questions WHERE id = $questionId";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function addToLinkback($name,$email_address,$link_url,$title,$comments,$agreement,$relevant_to) {
	global $dblink;
	$query = "INSERT INTO linkback (name,email_address,link_url,title,comments,agreement,relevant_to) VALUES ('$name','$email_address','$link_url','$title','$comments',$agreement,'$relevant_to')";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function getLinkbacks($relevant_to,$category) {
	global $dblink;
	$query= "SELECT link_url,title,name,date_posted FROM linkback WHERE category='$category' AND relevant_to='$relevant_to' AND approved = 1 ORDER BY date_posted DESC";
	$result = mysql_query($query,$dblink);
	$linkbackstring = "";
	// return true if address is present
	if (mysql_num_rows($result)) { 
		while ($row = mysql_fetch_assoc($result)) {
			$shortdate = date('M d Y',strtotime($row['date_posted']));
			$linkbackstring .= "<p><a href=\"{$row['link_url']}\">{$row['title']}</a><br />by {$row['name']} <small class=\"ltxt\">($shortdate)</small></p>";
		}
		echo $linkbackstring;
	} else {
		$linkbackstring = "<p>Sorry, there are no approved links yet.</p>";
		echo $linkbackstring;
	}
}

function addSuggestion($name,$email_address,$add_to_list,$suggestion) {
	global $dblink;
	$query = "INSERT INTO suggestions (name,email_address,add_to_list,suggestion) VALUES ('$name','$email_address',$add_to_list,'$suggestion')";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function addExternalLink($permalink,$title,$author,$sitename,$pubdate) {
	global $dblink;
	$permalink = str_replace("'","\'",$permalink);
	$title = str_replace("'","\'",$title);
	$author = str_replace("'","\'",$author);
	$sitename = str_replace("'","\'",$sitename);
	$query = "INSERT INTO external_links (permalink,title,author,sitename,pubdate) VALUES ('$permalink','$title','$author','$sitename',$pubdate)";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function getExternalLinks() {
	global $dblink;
	$query = "SELECT permalink FROM external_links ORDER BY pubdate DESC";
	$result = mysql_query($query);
	if (mysql_num_rows($result)) { 
		$alllinks = array();
		while ($row = mysql_fetch_assoc($result)) {
			$alllinks[$row['permalink']] = $row['permalink'];
			//return $alllinks;
		}
		return $alllinks;
		mysql_free_result($result);
	}
}

function getExternalLinkCollection($statuslevel) {
	global $dblink;
	$query = "SELECT * FROM external_links WHERE status = $statuslevel ORDER BY pubdate DESC";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$alllinks = array();
		while ($row = mysql_fetch_assoc($result)) {
			$alllinks[] = $row;
		}
		return $alllinks;
		mysql_free_result($result);
	}
}

function updateExternalLink($linkid,$status) {
	global $dblink;
	$query = "UPDATE external_links SET status = $status WHERE id = $linkid";
	if (mysql_query($query,$dblink)) {
		return true;
	}
}

function processBadgeRequest($benefactor,$referer,$referer_ip) {
	global $dblink;
	if (!empty($referer)) {
		$query = "SELECT * FROM badge_requests WHERE referer = '$referer'";
		$result = mysql_query($query,$dblink);
		if (mysql_num_rows($result)) { 
			$row = mysql_fetch_assoc($result); 
			if ($row['benefactor'] == $benefactor && $row['last_ip'] != $referer_ip) {
				$newcount = $row['total_requests'] + 1;
				$query = "UPDATE badge_requests SET total_requests = $newcount, last_ip = '$referer_ip' WHERE referer = '$referer'";
				mysql_query($query,$dblink);
			} elseif ($row['benefactor'] != $benefactor) {
				$newcount = 1;
				$query = "UPDATE badge_requests SET benefactor = '$benefactor', total_requests = $newcount, last_ip = '$referer_ip' WHERE referer = '$referer'";
				mysql_query($query,$dblink);
			}
		} else {
			$query = "INSERT INTO badge_requests (referer,benefactor,last_ip) VALUES ('$referer','$benefactor','$referer_ip')";
			mysql_query($query,$dblink);
		}
	}
}

function getAllBadgeStats() {
	global $dblink;
	$query = "SELECT * FROM badge_requests ORDER BY benefactor ASC, total_requests DESC";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$full_list = "";
		$lastbenefactor = "";
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['benefactor'] != $lastbenefactor) {
				$lastbenefactor = $row['benefactor'];
				if (!empty($lastbenefactor)) {
					$full_list = $full_list .  "</p>";
				}
				$full_list = $full_list .  "<p><b>$lastbenefactor</b><br />";
			}
			if ($row['total_requests'] > 1) {
				$theplural = "s";
			} else {
				$theplural = "";
			}
			$full_list = $full_list . "{$row['referer']} ({$row['total_requests']} hit$theplural)<br />";
		}
		echo $full_list;
	}
}

function getLoginInformation($user_email,$pass_str) {
	$generated_hash = generateHash($pass_str);
	$user_email = strtolower(trim($user_email));
	global $dblink;
	$query = "SELECT l.password,l.assoc_artist,l.admin_level,a.username FROM logins l LEFT OUTER JOIN artists a ON l.assoc_artist=a.id WHERE LOWER(l.email_address)='$user_email'";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$row = mysql_fetch_assoc($result);
		if ($row['password'] == $generated_hash) {
			$login_information = array('assoc_artist' => $row['assoc_artist'],'assoc_artist_name' => $row['username'],'admin_level' => $row['admin_level']);
			return $login_information;
		} else {
			return false;
		}
	}
}

/////////////////////////////////////////////////////////
/*

SECTION:
ac_ = Attached Comments
functions all pertain to site comments

*/
/////////////////////////////////////////////////////////

function addComment($name,$email_address,$link_url,$comment,$agreement,$artist_id,$attached_to,$timestamp) {
	global $dblink;
	$query = "INSERT INTO ac_comments (name,email_address,link_url,comment,agreement,artist_id,attached_to,timestamp) VALUES ('$name','$email_address','$link_url','$comment',$agreement,$artist_id,'$attached_to',$timestamp)";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function getApprovedCommentsByAttachment($attached_to) {
	global $dblink;
	$query = "SELECT * FROM ac_comments a WHERE attached_to = '$attached_to' AND approved = 1 ORDER BY timestamp DESC";
	$result = queryAndReturnMultiAssoc($query);
	return $result;
}

/////////////////////////////////////////////////////////
/*

SECTION:
lv_ = Live
functions all pertain to live performances

*/
/////////////////////////////////////////////////////////

function lv_addVenue($name,$address1,$address2,$city,$region,$country,$postalcode,$website,$phone) {
	global $dblink;
	$name = mysql_real_escape_string($name);
	$address1 = mysql_real_escape_string($address1);
	$address2 = mysql_real_escape_string($address2);
	$city = mysql_real_escape_string($city);
	$region = mysql_real_escape_string($region);
	$country = mysql_real_escape_string($country);
	$postalcode = mysql_real_escape_string($postalcode);
	$website = mysql_real_escape_string($website);
	$phone = mysql_real_escape_string($phone);
	$query = "INSERT INTO lv_venues (name,address1,address2,city,region,country,postalcode,website,phone) VALUES ('$name','$address1','$address2','$city','$region','$country','$postalcode','$website','$phone')";
	if (mysql_query($query,$dblink)) {
		$new_venue_id = mysql_insert_id();
		return $new_venue_id;
	} else {
		//echo mysql_error();
		return false;
	}
}

function lv_addDate($date,$artist_id,$venue_id,$publish,$cancelled,$comment) {
	global $dblink;
	$comment = mysql_real_escape_string($comment);
	$query = "INSERT INTO lv_dates (date,artist_id,venue_id,publish,cancelled,comments) VALUES ($date,$artist_id,$venue_id,$publish,$cancelled,'$comment')";
	if (mysql_query($query,$dblink)) {
		$new_venue_id = mysql_insert_id();
		return $new_venue_id;
	} else {
		//echo mysql_error();
		return false;
	}
}

function lv_getAllDates($artist_id,$offset=0) {
	$offset = 86400 * $offset;
	$cutoffdate = time() - $offset;
	$query = "SELECT d.id,a.fullname as artistname,d.date,v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone,d.publish,d.cancelled,d.comments FROM lv_dates d JOIN lv_venues v ON d.venue_id = v.id JOIN artists a ON d.artist_id = a.id WHERE d.date > $cutoffdate AND a.id = $artist_id ORDER BY d.date ASC";
	$result = queryAndReturnMultiAssoc($query);
	return $result;
}

function lv_getDatesBetween($artist_id,$afterdate,$beforedate) {
	$query = "SELECT d.id,a.fullname as artistname,d.date,v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone,d.publish,d.cancelled,d.comments FROM lv_dates d JOIN lv_venues v ON d.venue_id = v.id JOIN artists a ON d.artist_id = a.id WHERE d.date > $afterdate AND d.date < $beforedate AND a.id = $artist_id ORDER BY d.date ASC";
	$result = queryAndReturnMultiAssoc($query);
	return $result;
}

function lv_getDateByArtistAndDate($artist_id,$date) {
	$query = "SELECT d.id,a.fullname as artistname,d.date,v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone,d.publish,d.cancelled,d.comments FROM lv_dates d JOIN lv_venues v ON d.venue_id = v.id JOIN artists a ON d.artist_id = a.id WHERE d.date = $date AND a.id = $artist_id ORDER BY d.date ASC";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function lv_getVenueById($venue_id) {
	$query = "SELECT * FROM lv_venues WHERE id = $venue_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

/////////////////////////////////////////////////////////
/*

SECTION:
ss_ = Secure Streams
functions all pertain to semi-secure audio streams

*/
/////////////////////////////////////////////////////////

function getSecureStreamLoginInformation($user_email,$pass_str,$which_stream) {
	$generated_hash = generateHash($pass_str);
	$user_email = strtolower(trim($user_email));
	global $dblink;
	$query = "SELECT l.password,l.first_name,l.last_name,p.allowed_logins,p.total_logins,p.date_expires,p.stream_password,p.id FROM ss_logins l LEFT OUTER JOIN ss_permissions p ON l.id=p.login_id WHERE LOWER(l.email_address)='$user_email' AND p.stream_id=$which_stream";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$row = mysql_fetch_assoc($result);
		if ($row['password'] == $generated_hash || $row['stream_password'] == $generated_hash) {
			$login_information = array('first_name' => $row['first_name'],'last_name' => $row['last_name'],'allowed_logins' => $row['allowed_logins'],'total_logins' => $row['total_logins'],'date_expires' => $row['date_expires']);
			if (($login_information['allowed_logins'] > $login_information['total_logins'] || $login_information['allowed_logins'] == -1) && ($login_information['date_expires'] > time() || $login_information['date_expires'] == -1)) {
				$tmp_total_logins = $login_information['total_logins'] + 1;
				$permissions_update_id = $row['id'];
				$current_time = time();
				$current_ip_array = get_current_ip();
				$current_ip = 'ip:' . $current_ip_array[0];
				if ($current_ip_array[1] != 0) {
					$current_ip .= ' proxy:' . $current_ip_array[1];
				}	
				$query = "UPDATE ss_permissions SET total_logins = $tmp_total_logins, last_timestamp = $current_time, last_ip = '$current_ip' WHERE id = $permissions_update_id";
				mysql_query($query,$dblink);
			} else {
				$login_information = -1;
			} 
		} else {
			$login_information = -2;
		}
	} else {
		// check for org 1 or org 4
		$user_id = ss_verifyLogin($user_email,$pass_str);
		if ($user_id) {
			$all_orgs = ss_getOrganizationsById($user_id);
			if (in_array(4,$all_orgs) || ss_getStreamAdminStatus($user_id,$which_stream)) {
				$loginInfo = ss_getLoginInfoById($user_id);
				$login_information = array('first_name' => $loginInfo['first_name'],'last_name' => $loginInfo['last_name'],'allowed_logins' => -1,'total_logins' => 0,'date_expires' => -1);
			} else {
				$login_information = 0;
			}
		} else { 
			$login_information = 0;
		}
	}
	return $login_information;
}

function addSecureStreamUser($user_email,$pass_str,$first_name,$last_name,$organization,$which_stream,$allowed_logins,$date_expires) {
	$generated_hash = generateHash($pass_str);
	$user_email = strtolower(trim($user_email));
	global $dblink;
	//$user_email = mysql_real_escape_string($user_email);
	//$first_name = mysql_real_escape_string($first_name);
	//$last_name = mysql_real_escape_string($last_name);
	//$organization = mysql_real_escape_string($organization);
	$get_id = false;
	$get_permission = false;
	$query = "SELECT id FROM ss_logins WHERE LOWER(email_address)='$user_email'";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$row = mysql_fetch_assoc($result);
		$get_id = $row['id'];
	}
	if (!$get_id) {
		$query = "INSERT INTO ss_logins (email_address,password,first_name,last_name,organization) VALUES ('$user_email','$generated_hash','$first_name','$last_name','$organization')";
		if (mysql_query($query,$dblink)) { 
			$query = "SELECT id FROM ss_logins WHERE email_address='$user_email'";
			$result = mysql_query($query,$dblink);
			if (mysql_num_rows($result)) { 
				$row = mysql_fetch_assoc($result);
				$get_id = $row['id'];
			}
		}
	}
	if ($get_id) {
		$query = "SELECT id FROM ss_permissions WHERE login_id = $get_id AND stream_id = $which_stream";
		$result = mysql_query($query,$dblink);
		if (mysql_num_rows($result)) { 
			$row = mysql_fetch_assoc($result);
			$get_permission = $row['id'];
		}
		if ($get_permission) {
			$query = "UPDATE ss_permissions SET stream_password = '$generated_hash',allowed_logins = $allowed_logins,date_expires = $date_expires WHERE id = $get_permission"; 
			if (mysql_query($query,$dblink)) { 
				return true;
			}
		} else {
			$query = "INSERT INTO ss_permissions (login_id,stream_id,allowed_logins,date_expires,stream_password) VALUES ($get_id,$which_stream,$allowed_logins,$date_expires,'$generated_hash')";
			if (mysql_query($query,$dblink)) { 
				return true;
			}
		}
	}
}

function ss_verifyLogin($user_email,$pass_str) {
	$generated_hash = generateHash($pass_str);
	$user_email = strtolower(trim($user_email));
	global $dblink;
	$query = "SELECT id FROM ss_logins WHERE LOWER(email_address)='$user_email' AND password='$generated_hash' ORDER BY id ASC";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$row = mysql_fetch_assoc($result);
		return $row['id'];
	} else {
		return false;
	}
}

function ss_getOrganizationsById($login_id) {
	global $dblink;
	$query = "SELECT organization_id FROM ss_organizations_admin WHERE login_id = $login_id";
	$result = mysql_query($query,$dblink); 
	if ($result) {
		$allorgs = array();
		while ($row = mysql_fetch_assoc($result)) {
			$allorgs[] = $row['organization_id'];
		}
		return $allorgs;
	} else {
		return false;
	}
}

function ss_getLoginInfo($user_email) {
	$user_email = strtolower(trim($user_email));
	$query = "SELECT * FROM ss_logins WHERE LOWER(email_address)='$user_email' ORDER BY id ASC";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function ss_getLoginInfoById($login_id) {
	$query = "SELECT * FROM ss_logins WHERE id=$login_id ORDER BY id ASC";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function ss_getStreamPermission($login_id,$stream_id) {
	$query = "SELECT * FROM ss_permissions WHERE login_id=$login_id AND stream_id=$stream_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function ss_getStreamAdminStatus($login_id,$stream_id) {
	// check for explicit stream admin status
	$query = "SELECT * FROM ss_streams_admin WHERE login_id=$login_id AND stream_id=$stream_id";
	$result = queryAndReturnAssoc($query);
	if($result) {
		return true;
	} else {
		// check for parent organization admin status
		$stream_info = ss_getStreamInfo($stream_id);
		$stream_organization = $stream_info['organization_id'];
		$query = "SELECT * FROM ss_organizations_admin WHERE login_id=$login_id AND organization_id=$stream_organization";
		$result = queryAndReturnAssoc($query);
		if($result) {
			return true;
		} else {
			// always allow CASH admins access to all
			$query = "SELECT * FROM ss_organizations_admin WHERE login_id=$login_id AND organization_id=1";
			$result = queryAndReturnAssoc($query);
			if($result) {
				return true;
			} else {
				return false;
			}
		}
	}
}

function ss_getStreamInfo($which_stream) {
	$query = "SELECT a.fullname as artist_name,s.title,s.primary_url,s.organization_id FROM ss_streams s JOIN artists a on s.artist_id=a.id WHERE s.id=$which_stream";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function ss_generateResetString($login_id) {
	global $dblink;
	$current_time = time();
	$random_key = random_str_gen(8);
	$query = "INSERT INTO ss_logins_resetpassword (time_requested,random_key,login_id) VALUES ($current_time,'$random_key',$login_id)"; 
	if (mysql_query($query,$dblink)) {
		$reset_string = $current_time . $random_key;
		return $reset_string;
	} else {
		return false;
	}
}

function ss_deleteResetString($reset_string) {
	global $dblink;
	$reset_requesttime = substr($reset_string,0,10);
	$reset_key = substr($reset_string,10);
	$query = "DELETE FROM ss_logins_resetpassword WHERE random_key = '$reset_key' AND time_requested = $reset_requesttime"; 
	if (mysql_query($query,$dblink)) {
		return true;
	} else {
		return false;
	}
}

function ss_getIdFromResetString($reset_string) {
	global $dblink;
	$reset_requesttime = substr($reset_string,0,10);
	$reset_key = substr($reset_string,10);
	$query = "SELECT login_id FROM ss_logins_resetpassword WHERE random_key = '$reset_key' AND time_requested = $reset_requesttime"; 
	$result = mysql_query($query,$dblink); 
	if ($result) {
		$row = mysql_fetch_assoc($result);
		return $row['login_id'];
	} else {
		return false;
	}
}

function ss_getEmailFromLoginId($login_id) {
	global $dblink;
	$query = "SELECT email_address FROM ss_logins WHERE id = $login_id"; 
	$result = mysql_query($query,$dblink); 
	if ($result) {
		$row = mysql_fetch_assoc($result);
		return $row['email_address'];
	} else {
		return false;
	}
}

function ss_resetPassword($login_id,$pass_str) {
	global $dblink;
	$generated_hash = generateHash($pass_str);
	$query = "UPDATE ss_logins SET password = '$generated_hash' WHERE id = $login_id"; 
	if (mysql_query($query,$dblink)) {
		return true;
	} else {
		return false;
	}
}

function ss_listAllPermittedStreams($login_id) {
	// for general users NOT admin
	global $dblink;
	$query = "SELECT DISTINCT s.title,s.primary_url,a.fullname as artist_name FROM ss_streams s JOIN artists a, ss_permissions p ON a.id = s.artist_id AND p.stream_id = s.id WHERE p.login_id = $login_id AND (p.date_expires > UNIX_TIMESTAMP() OR p.date_expires = -1) AND (p.total_logins < p.allowed_logins OR p.allowed_logins = -1)";
	$result = mysql_query($query,$dblink); 
	if ($result) {
		$allstreams = array();
		while ($row = mysql_fetch_assoc($result)) {
			$allstreams[] = $row;
		}
		return $allstreams;
	} else {
		return false;
	}
}

function ss_listAllOrganizationStreams($organization_id) {
	global $dblink;
	$query = "SELECT DISTINCT s.id,s.title,s.primary_url,a.fullname as artist_name FROM ss_organizations_admin oa JOIN artists a, ss_streams s ON a.id = s.artist_id AND oa.organization_id = s.organization_id WHERE oa.organization_id = $organization_id";
	$result = mysql_query($query,$dblink); 
	if ($result) {
		$allstreams = array();
		while ($row = mysql_fetch_assoc($result)) {
			$allstreams[] = $row;
		}
		return $allstreams;
	} else {
		return false;
	}
}

function ss_listAllAdminStreams($login_id) {
	global $dblink;
	$query = "SELECT DISTINCT s.id,s.title,s.primary_url,a.fullname as artist_name FROM ss_streams_admin sa JOIN artists a, ss_streams s, ss_logins l ON a.id = s.artist_id AND sa.login_id = l.id AND s.id = sa.stream_id WHERE l.id = $login_id";
	$result = mysql_query($query,$dblink); 
	if ($result) {
		$allstreams = array();
		while ($row = mysql_fetch_assoc($result)) {
			$allstreams[] = $row;
		}
		return $allstreams;
	} else {
		return false;
	}
}

function ss_listAllUserAdminStreams($login_id) {
	global $dblink;
	$allAdminStreams = array();
	$allincludedorgs = ss_getOrganizationsById($login_id);
	if ($allincludedorgs != false) {	
		if (in_array(1, $allincludedorgs)) {
			$query = "SELECT DISTINCT s.id,s.title,s.primary_url,a.fullname as artist_name FROM ss_streams s JOIN artists a ON a.id = s.artist_id";
			$result = mysql_query($query,$dblink); 
			if ($result) {
				while ($row = mysql_fetch_assoc($result)) {
					$allAdminStreams[] = $row;
				}
				return $allAdminStreams;
			} else {
				return false;
			}
		} else {
			foreach ($allincludedorgs as $org) {
				$orgStreams = ss_listAllOrganizationStreams($org);
				if (is_array($orgStreams)) {
					$allAdminStreams = array_merge($allAdminStreams,$orgStreams);
				}
			}
		}
	}
	$adminStreams = ss_listAllAdminStreams($login_id);
	if (is_array($adminStreams)) {
		$allAdminStreams = array_merge($allAdminStreams,$adminStreams);
	}
	if (count($allAdminStreams) > 0) {
		return $allAdminStreams;
	} else {
		return false;
	}
}

function ss_getTag($login_id,$stream_id) {
	global $dblink;
	$query = "SELECT tag FROM ss_tags WHERE stream_id = $stream_id AND login_id = $login_id";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$row = mysql_fetch_assoc($result);
		return $row['tag'];
	} else {
		return '';
	}
}

function ss_setTag($login_id,$stream_id,$tag_txt) {
	global $dblink;
	$query = "SELECT tag FROM ss_tags WHERE stream_id = $stream_id AND login_id = $login_id";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$query = "UPDATE ss_tags SET tag = '$tag_txt' WHERE stream_id = $stream_id AND login_id = $login_id"; 
		if (mysql_query($query,$dblink)) {
			return true;
		} else {
			return false;
		}
	} else {
		$query = "INSERT INTO ss_tags (stream_id,login_id,tag) VALUES ($stream_id,$login_id,'$tag_txt')";
		if (mysql_query($query,$dblink)) { 
			return true;
		} else {
			return false;
		}
	}
}


/////////////////////////////////////////////////////////
/*

SECTION:
dl_ = Downloads
functions all pertain to download codes

*/
/////////////////////////////////////////////////////////

/*
CHARACTER LIST:
*/

function dl_getLastUID() {
	global $dblink;
	$query= "SELECT uid FROM dl_download_codes ORDER BY id DESC LIMIT 1";
	$result = mysql_query($query,$dblink);
	$row = mysql_fetch_assoc($result);
	return $row['uid'];
}

function dl_wrapInc($current,$incBy,$total) {
	if (($current+$incBy) < ($total)) {
		$final_value = $current+$incBy;
	} else {
		$final_value = ($current-$total)+$incBy;
	}
	return $final_value;
}

function dl_wrapDec($current,$decBy,$total) {
	if (($current-$decBy) > -1) {
		$final_value = $current-$decBy;
	} else {
		$final_value = ($total+$current) - $decBy;
	}
	return $final_value;
}

function dl_verifyUniqueUID($lookup_uid) {
	global $dblink;
	$query= "SELECT uid FROM dl_downloads WHERE uid='$lookup_uid'";
	$result = mysql_query($query,$dblink);
	if ($result) {
		if (mysql_num_rows($result)) {
			return false;
		} else {
			return true;
		}
	} else {
		return true;
	}
}

function dl_addNewDownloadCode($download_id){
	global $dblink;
	$uid = dl_generateNextUID();
	$right_now = time();
	$query = "INSERT INTO dl_download_codes (uid,download_id,date_added) VALUES ('$uid',$download_id,$right_now)";
	if (mysql_query($query,$dblink)) { 
		return $uid;
	} else {
		return false;
	}
}

function dl_generateNextUID($forceLastUid=false) {
	// no 1,l,0,or o to avoid confusion...
	$num_chars = array('2','3','4','6','7','8','9');
	$txt_chars = array('5','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z');
	$all_chars = array('2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','m','n','p','q','r','s','t','u','v','w','x','y','z');
	if (!$forceLastUid) {
		$last_uid = dl_getLastUID();
	} else {
		$last_uid = $forceLastUid;
	}
	$exploded_last_uid = str_split($last_uid);
	$char_count_num = count($num_chars);
	$char_count_txt = count($txt_chars);
	$char_count_all = count($all_chars);
	if ($exploded_last_uid[2] == $all_chars[0]) {
		$exploded_last_uid[0] = $all_chars[dl_wrapInc(array_search($exploded_last_uid[0],$all_chars),1,$char_count_all)];
	}
	$exploded_last_uid[1] = $num_chars[dl_wrapDec(array_search($exploded_last_uid[1],$num_chars),3,$char_count_num)];
	if ($exploded_last_uid[3] == $txt_chars[0]) {
		$exploded_last_uid[1] = $num_chars[dl_wrapDec(array_search($exploded_last_uid[1],$num_chars),1,$char_count_num)];
	}
	$exploded_last_uid[2] = $all_chars[dl_wrapInc(array_search($exploded_last_uid[2],$all_chars),5,$char_count_all)];
	$exploded_last_uid[3] = $txt_chars[dl_wrapDec(array_search($exploded_last_uid[3],$txt_chars),rand(1,3),$char_count_txt)];
	$exploded_last_uid[4] = $all_chars[dl_wrapInc(array_search($exploded_last_uid[4],$all_chars),11,$char_count_all)];
	if ($exploded_last_uid[0] == $all_chars[0]) {
		$exploded_last_uid[5] = $all_chars[dl_wrapDec(array_search($exploded_last_uid[5],$all_chars),1,$char_count_all)];
	}
	$exploded_last_uid[6] = $num_chars[dl_wrapDec(array_search($exploded_last_uid[6],$num_chars),3,$char_count_num)];
	$exploded_last_uid[7] = $txt_chars[dl_wrapInc(array_search($exploded_last_uid[7],$txt_chars),rand(1,3),$char_count_txt)];
	$next_uid = implode('',$exploded_last_uid);
	while (!dl_verifyUniqueUID($next_uid)) {
		$next_uid = dl_generateNextUID($next_uid);
	}
	return $next_uid;
}

function dl_getDownloadInformation($download_id) {
	$query = "SELECT * FROM dl_downloads WHERE id=$download_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function dl_getCodeInformation($uid,$download_id) {
	$query = "SELECT * FROM dl_download_codes WHERE uid='$uid' AND download_id=$download_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function dl_parseCode($code) {
	$idfromcode = substr($code,0,(strlen($code)-8));
	$uidfromcode = substr($code,-8);
	$identifier = array('uid' => $uidfromcode, 'id' => $idfromcode);
	return $identifier;
}

function dl_verifyCode($code,$email=false) {
	$identifier = dl_parseCode($code);
	$result = dl_getCodeInformation($identifier['uid'],$identifier['id']);
	if ($result !== false) {
		if (!$email) {
			if ($result['expired'] == 1) {
				return false;
			} else {
				return true;
			}
		} else {
			// verified email stuff...
		}
	} else {
		return false;
	}
}

function dl_setEmailVerification($code,$emailaddress) {
	global $dblink;
	$emailaddress = mysql_real_escape_string(stripslashes(strtolower($emailaddress)));
	$verificationcode = time();
	$query = "UPDATE dl_download_codes SET associated_email = '$emailaddress', verification = $verificationcode, date_claimed = $verificationcode WHERE uid = '$code'";
	if (mysql_query($query,$dblink)) { 
		return $verificationcode;
	} else {
		return false;
	}
}

function dl_doEmailVerification($emailaddress,$verificationcode) {
	global $dblink;
	$emailaddress = mysql_real_escape_string(stripslashes(strtolower($emailaddress)));
	$query = "SELECT * FROM dl_download_codes WHERE associated_email='$emailaddress' AND verification=$verificationcode";
	$result = queryAndReturnAssoc($query);
	if ($result !== false) { 
		$uid = $result['uid'];
		$query = "UPDATE dl_download_codes SET verification = 1 WHERE uid = '$uid'";
		if (mysql_query($query,$dblink)) { 
			$returnarray = array('uid' => $uid,'id' => $result['download_id']);
			return $returnarray;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function dl_getAllFilesForDownload($download_id) {
	$query = "SELECT * from dl_files WHERE download_id=$download_id";
	$result = queryAndReturnMultiAssoc($query);
	return $result;
}

function dl_getFileDetails($file_id) {
	$query = "SELECT * from dl_files WHERE id=$file_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function countDownloads($download_id,$uid,$expireCode=false) {
	global $dblink;
	$downloadinfo = dl_getCodeInformation($uid,$download_id);
	$newcount = $downloadinfo['total_downloads'] + 1;
	if ($expireCode) {
		$currenttime = time();
		$query = "UPDATE dl_download_codes SET total_downloads=$newcount, date_claimed=$currenttime, expired=1 WHERE uid = '$uid' and download_id=$download_id";
		@mysql_query($query,$dblink);
	} else {
		$query = "UPDATE dl_download_codes SET total_downloads=$newcount WHERE uid = '$uid' and download_id=$download_id";
		@mysql_query($query,$dblink);
	}
}

/////////////////////////////////////////////////////////
/*

SECTION:
el_ = Email Lists
functions all pertain to email lists
(borrowed HEAVILY from downloads...look to merge)

*/
/////////////////////////////////////////////////////////

function el_getAddressInformation($email,$list_id) {
	$email = mysql_real_escape_string(stripslashes(strtolower($email)));
	$query = "SELECT * FROM el_addresses WHERE email_address='$email' AND list_id=$list_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function el_emailIsVerified($email,$list_id) {
	$email_information = el_getAddressInformation($email,$list_id);
	if (!$email_information) {
		$result = -1; 
	} else {
		$result = $email_information['verified'];
	}
	return $result;
}

function el_addEmailAddress($email,$list_id) {
	global $dblink;
	$email = mysql_real_escape_string(stripslashes(strtolower($email)));
	$creation_date = time();
	$query = "INSERT INTO el_addresses (email_address,list_id,creation_date) VALUES ('$email',$list_id,$creation_date)";
	if (mysql_query($query,$dblink)) { 
		return true;
	} else {
		return false;
	}
}

function el_setEmailVerification($email,$list_id) {
	global $dblink;
	$email = mysql_real_escape_string(stripslashes(strtolower($email)));
	$verification_code = time();
	$query = "UPDATE el_addresses SET verification_code = '$verification_code' WHERE email_address='$email' AND list_id=$list_id";
	if (mysql_query($query,$dblink)) { 
		return $verification_code;
	} else {
		return false;
	}
}

function el_doEmailVerification($email,$list_id,$verification_code) {
	global $dblink;
	$alreadyverified = el_emailIsVerified($email,$list_id);
	if ($alreadyverified == 1) {
		$addressInfo = el_getAddressInformation($email,$list_id);
		return $addressInfo['id'];
	} else {
		$email = mysql_real_escape_string(stripslashes(strtolower($email)));
		$query = "SELECT * FROM el_addresses WHERE email_address='$email' AND verification_code='$verification_code' AND list_id=$list_id";
		$result = queryAndReturnAssoc($query);
		if ($result !== false) { 
			$id = $result['id'];
			$query = "UPDATE el_addresses SET verified = 1 WHERE id = $id";
			if (mysql_query($query,$dblink)) { 
				return $id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

/////////////////////////////////////////////////////////
/*

SECTION:
ul_ = Uploads
functions all pertain to uploads to S3 (public)

NOTE:
the uploadcategory table doesn't exist, but will
ultimately contain artist id, category name, etc.
this will be used to organize uploads by project or
artist...

*/
/////////////////////////////////////////////////////////

function ul_newUpload($uploadcategory_id,$title,$description,$uploadedby_name,$uploadedby_email,$key_prefix,$file_extension) {
	global $dblink;
	$title = mysql_real_escape_string(stripslashes(strtolower($title)));
	$description = mysql_real_escape_string(stripslashes(strtolower($description)));
	$uploadedby_name = mysql_real_escape_string(stripslashes(strtolower($uploadedby_name)));
	$uploadedby_email = mysql_real_escape_string(stripslashes(strtolower($uploadedby_email)));
	$creation_date = time();
	$query = "INSERT INTO ul_uploads (uploadcategory_id,title,description,uploadedby_name,uploadedby_email,upload_time) VALUES ($uploadcategory_id,'$title','$description','$uploadedby_name','$uploadedby_email',$creation_date)";
		
	if (mysql_query($query,$dblink)) { 
		$upload_id = mysql_insert_id();
		$finalkey = $key_prefix.$upload_id.'_'.$creation_date.'.'.$file_extension;
		$query = "UPDATE ul_uploads SET s3_key = '$finalkey' WHERE id=$upload_id";
		if (mysql_query($query,$dblink)) { 
			$returnarray = array (
			    "id"  => $upload_id,
			    "upload_time" => $creation_date,
			    "key" => $finalkey
			);
			return $returnarray;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function ul_approveUpload($upload_id) {
	global $dblink;
	$query = "UPDATE ul_uploads SET approved = 1 WHERE id=$upload_id";
	if (mysql_query($query,$dblink)) { 
		return true;
	} else {
		return false;
	}
}

/////////////////////////////////////////////////////////
/*

SECTION:
pg_ = Photo Galleries
functions all pertain to photo collections

*/
/////////////////////////////////////////////////////////

function pg_addPhoto($gallery_id,$code='',$email='',$name='',$comment='') {
	global $dblink;
	$code = mysql_real_escape_string(stripslashes(strtolower($code)));
	$email = mysql_real_escape_string(stripslashes(strtolower($email)));
	$name = mysql_real_escape_string(stripslashes($name));
	$comment = mysql_real_escape_string(stripslashes($comment));
	$query = "INSERT INTO pg_photos (gallery_id,code,email,name,comment) VALUES ($gallery_id,'$code','$email','$name','$comment')";
	if (mysql_query($query,$dblink)) { 
		$newphotoid = mysql_insert_id();
		return $newphotoid;
	} else {
		return false;
	}
}

function pg_deletePhoto($photo_id) {
	// add paths to files for removal too...
}

function pg_verifyPhoto($photo_id,$code) {
	$code = mysql_real_escape_string(stripslashes(strtolower($code)));
	$query = "SELECT * FROM pg_photos WHERE id=$photo_id AND code=$code";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function pg_editPhotoDetails($photo_id,$email='',$name='',$comment='') {
	global $dblink;
	$email = mysql_real_escape_string(stripslashes(strtolower($email)));
	$name = mysql_real_escape_string(stripslashes($name));
	$comment = mysql_real_escape_string(stripslashes($comment));
	$query = "UPDATE pg_photos SET email='$email',name='$name',comment='$comment' WHERE id=$photo_id";
	@mysql_query($query,$dblink);
}

function pg_getPhotoByCode($gallery_id,$code) {
	$code = mysql_real_escape_string(stripslashes(strtolower($code)));
	$query = "SELECT * FROM pg_photos WHERE gallery_id=$gallery_id AND code=$code";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function pg_getPhotosByGallery($gallery_id) {
	$query = "SELECT * FROM pg_photos WHERE gallery_id=$gallery_id";
	$result = queryAndReturnMultiAssoc($query);
	return $result;
}

function pg_getGalleryById($gallery_id) {
	$query = "SELECT * FROM pg_galleries WHERE id=$gallery_id";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function pg_getLinkedContentByGalleryId($gallery_id) {
	$gallery = pg_getGalleryById($gallery_id);
	$query = "SELECT * FROM {$gallery['table_name']} WHERE id={$gallery['table_id']}";
	$result = queryAndReturnAssoc($query);
	return $result;
}

function pg_publishGallery($gallery_id) {
	global $dblink;
	$query = "UPDATE pg_galleries SET publish=1 WHERE id=$gallery_id";
	@mysql_query($query,$dblink);
}

function pg_unpublishGallery($gallery_id) {
	global $dblink;
	$query = "UPDATE pg_galleries SET publish=0 WHERE id=$gallery_id";
	@mysql_query($query,$dblink);
}

/////////////////////////////////////////////////////////
/*

SECTION:
custom functions

*/
/////////////////////////////////////////////////////////

function xiuxiu_addToHaiku($img_name,$q1,$q2,$q3,$q4,$q5,$q6,$q7,$q8,$q9,$q10,$q11,$q12) {
	global $dblink;
	$query = "INSERT INTO custom_xiuxiu_haiku (img_name,q1,q2,q3,q4,q5,q6,q7,q8,q9,q10,q11,q12) VALUES ('$img_name','$q1','$q2','$q3','$q4','$q5','$q6','$q7','$q8','$q9','$q10','$q11','$q12')";
	if (mysql_query($query,$dblink)) { 
		return true;
	}
}

function xiuxiu_countRemainingHaiku() {
	global $dblink;
	$query = "SELECT * FROM custom_xiuxiu_haiku WHERE approved = 1";
	$result = mysql_query($query,$dblink);
	$remaininghaiku = 234 - mysql_num_rows($result);
	return $remaininghaiku;
}
?>