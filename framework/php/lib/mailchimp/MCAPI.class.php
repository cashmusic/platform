<?php

class MCAPI {
    var $version = "1.3";
    var $errorMessage;
    var $errorCode;
    
    /**
     * Cache the information on the API location on the server
     */
    var $apiUrl;
    
    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300; 
    
    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;
    
    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $api_key;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $secure = false;
    
    /**
     * Connect to the MailChimp API for a given list.
     * 
     * @param string $apikey Your MailChimp apikey
     * @param string $secure Whether or not this should use a secure connection
     */
    function MCAPI($apikey, $secure=false) {
        $this->secure = $secure;
        $this->apiUrl = parse_url("http://api.mailchimp.com/" . $this->version . "/?output=php");
        $this->api_key = $apikey;
    }
    function setTimeout($seconds){
        if (is_int($seconds)){
            $this->timeout = $seconds;
            return true;
        }
    }
    function getTimeout(){
        return $this->timeout;
    }
    function useSecure($val){
        if ($val===true){
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }
    
    /**
     * Actually connect to the server and call the requested methods, parsing the result
     * You should never have to call this function manually
     */
    function __call($method, $params) {
	    $dc = "us1";
	    if (strstr($this->api_key,"-")){
        	list($key, $dc) = explode("-",$this->api_key,2);
            if (!$dc) $dc = "us1";
        }
        $host = $dc.".".$this->apiUrl["host"];

        $this->errorMessage = "";
        $this->errorCode = "";
        $sep_changed = false;
        //sigh, apparently some distribs change this to &amp; by default
        if (ini_get("arg_separator.output")!="&"){
            $sep_changed = true;
            $orig_sep = ini_get("arg_separator.output");
            ini_set("arg_separator.output", "&");
        }
        //mutate params
        $mutate = array();
		$mutate["apikey"] = $this->api_key;
        foreach($params as $k=>$v){
            $mutate[$this->function_map[$method][$k]] = $v;
        }
        $post_vars = http_build_query($mutate);
        if ($sep_changed){
            ini_set("arg_separator.output", $orig_sep);
        }
        
        $payload = "POST " . $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . "&method=" . $method . " HTTP/1.0\r\n";
        $payload .= "Host: " . $host . "\r\n";
        $payload .= "User-Agent: MCAPImini/" . $this->version ."\r\n";
        $payload .= "Content-type: application/x-www-form-urlencoded\r\n";
        $payload .= "Content-length: " . strlen($post_vars) . "\r\n";
        $payload .= "Connection: close \r\n\r\n";
        $payload .= $post_vars;
        
        ob_start();
        if ($this->secure){
            $sock = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);
        } else {
            $sock = fsockopen($host, 80, $errno, $errstr, 30);
        }
        if(!$sock) {
            $this->errorMessage = "Could not connect (ERR $errno: $errstr)";
            $this->errorCode = "-99";
            ob_end_clean();
            return false;
        }
        
        $response = "";
        fwrite($sock, $payload);
        stream_set_timeout($sock, $this->timeout);
        $info = stream_get_meta_data($sock);
        while ((!feof($sock)) && (!$info["timed_out"])) {
            $response .= fread($sock, $this->chunkSize);
            $info = stream_get_meta_data($sock);
        }
        fclose($sock);
        ob_end_clean();
        if ($info["timed_out"]) {
            $this->errorMessage = "Could not read response (timed out)";
            $this->errorCode = -98;
            return false;
        }

        list($headers, $response) = explode("\r\n\r\n", $response, 2);
        $headers = explode("\r\n", $headers);
        $errored = false;
        foreach($headers as $h){
            if (substr($h,0,26)==="X-MailChimp-API-Error-Code"){
                $errored = true;
                $error_code = trim(substr($h,27));
                break;
            }
        }
        
        if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);
        
        $serial = unserialize($response);
        if($response && $serial === false) {
        	$response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
        } else {
        	$response = $serial;
        }
        if($errored && is_array($response) && isset($response["error"])) {
            $this->errorMessage = $response["error"];
            $this->errorCode = $response["code"];
            return false;
        } elseif($errored){
            $this->errorMessage = "No error message was found";
            $this->errorCode = $error_code;
            return false;
        }
        
        return $response;
    }
    
    protected $function_map = array('campaignUnschedule'=>array("cid"),
'campaignSchedule'=>array("cid","schedule_time","schedule_time_b"),
'campaignResume'=>array("cid"),
'campaignPause'=>array("cid"),
'campaignSendNow'=>array("cid"),
'campaignSendTest'=>array("cid","test_emails","send_type"),
'campaignSegmentTest'=>array("list_id","options"),
'campaignCreate'=>array("type","options","content","segment_opts","type_opts"),
'campaignUpdate'=>array("cid","name","value"),
'campaignReplicate'=>array("cid"),
'campaignDelete'=>array("cid"),
'campaigns'=>array("filters","start","limit"),
'campaignStats'=>array("cid"),
'campaignClickStats'=>array("cid"),
'campaignEmailDomainPerformance'=>array("cid"),
'campaignMembers'=>array("cid","status","start","limit"),
'campaignHardBounces'=>array("cid","start","limit"),
'campaignSoftBounces'=>array("cid","start","limit"),
'campaignUnsubscribes'=>array("cid","start","limit"),
'campaignAbuseReports'=>array("cid","since","start","limit"),
'campaignAdvice'=>array("cid"),
'campaignAnalytics'=>array("cid"),
'campaignGeoOpens'=>array("cid"),
'campaignGeoOpensForCountry'=>array("cid","code"),
'campaignEepUrlStats'=>array("cid"),
'campaignBounceMessage'=>array("cid","email"),
'campaignBounceMessages'=>array("cid","start","limit","since"),
'campaignEcommOrders'=>array("cid","start","limit","since"),
'campaignShareReport'=>array("cid","opts"),
'campaignContent'=>array("cid","for_archive"),
'campaignTemplateContent'=>array("cid"),
'campaignOpenedAIM'=>array("cid","start","limit"),
'campaignNotOpenedAIM'=>array("cid","start","limit"),
'campaignClickDetailAIM'=>array("cid","url","start","limit"),
'campaignEmailStatsAIM'=>array("cid","email_address"),
'campaignEmailStatsAIMAll'=>array("cid","start","limit"),
'campaignEcommOrderAdd'=>array("order"),
'lists'=>array("filters","start","limit"),
'listMergeVars'=>array("id"),
'listMergeVarAdd'=>array("id","tag","name","options"),
'listMergeVarUpdate'=>array("id","tag","options"),
'listMergeVarDel'=>array("id","tag"),
'listInterestGroupings'=>array("id"),
'listInterestGroupAdd'=>array("id","group_name","grouping_id"),
'listInterestGroupDel'=>array("id","group_name","grouping_id"),
'listInterestGroupUpdate'=>array("id","old_name","new_name","grouping_id"),
'listInterestGroupingAdd'=>array("id","name","type","groups"),
'listInterestGroupingUpdate'=>array("grouping_id","name","value"),
'listInterestGroupingDel'=>array("grouping_id"),
'listWebhooks'=>array("id"),
'listWebhookAdd'=>array("id","url","actions","sources"),
'listWebhookDel'=>array("id","url"),
'listStaticSegments'=>array("id"),
'listStaticSegmentAdd'=>array("id","name"),
'listStaticSegmentReset'=>array("id","seg_id"),
'listStaticSegmentDel'=>array("id","seg_id"),
'listStaticSegmentMembersAdd'=>array("id","seg_id","batch"),
'listStaticSegmentMembersDel'=>array("id","seg_id","batch"),
'listSubscribe'=>array("id","email_address","merge_vars","email_type","double_optin","update_existing","replace_interests","send_welcome"),
'listUnsubscribe'=>array("id","email_address","delete_member","send_goodbye","send_notify"),
'listUpdateMember'=>array("id","email_address","merge_vars","email_type","replace_interests"),
'listBatchSubscribe'=>array("id","batch","double_optin","update_existing","replace_interests"),
'listBatchUnsubscribe'=>array("id","emails","delete_member","send_goodbye","send_notify"),
'listMembers'=>array("id","status","since","start","limit"),
'listMemberInfo'=>array("id","email_address"),
'listMemberActivity'=>array("id","email_address"),
'listAbuseReports'=>array("id","start","limit","since"),
'listGrowthHistory'=>array("id"),
'listActivity'=>array("id"),
'listLocations'=>array("id"),
'listClients'=>array("id"),
'templates'=>array("types","category","inactives"),
'templateInfo'=>array("tid","type"),
'templateAdd'=>array("name","html"),
'templateUpdate'=>array("id","values"),
'templateDel'=>array("id"),
'templateUndel'=>array("id"),
'getAccountDetails'=>array(),
'generateText'=>array("type","content"),
'inlineCss'=>array("html","strip_css"),
'folders'=>array("type"),
'folderAdd'=>array("name","type"),
'folderUpdate'=>array("fid","name","type"),
'folderDel'=>array("fid","type"),
'ecommOrders'=>array("start","limit","since"),
'ecommOrderAdd'=>array("order"),
'ecommOrderDel'=>array("store_id","order_id"),
'listsForEmail'=>array("email_address"),
'campaignsForEmail'=>array("email_address"),
'chimpChatter'=>array(),
'apikeys'=>array("username","password","expired"),
'apikeyAdd'=>array("username","password"),
'apikeyExpire'=>array("username","password"),
'ping'=>array());

}

?>