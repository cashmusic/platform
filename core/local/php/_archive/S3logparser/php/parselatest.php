<?php
// include global settings
include(dirname(__FILE__).'/settings.php');

// disable timeout as this script runs in the backround as a shell process...
set_time_limit(0);

if ($workingdir = opendir($unprocessed_location)) {
	$logfile = readdir($workingdir);
	while ((false !== $logfile) && substr($logfile,0,1) == ".") {
		$logfile = readdir($workingdir);
	}
	
	$openedapplication_log = fopen($application_log, 'a');
	
	while ($logfile !== false) {
		/*
		LINECOUNT VARIABLES:
		*/
		$parsed_lines = 0;
		$inserted_lines = 0;

		$openedlogfile = @fopen($unprocessed_location . '/' . $logfile, "r");
		if ($openedlogfile) {
		    while (!feof($openedlogfile)) {
		        $currentline = fgets($openedlogfile, 4096);
		        if (trim($currentline) != '') {
		        	parseAndInsertLine($currentline);
		        	$parsed_lines = $parsed_lines+1;
		        }
		    }
		    fclose($openedlogfile);
			$successmoving = rename($unprocessed_location . '/' . $logfile,$processed_location . '/' . $logfile);
			if ($successmoving) {
				// log the action
				
				if ($openedapplication_log !== false) {
					fwrite($openedapplication_log,time() . ' ' . $logfile . ' ' . $parsed_lines . ' ' . $inserted_lines . "\n");
				}
				
				/*
				ORIGINAL IDEA:
				loop through one logfile then start a new process for each file. too many open files/connections/processes
				
				// spawn another parselatest.php process
				// close dir handle first, then use pcntl_exec to replace this process with a new instance
				closedir($workingdir);
				mysql_close($dblink);
				$pcntl_exec_args = array("$application_location/parselatest.php");
				pcntl_exec($php_location,$pcntl_exec_args);
				*/
			}
		}
		
		$logfile = readdir($workingdir);
	}

	closedir($workingdir);
	fclose($openedapplication_log);
}





function dashToZero($value) {
	if ($value == '-') {
		return 0;
	} else {
		return $value;
	}
}

function parseAndInsertLine($currentline) {
	/*
	function parses a single line from the logfile and inserts it into the requests table in the DB
	*/
	
	// split by spaces (only if those spaces are not found between quotes)
	$splits = preg_split('/\s+(?!([^"]*"[^"]*")*[^"]*"[^"]*$)/',$currentline);
	foreach ($splits as &$value) {
	    $value = str_replace('"','',$value);
	}
	
	// convert date/time to timestamp (join based on an odd amazon space in the date)
	$joined_date = trim($splits[2].' '.$splits[3],'[]');
	$parsed_date = strptime($joined_date, '%d/%B/%Y:%H:%M:%S %z');
	$request_date = gmmktime($parsed_date['tm_hour'],
							 $parsed_date['tm_min'],
							 $parsed_date['tm_sec'],
							 $parsed_date['tm_mon'],
							 $parsed_date['tm_mday'],
							 $parsed_date['tm_year']+1900);
	
	// grab the remaining information
	$remote_ip = $splits[4];
	$amazon_user_id = $splits[5];
	$amazon_operation = $splits[7];
	$file_name = $splits[8];
	$request_uri = $splits[9];
	$http_status = $splits[10];
	$amazon_error = $splits[11];
	$bytes_sent = dashToZero($splits[12]);
	$file_size = dashToZero($splits[13]);
	$total_time = dashToZero($splits[14]);
	$processing_time = dashToZero($splits[15]);
	$referer = $splits[16];
	$user_agent = $splits[17];

	global $dblink;
	global $logfile;
	
	if ($file_name == '-' || substr($file_name,0,4) != 'log/') {
		$query = "INSERT INTO requests (request_date,remote_ip,amazon_user_id,amazon_operation,file_name,request_uri,http_status,amazon_error,bytes_sent,file_size,total_time,processing_time,referer,user_agent,from_logfile) VALUES ($request_date,'$remote_ip','$amazon_user_id','$amazon_operation','$file_name','$request_uri','$http_status','$amazon_error',$bytes_sent,$file_size,$total_time,$processing_time,'$referer','$user_agent','$logfile')"; 
		if (mysql_query($query,$dblink)) {
			global $inserted_lines;
			$inserted_lines = $inserted_lines+1;
			return true;
		} else {
			//echo mysql_error();
			return false;
		}
	}

}
?>
