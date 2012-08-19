<?php
	function formatVenueDetails($venue_details) {
		$display_string = $venue_details['name'];
		if (strtolower($venue_details['country']) == 'usa' || strtolower($venue_details['country']) == 'canada') {
			$display_string .= ' / ' . $venue_details['city'] . ', ' . $venue_details['region'];
		} else {
			$display_string .= ' / ' . $venue_details['city'] . ', ' . $venue_details['country'];	
		}
		return array(
			'id' => $venue_details['id'],
			'displayString' => $display_string
		);
	}
	if (isset($request_parameters[1])) {
		if ($request_parameters[0] == 'venues') {
			$matchingvenues_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'calendar', 
					'cash_action' => 'finevenues',
					'query' => $request_parameters[1]
				)
			);
			if (is_array($matchingvenues_response['payload'])) {
				$options_array = array();
				foreach ($matchingvenues_response['payload'] as $venue_details) {
					$options_array[] = formatVenueDetails($venue_details);
				}
				echo json_encode($options_array);
			} else {
				echo '[{"id":"0","displayString":"No matching value found. Add the venue then try again."}]';
			}
		} else {
			echo '[{"id":"0","displayString":"No matching value found. Add the venue then try again."}]';
		}
	}
	exit();
?>